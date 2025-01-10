<?php
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$token = $_GET['token'];
$env = $_GET['env'];


//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos" : "http://100.78.93.50:8009/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos";

// consulta al gparametros para traer el saldo final que se guarda manualmente // tiene un 0 al final porque es el centro de costo que pide pero que se ha desabilitado
// $url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/gparametros/c/parametros/0" : "http://100.78.93.50:8009/api/gparametros/c/parametros/0";

$url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/gparametros/c/inventario_por_trimestre/". $fecha_final : "http://100.78.93.50:8009/api/gparametros/c/inventario_por_trimestre/". $fecha_final;


$data = array(
    "data" => array(
        "fecha_inicial" => $fecha_inicial,
        "fecha_final" => $fecha_final
    )
);


$opciones = array(
    'http' => array(
        'method' => 'POST',
        'header' => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ),
        'content' => json_encode($data)
    )
);

$query_nombre_cdc = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));



// $opciones = array('http' => array(
//     'method' => 'GET',
//     'header' => 'Authorization: Bearer ' . $token,
// ));

$contexto = stream_context_create($opciones);
$respuesta = json_decode(file_get_contents($url, false, $contexto), true);



// CONSULTA DE INVENTARIO FINAL EN GPARAMETROS
$query_inventario_final = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto3 = stream_context_create($query_inventario_final);
$respuesta3 = json_decode(file_get_contents($url3, false, $contexto3), true);





// CUENTAS PARA SUMAR DE LA SEGUNDA COLUMNA NIVEL 6


$suma_col_1 = 0;
$suma_col_2 = 0;
$suma_col_3 = 0;


function buscar_cuenta_no_recursiva($datos, $codigo_buscado)
{
    $pila = [$datos];

    while (!empty($pila)) {
        $nivel_actual = array_pop($pila);

        foreach ($nivel_actual as $item) {
            if (isset($item['codigo_crudo']) && $item['codigo_crudo'] === $codigo_buscado) {
                return $item;
            }

            if (isset($item['detalle']) && is_array($item['detalle'])) {
                $pila[] = $item['detalle'];
            }
        }
    }

    return null;
}


function suma_saldos($resp, $array){

    $resultado = 0;

    foreach ($array as $key) {
        $resultado += abs(buscar_cuenta_no_recursiva($resp, $key)['saldo_final']);
    }


    return $resultado;

}


// CACULO EN BASE AL REPORTE RPG.PHP

