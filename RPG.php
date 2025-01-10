<?php
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$centro_de_costo = 0;//$_GET['centro_de_costo'];
$token = $_GET['token'];
$env = $_GET['env'];


//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos" : "http://100.78.93.50:8009/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos";

$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/centros-de-costos/c/nombre/" . $centro_de_costo : "http://100.78.93.50:8009/api/centros-de-costos/c/nombre/" . $centro_de_costo;


// consulta al gparametros para traer el saldo final que se guarda manualmente // tiene un 0 al final porque es el centro de costo que pide pero que se ha desabilitado
// $url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/gparametros/c/parametros/0" : "http://100.78.93.50:8009/api/gparametros/c/parametros/0";

$url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/gparametros/c/inventario_por_trimestre/". $fecha_final : "http://100.78.93.50:8009/api/gparametros/c/inventario_por_trimestre/". $fecha_final;

$data = array(
    "data" => array(
        "fecha_inicial" => $fecha_inicial,
        "fecha_final" => $fecha_final,
        "centro_de_costo" => $centro_de_costo
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

// OBTENER CATALOGO DE CUENTAS


// $opciones = array('http' => array(
//     'method' => 'GET',
//     'header' => 'Authorization: Bearer ' . $token,
// ));

$contexto = stream_context_create($opciones);
$respuesta = json_decode(file_get_contents($url, false, $contexto), true);

// consulta de nombre de de centro de costo
$contexto2 = stream_context_create($query_nombre_cdc);
$respuesta2 = json_decode(file_get_contents($url2, false, $contexto2), true);

$nombre_centro_de_costo = $centro_de_costo == 0 ? 'GENERAL' : $respuesta2[0][nombre];

// CONSULTA DE INVENTARIO FINAL EN GPARAMETROS
$query_inventario_final = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto3 = stream_context_create($query_inventario_final);
$respuesta3 = json_decode(file_get_contents($url3, false, $contexto3), true);



$total_cuenta_1 = 0;
$totales_de_cuentas = [];



function imprimirDatos($filas, $raiz, $level = 0)
{
    $html = '';
    foreach ($filas as $item) {

        $saldo_anterior = $item[saldo_anterior] == 0 ? '' :  'Q' . number_format($item[saldo_anterior], 2, '.', ',');
        $debe = $item[debe] == 0 ? '' :  'Q' . number_format(abs($item[debe]), 2, '.', ',');
        $haber = $item[haber] == 0 ? '' :  'Q' . number_format(abs($item[haber]), 2, '.', ',');
        $saldo_final = $item[saldo_final] == 0 ? '' :  'Q' . number_format(abs($item[saldo_final]), 2, '.', ',');

        $clave = explode('.', $item['codigo']);

        if ($item['tipo_de_cuenta'] == "M") {

            if ($clave[0] == $raiz) {

                if (count($clave) == 1) {
                    global $total_cuenta_1;
                    $total_cuenta_1 = $saldo_final;
                    global $totales_de_cuentas;
                    array_push($totales_de_cuentas, abs($item[saldo_final]));
                }


                $html .= '<tr>';
                $html .= '<td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: 20%;">' . $item['codigo'] . '</td>';
                $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: 60%;">' . $item['nombre_cuenta'] . '</td>';
                $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>';
                $html .= '</tr>';
            }
        } else {

            if ($clave[0] == $raiz) {
                $html .= '<tr>';
                $html .= '<td class="estilo_celda2" style="width: 20%;">' . $item['codigo'] . '</td>';
                $html .= '<td class="estilo_celda" style="width: 60%;">' . $item['nombre_cuenta'] . '</td>';
                $html .= '<td class="estilo_celda centrar_texto" style="width: 20%;">' . $saldo_final . '</td>';
                $html .= '</tr>';
            }
        }


        if (!empty($item['detalle'])) {
            $html .= imprimirDatos($item['detalle'], $raiz, $level + 1);
        }
    }

    return $html;
}


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


$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo_sub">
            ESTADO DE PRODUCTOS Y GASTOS
        </div>
        <div class="titulo_sub">
            DEL ' . date('d/m/Y', strtotime($fecha_inicial)) . ' AL ' . date('d/m/Y', strtotime($fecha_final)) . '
        </div>

        <table class="table">';

// $html .= imprimirDatos($respuesta, 6);

$html .= '
        <tr>  
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 20%;">6</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 60%;">PRODUCTOS</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 20%;">601</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 60%;">PRODUCTOS FINANCIEROS</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">6.01.1.01</td>
            <td class="estilo_celda6" style="width: 60%;">Intereses</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '601101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 20%;">6.02</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 60%;">PRODUCTOS POR SERVICIO</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 20%;">6.02.1</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 60%;">COMISIONES</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">6.02.1.01</td>
            <td class="estilo_celda6" style="width: 60%;">Comisiones por servicios diversos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '602101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 20%;">6.13</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 60%;">INGRESOS, OTROS NEGOCIOS SIN INTERMEDIACIÓN FINANCIERA</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 20%;">6.13.1</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold" style="width: 60%;">INGRESOS VARIOS</td>
            <td class="estilo_celda_ fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>';

        $formula_ventas = (abs(buscar_cuenta_no_recursiva($respuesta, '613101')['saldo_final']) - abs(buscar_cuenta_no_recursiva($respuesta, '61310199')['saldo_final'])) - abs(buscar_cuenta_no_recursiva($respuesta, '61310199')['saldo_final']);

    //<td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '613101')['saldo_final']), 2, '.', ',') .'</td>

$html.='<tr>  
            <td class="estilo_celda6" style="width: 20%;">6.13.1.01</td>
            <td class="estilo_celda6" style="width: 60%;">Ventas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs($formula_ventas), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">6.13.1.02</td>
            <td class="estilo_celda6" style="width: 60%;">Servicios</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '613102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">6.13.1.03</td>
            <td class="estilo_celda6" style="width: 60%;">Otros ingresos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '613103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">6.13.1.04</td>
            <td class="estilo_celda6" style="width: 60%;">Otros productos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '613104')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">6.13.1.06</td>
            <td class="estilo_celda6" style="width: 60%;">COMISIONES SECCIÓN CONSUMO</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '613106')['saldo_final']), 2, '.', ',') .'</td>
        </tr>';


        $total_1 = suma_saldos($respuesta, ["601101", "602101", "613101", "613102", "613103", "613104", "613106"]);
        $ntotal_1 = $total_1 - (2 * abs(buscar_cuenta_no_recursiva($respuesta, '61310199')['saldo_final']));