$formula_costo_de_ventas = (abs(buscar_cuenta_no_recursiva($respuesta, '713101')['saldo_final']) + abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final'])) - abs($respuesta3['inventario_final']);

$total_1_RPG = suma_saldos($respuesta, ["601101", "602101", "613101", "613102", "613103", "613104", "613106"]);

$total_2_RPG = suma_saldos($respuesta, ["701101", "706101", "706102", "706103", "706104", "706105", "706106", "706107", "706108", "706109", "706110", "706199", "709199", "713102", "713103", "713104", "714101", "714102", "714103", "714104", "714105", "714106", "714107", "714108", "714109", "714110", "714111", "714112", "715101", "715102", "715103", "715104", "715105", "715106", "715107", "715108", "715109", "715110", "715111", "716101", "716102", "716103", "716104", "716105", "716106", "716107", "716108", "716109", "716110", "716111"]) + $formula_costo_de_ventas;

$utilidad = $total_1_RPG - $total_2_RPG;

$operacion_activo = $total_1_RPG - (2 * abs(buscar_cuenta_no_recursiva($respuesta, '61310199')['saldo_final']));
$operacion_pasivo = $total_2_RPG - (2 * abs(buscar_cuenta_no_recursiva($respuesta, '7131010105')['saldo_final']));

echo "Activo: " . $operacion_activo . "</br>";
echo "Pasivo: " . $operacion_pasivo . "</br>";
echo "Utilidad: " . $utilidad . "</br>";


// print_r($utilidad);
die();

$reserva_irrepartible = (80 / 100) * $utilidad;


$resultado_obras_sociales = (10 / 100) * $utilidad;
$resultado_educacion = (10 / 100) * $utilidad;

// $listado[10]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6131010150')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010151')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010202')['saldo_final'];



$row_1 = 20;
$row_2 = 50;
$row_3 = 10;
$row_4 = 20;
// $row_5 = 15;

$pos = 0;


$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo_sub">
            BALANCE GENERAL
        </div>
        <div class="titulo_sub">
            DEL ' . date('d/m/Y', strtotime($fecha_inicial)) . ' AL ' . date('d/m/Y', strtotime($fecha_final)) . '
        </div>
        <div class="titulo_sub">
            (CIFRAS EXPRESADA EN QUETZALES)
        </div>

        <table class="table">';

$html .= '
        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">1</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">ACTIVO</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;">Nota</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">101</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">DISPONIBLES</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">101101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Caja</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">4</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '101101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">101103</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Banco del país</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">4</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '101103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">103</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CARTERA DE CRÉDITOS</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>';

        $formula_cuenta_vigentes = abs(buscar_cuenta_no_recursiva($respuesta, '103101')['saldo_final']) - abs(buscar_cuenta_no_recursiva($respuesta, '3061019901')['saldo_final']);


$html.='<tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">103101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Vigente</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">5</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format($formula_cuenta_vigentes, 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">109</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">INMUEBLES Y MUEBLES</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">109101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Inmuebles</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">7</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '109101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">109102</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Muebles</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">7</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '109102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">109103</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Construcción en proceso</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '109103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;height: 15px;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;height: 15px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;height: 15px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;height: 15px;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">110</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CARGOS DIFERIDOS</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">110101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Gastos por amortizar</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">9</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '110101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">110103</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Gastos anticipados</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">9</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '110103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">113</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">NEGOCIOS SIN INTERMEDIACIÓN FINANCIERA</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">113105</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Cuentas y documentos por cobrar</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">10</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '113105')['saldo_final']), 2, '.', ',') .'</td>
        </tr>';

        $formula_inventario_ = abs($respuesta3['inventario_final']) + abs(buscar_cuenta_no_recursiva($respuesta, '113107')['saldo_final']);
        
$html.='<tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">113106</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Inventarios</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">11</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format($formula_inventario_, 2, '.', ',') .'</td>
        </tr>';

        $sub_total_activo = suma_saldos($respuesta, ["101101", "101103", "109101", "109102", "109103", "110101", "110103", "113105"]) + $formula_inventario_ + $formula_cuenta_vigentes;


$html .= '<tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">SUB-TOTAL ACTIVO</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs($sub_total_activo), 2, '.', ',') .'</td>
        </tr>


        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">2</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CUENTAS REGULARIZADORAS DE ACTIVO</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">202</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">DEPRECIACIONES ACUMULADAS</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">202109</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Inmuebles y muebles</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">8</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '202109')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">203</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">AMROTIZACIONES ACUMULADAS</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">203110</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Cargos diferidos</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">9</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '203110')['saldo_final']), 2, '.', ',') .'</td>
        </tr>
';

$total_del_1 = suma_saldos($respuesta, ["202109", "203110"]);

$html .= '
        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">TOTALES</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs($total_del_1), 2, '.', ',') .'</td>
        </tr>
        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">TOTAL DEL ACTIVO</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(($sub_total_activo - $total_del_1)), 2, '.', ',') .'</td>
        </tr>


        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;height: 15px;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;height: 15px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;height: 15px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;height: 15px;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">3</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">PASIVO</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">301</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">OBLIGACIONES DEPOSITARIAS</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">301102</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Depósitos de ahorro</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">12</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '301102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">301103</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Depósitos a plazo</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">12</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '301103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">305</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CUENTAS POR PAGAR</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">305101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Obligaciones inmediatas</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">13</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '305101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">306</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">PROVISIONES</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>';

        $formula_laborales = suma_saldos($respuesta, ["3061010301" , "3061019902","3061019903","3061019904","3061019905","3061019906"]);


$html.='<tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">306101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Laborales</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">14</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs($formula_laborales), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">313</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">NEGOCIOS SIN INTERMEDIACIÓN FINANCIERA</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">313101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Obligaciones corrientes</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">15</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '313101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">313102</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Obligaciones no corrientes</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">15</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '313102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">313103</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Otros pasivos</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">15</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '313103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>';

                
$total_pasivos = suma_saldos($respuesta, ["301102", "301103", "305101", "313101", "313102", "313103"]) + $formula_laborales;


$html .='
        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">TOTAL PASIVO</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs($total_pasivos), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;height: 15px;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;height: 15px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;height: 15px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;height: 15px;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">5</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CAPITAL CONTABLE</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>
        
        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">502</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CAPITAL SOCIAL</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">501101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Aportaciones asociados</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">16</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '501101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">503</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CAPITAL INSTITUCIONAL</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">503101</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Reserva irrepartible</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">16</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '503101')['saldo_final']) + $reserva_irrepartible, 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">504</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">CAPITAL TRANSITORIO</td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
        </tr>';


        