$html .= '<tr>
            <td class="estilo_celda2" style="width: 20%;"></td>
            <td class="estilo_celda3" style="width: 50%;font-weight: bold;">TOTAL PRODUCTOS</td>
            <td class="estilo_celda3 centrar_texto" style="width: 30%;">Q' . number_format($ntotal_1, 2, '.', ',') . '</td>
        </tr>
            ';

$html .= '<tr>
            <td class="estilo_celda2" style="width: 20%;height: 25px;"></td>
            <td class="estilo_celda3" style="width: 50%;"></td>
            <td class="estilo_celda3 centrar_texto" style="width: 30%;"></td>
        </tr>
            

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">701</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS FINANCIEROS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7011</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS PAGO DE INTERESES</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">701101</td>
            <td class="estilo_celda6" style="width: 60%;">Intereses</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '701101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">706</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS DE ADMINISTRACION</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7061</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">706101</td>
            <td class="estilo_celda6" style="width: 60%;">Organos directivos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>  
            <td class="estilo_celda6" style="width: 20%;">706102</td>
            <td class="estilo_celda6" style="width: 60%;">Funcionarios y empleados</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>  
            <td class="estilo_celda6" style="width: 20%;">706103</td>
            <td class="estilo_celda6" style="width: 60%;">Tributos y otras cuotas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">706104</td>
            <td class="estilo_celda6" style="width: 60%;">Honorarios profesionales</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706104')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">706105</td>
            <td class="estilo_celda6" style="width: 60%;">Arrendamientos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706105')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6" style="width: 20%;">706106</td>
            <td class="estilo_celda6" style="width: 60%;">Reparaciones y mantenimiento</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706106')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">706107</td>
            <td class="estilo_celda6" style="width: 60%;">Mercadeo y publicidad</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706107')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">706108</td>
            <td class="estilo_celda6" style="width: 60%;">Primas de Seguros y Fianzas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706108')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">706109</td>
            <td class="estilo_celda6" style="width: 60%;">Depreciaciones y Amortizaciones</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706109')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">706110</td>
            <td class="estilo_celda6" style="width: 60%;">Papelería</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706110')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">706199</td>
            <td class="estilo_celda6" style="width: 60%;">Gastos varios</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '706199')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">708</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS EXTRAORDINARIOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7091</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS EXTRAORDINARIOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>


        <tr>    
            <td class="estilo_celda6" style="width: 20%;">709199</td>
            <td class="estilo_celda6" style="width: 60%;">Otros</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '709199')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">713</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">COSTOS Y GASTOS DE NEGOCIOS SIN INTERMEDIACION FINANCIERA</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7131</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">COSTOS Y GASTOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>';