$html.='<tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">503199</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Reserva obras sociales</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">16</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '503199')['saldo_final']) + $resultado_obras_sociales, 2, '.', ',') .'</td>
        </tr>


        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;">504104</td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">Reserva de educación</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">16</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '504104')['saldo_final']) + $resultado_educacion, 2, '.', ',') .'</td>
        </tr>';

$total_capital_contable = suma_saldos($respuesta, ["501101", "503101", "503199", "504104"]) + $resultado_obras_sociales + $resultado_educacion + $reserva_irrepartible;


$html .='<tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">TOTAL CAPITAL CONTABLE</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs($total_capital_contable), 2, '.', ',') .'</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;">TOTAL PASIVO Y CAPITAL</td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q'. number_format(abs($total_pasivos + $total_capital_contable), 2, '.', ',') .'</td>
        </tr>

         <tr>
            <td class="estilo_celda2" style="width: ' . $row_1 . '%;height: 4px;"></td>
            <td class="estilo_celda" style="width: ' . $row_2 . '%;height: 4px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;height: 4px;"></td>
            <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;height: 4px;"></td>
        </tr>


';









$html .= '<tr>
                        <td colspan="4" class="estilo_celda4">El infrascrito Perito Contador, registrado en la Superintendencia de Administración Tributaria, con nit 7584104-5, CERTIFICA :</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="estilo_celda4">Que el presente estado de resultado, de la Cooperativa de Ahorro, Crédito y Servicios Varios</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="estilo_celda4">"SITRABI", R. L., por el primer trimestre comprendido de 01 enero al 31 marzo 2024, se encuentra elaborado de acuerdo a las</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="estilo_celda4">Normas Internacionales de contabilidad.</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="estilo_celda4" style="height: 13px";></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    <tr>
                        <td colspan="4" class="estilo_celda4">Morales Izabal, 24 de enero de 2024</td>
                    </tr>

                    <tr>
                        <td colspan="4" class="estilo_celda4" style="height: 13px";></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    <tr>
                        <td colspan="4" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    
            ';


$html .= '        
        </table>

        <table class="table">
            <tr>
                <td class="estilo_celda4">Juan Carlos Galdámez Vásquez</td>
                <td class="estilo_celda4">Orlando Antonio Salazar Martínez</td>
            </tr>
            <tr>
                <td class="estilo_celda4">Contador General</td>
                <td class="estilo_celda4">Representante Leal</td>
            </tr>

        </table>
    ';


$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Reporte_Balance_General.pdf', 'I');