//ESTA CUETNA SALE DE LA CONSULTA DIRECTA AL INVENTARIO DE MERCADERÍA DEL BALANCE DE SALDOS
//1.13.1.06.01.01
//1131060101
//INVENTARIO DE MERCADERIA

// LA FORMULAR ES LA SIGUIENTE: saldo de cuenta costo de ventas (713101) + saldo de inventario de mercaderia (1131060101) - inventario final (que está en gparametro)


        $ctv_713101 = abs(buscar_cuenta_no_recursiva($respuesta, '713101')['saldo_final']);
        $ctv_1131060101 = abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final']);
        $ctv_7131010105 = abs(buscar_cuenta_no_recursiva($respuesta, '7131010105')['saldo_final']);
        $ctv_inventario_final = abs($respuesta3['inventario_final']);

        // $formula_costo_de_ventas = ((abs(buscar_cuenta_no_recursiva($respuesta, '713101')['saldo_final']) + abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final'])) - (2 * abs(buscar_cuenta_no_recursiva($respuesta, '7131010105')['saldo_final']))) - abs($respuesta3['inventario_final']);

        // $formula_costo_de_ventas = (($ctv_713101 + $ctv_1131060101) - (2 * $ctv_7131010105)) - $ctv_inventario_final);
        $formula_costo_de_ventas = (($ctv_713101 + $ctv_1131060101) - (2 * $ctv_7131010105)) - $ctv_inventario_final;



$html.= '<tr>    
            <td class="estilo_celda6" style="width: 20%;">713101</td>
            <td class="estilo_celda6" style="width: 60%;">Costo de ventas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format($ctv_inventario_final, 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">713102</td>
            <td class="estilo_celda6" style="width: 60%;">Gastos de operación</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '713102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">713103</td>
            <td class="estilo_celda6" style="width: 60%;">Otros gastos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '713103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">713104</td>
            <td class="estilo_celda6" style="width: 60%;">Gastos extraordinarios</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '713104')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">714</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS SECCIÓN CONSUMO (FINANCIEROS)</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7141</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714101</td>
            <td class="estilo_celda6" style="width: 60%;">Intereses</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714102</td>
            <td class="estilo_celda6" style="width: 60%;">Organos directivos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        
        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714103</td>
            <td class="estilo_celda6" style="width: 60%;">Funcionarios y empleados</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714104</td>
            <td class="estilo_celda6" style="width: 60%;">Tributos y otras cuotas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714104')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714105</td>
            <td class="estilo_celda6" style="width: 60%;">Honorarios profesionales</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714105')['saldo_final']), 2, '.', ',') .'</td>
        </tr>
        
        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714106</td>
            <td class="estilo_celda6" style="width: 60%;">Reparaciones y mantenimiento</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714106')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714107</td>
            <td class="estilo_celda6" style="width: 60%;">Mercadeo y publicidad</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714107')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714108</td>
            <td class="estilo_celda6" style="width: 60%;">Primas de seguros y Fianzas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714108')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714109</td>
            <td class="estilo_celda6" style="width: 60%;">Depreciaciones y Amotizaciones</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714109')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714110</td>
            <td class="estilo_celda6" style="width: 60%;">Gastos varios</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714110')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714111</td>
            <td class="estilo_celda6" style="width: 60%;">Papelería</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714111')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">714112</td>
            <td class="estilo_celda6" style="width: 60%;">Otros Gastos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '714112')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">715</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS SECCIÓN TRANSPORTE (FINANCIEROS)</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>


        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7151</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715101</td>
            <td class="estilo_celda6" style="width: 60%;">Intereses</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715102</td>
            <td class="estilo_celda6" style="width: 60%;">Organos directivos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715103</td>
            <td class="estilo_celda6" style="width: 60%;">Funcionarios y empleados</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715104</td>
            <td class="estilo_celda6" style="width: 60%;">Tributos y otras cuotas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715104')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715105</td>
            <td class="estilo_celda6" style="width: 60%;">Honorarios profesionales</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715105')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715106</td>
            <td class="estilo_celda6" style="width: 60%;">Reparaciones y mantenimiento</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715106')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715107</td>
            <td class="estilo_celda6" style="width: 60%;">Mercadeo y publicidad</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715107')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715108</td>
            <td class="estilo_celda6" style="width: 60%;">Primas de Seguros y Fianzas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715108')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715109</td>
            <td class="estilo_celda6" style="width: 60%;">Depreciaciones y Amortizaciones</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715109')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715110</td>
            <td class="estilo_celda6" style="width: 60%;">Gastos Varios</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715110')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">715111</td>
            <td class="estilo_celda6" style="width: 60%;">Otros Gastos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '715111')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">716</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS SECCIÓN LOTIFICACION (FINANCIEROS)</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>  
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 20%;">7161</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold" style="width: 60%;">GASTOS</td>
            <td class="estilo_celda6 fondo_gris_titulo estilo_bold centrar_texto" style="width: 20%;"></td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716101</td>
            <td class="estilo_celda6" style="width: 60%;">Intereses</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716101')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716102</td>
            <td class="estilo_celda6" style="width: 60%;">Organos directivos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716102')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716103</td>
            <td class="estilo_celda6" style="width: 60%;">Funcionarios y empleados</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716103')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716104</td>
            <td class="estilo_celda6" style="width: 60%;">Tributos y otras cuotas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716104')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716105</td>
            <td class="estilo_celda6" style="width: 60%;">Honorarios profesionales</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716105')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716106</td>
            <td class="estilo_celda6" style="width: 60%;">Reparaciones y mantenimiento</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716106')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716107</td>
            <td class="estilo_celda6" style="width: 60%;">Mercadeo Y publicidad</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716107')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716108</td>
            <td class="estilo_celda6" style="width: 60%;">Prima de Seguros y Fianzas</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716108')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716109</td>
            <td class="estilo_celda6" style="width: 60%;">Depreciaciones y Amortizaciones</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716109')['saldo_final']), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716110</td>
            <td class="estilo_celda6" style="width: 60%;">Gastos varios</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716110')['saldo_final']), 2, '.', ',') .'</td>
        </tr>


        <tr>    
            <td class="estilo_celda6" style="width: 20%;">716111</td>
            <td class="estilo_celda6" style="width: 60%;">Otros Gastos</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(abs(buscar_cuenta_no_recursiva($respuesta, '716111')['saldo_final']), 2, '.', ',') .'</td>
        </tr>';

    // a la funcion de suma de saldos se le quita la cuenta de COSTO DE VENTAS (713101) y en su lugar se le suma la formula: formula_costo_de_ventas, esto es para que cuadren los
    // resultados


        $total_2 = suma_saldos($respuesta, ["701101", "706101", "706102", "706103", "706104", "706105", "706106", "706107", "706108", "706109", "706110", "706199", "709199", "713102", "713103", "713104", "714101", "714102", "714103", "714104", "714105", "714106", "714107", "714108", "714109", "714110", "714111", "714112", "715101", "715102", "715103", "715104", "715105", "715106", "715107", "715108", "715109", "715110", "715111", "716101", "716102", "716103", "716104", "716105", "716106", "716107", "716108", "716109", "716110", "716111"]) + $formula_costo_de_ventas;


    $pre_total = $total_1 - $total_2;

$html .='
        <tr>
            <td class="estilo_celda2" style="width: 20%;"></td>
            <td class="estilo_celda3" style="width: 50%;font-weight: bold;">TOTAL COSTOS Y GASTOS</td>
            <td class="estilo_celda3 centrar_texto" style="width: 30%;">Q' . number_format($total_2, 2, '.', ',') . '</td>
        </tr>

        <tr>
            <td class="estilo_celda2" style="width: 20%;"></td>
            <td class="estilo_celda3" style="width: 50%;font-weight: bold;">PERDIDA Y/O GANANCIA DEL PERIODO</td>
            <td class="estilo_celda3 centrar_texto" style="width: 30%;">Q' . number_format($pre_total, 2, '.', ',') . '</td>
        </tr>



        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: 20%;">503</td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold" style="width: 50%;font-weight: bold;">CAPITAL INSTITUCIONAL</td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold centrar_texto" style="width: 30%;"></td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">503101</td>
            <td class="estilo_celda6" style="width: 60%;">Reserva irrepartible</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(((80 / 100) * $pre_total), 2, '.', ',') .'</td>
        </tr>


        <tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: 20%;">504</td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold" style="width: 50%;font-weight: bold;">CAPITAL TRANSIORIO</td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold centrar_texto" style="width: 30%;"></td>
        </tr>


        <tr>    
            <td class="estilo_celda6" style="width: 20%;">504101</td>
            <td class="estilo_celda6" style="width: 60%;">Reserva para obras sociales</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(((10 / 100) * $pre_total), 2, '.', ',') .'</td>
        </tr>

        <tr>    
            <td class="estilo_celda6" style="width: 20%;">504104</td>
            <td class="estilo_celda6" style="width: 60%;">Reserva para educación</td>
            <td class="estilo_celda6 centrar_texto" style="width: 20%;">Q'. number_format(((10 / 100) * $pre_total), 2, '.', ',') .'</td>
        </tr>';

    $reservas_capital = ((80 / 100) * $pre_total ) + ((10 / 100) * $pre_total ) + ((10 / 100) * $pre_total );


$html .= '<tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: 20%;"></td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold" style="width: 50%;font-weight: bold;">TOTAL RESERVAS DE CAPITAL</td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold centrar_texto" style="width: 30%;">Q'. number_format($reservas_capital, 2, '.', ',') .'</td>
        </tr>
        ';


        $utilidad = abs($pre_total) - abs($reservas_capital);


        $html .= '<tr>
            <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: 20%;"></td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold" style="width: 50%;font-weight: bold;">UTILIDAD DEL EJERCICIO</td>
            <td class="estilo_celda3 fondo_gris_titulo estilo_bold centrar_texto" style="width: 30%;">Q'. number_format(abs($utilidad), 2, '.', ',') .'</td>
        </tr>
        ';



$html .= '<tr>
                        <td class="estilo_celda2" style="width: 20%;height: 25px;"></td>
                        <td class="estilo_celda3" style="width: 50%"></td>
                        <td class="estilo_celda3 centrar_texto" style="width: 30%;"></td>
                    </tr>
            ';

$html .= '<tr>
                        <td class="estilo_celda2" style="width: 20%;height: 25px;"></td>
                        <td class="estilo_celda3" style="width: 50%"></td>
                        <td class="estilo_celda3 centrar_texto" style="width: 30%;"></td>
                    </tr>
            ';


$html .= '<tr>
                        <td colspan="3" class="estilo_celda4">El infrascrito Perito Contador, registrado en la Superintendencia de Administración Tributaria, con nit 7584104-5, CERTIFICA :</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="estilo_celda4">Que el presente estado de resultado, de la Cooperativa de Ahorro, Crédito y Servicios Varios</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="estilo_celda4">"SITRABI", R. L., por el primer trimestre comprendido de 01 enero al 31 marzo 2024, se encuentra elaborado de acuerdo a las</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="estilo_celda4">Normas Internacionales de contabilidad.</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="estilo_celda4" style="height: 13px";></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    <tr>
                        <td colspan="3" class="estilo_celda4">Morales Izabal, 24 de enero de 2024</td>
                    </tr>

                    <tr>
                        <td colspan="3" class="estilo_celda4" style="height: 13px";></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    <tr>
                        <td colspan="3" class="estilo_celda4" style="height: 13px";></td>
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

$mpdf->Output('Reporte_Productos_Gastos.pdf', 'I');
