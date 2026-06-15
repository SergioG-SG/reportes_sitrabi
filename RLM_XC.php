<?php

date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');



require_once('mpdf/mpdf.php');


// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$cuenta = $_GET['cuenta'];
$centro_de_costo = 0;
$token = $_GET['token'];
$env = $_GET['env'];




//OBTENER DATOS PARA LIBRO MAYOR

$url = $env == 'p' ? "http://186.151.206.62/app/coope/api/contabilidad-transacciones/c/libro_mayor_por_cuenta?fecha_inicial=". $fecha_inicial ."&fecha_final=" . $fecha_final . "&cuenta=". $cuenta : "http://100.78.93.50:8009/api/contabilidad-transacciones/c/libro_mayor_por_cuenta?fecha_inicial=". $fecha_inicial ."&fecha_final=" . $fecha_final . "&cuenta=" . $cuenta;


// OBTENER CATALOGO DE CUENTAS


$opciones = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto = stream_context_create($opciones);
$respuesta = json_decode(file_get_contents($url, false, $contexto), true);




$suma_debe = 0;
$suma_haber = 0;

// OBTENER NOMBRES DE POLIZAS

$url2 = $env == 'p' ? "http://186.151.206.62/app/coope/api/contabilidad-tipo-de-polizas" : "http://100.78.93.50:8009/api/contabilidad-tipo-de-polizas";


$opciones2 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto2 = stream_context_create($opciones2);
$respuesta2 = json_decode(file_get_contents($url2, false, $contexto2), true);




// OBTENER BALANCE DE SALDOS

$url3 = $env == 'p' ? "http://186.151.206.62/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos" : "http://100.78.93.50:8009/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos";

$data = array(
    "data" => array(
        "fecha_inicial" => $fecha_inicial,
        "fecha_final" => $fecha_final,
        "centro_de_costo" => $centro_de_costo
    )
);

$opciones3 = array(
    'http' => array(
        'method' => 'POST',
        'header' => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ),
        'content' => json_encode($data)
    )
);

$contexto3 = stream_context_create($opciones3);
$balance = json_decode(file_get_contents($url3, false, $contexto3), true);


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


function buscar_nombre_poliza($array, $codigo){

    foreach ($array as $key) {
        
        if($codigo == $key[id]){
            return $key[attributes][nombre];
        }

    }

}

// elimina los puntos de la cuenta para devolver el codigo en crudo


function quitar_ceros($cuenta) {
    $array = explode('.', $cuenta);
    
    while (count($array) > 0 && intval(end($array)) === 0) {
        array_pop($array);
    }
    
    $cuenta_sin_ceros = implode('', $array);
    return $cuenta_sin_ceros;
}


$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo">
            LIBRO MAYOR ' . date('d/m/Y', strtotime($fecha_inicial)) . ' al ' . date('d/m/Y', strtotime($fecha_final)) . '
        </div>
        <div class="titulo">
             ** '. $respuesta[0]['nombre_cuenta'] .' **
        </div>
        <table class="table">
            
    ';

// print_r($respuesta[0]);
// die();

// $saldo_anterior = buscar_cuenta_no_recursiva($balance, '1011010101')['saldo_anterior'];

foreach ($respuesta as $key) {

    $codigo_crudo = quitar_ceros($key[codigo_titulo]);
    $saldo_anterior = buscar_cuenta_no_recursiva($balance, $codigo_crudo)['saldo_anterior'];


    $html .= '
            <tr>
                <td colspan="1" class="fecha_registro">
                    ' .$key[codigo_titulo] . '
                </td>
                <td colspan="1" class="fecha_registro">
                    ' .$key[nombre_cuenta] . '
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro" style="font-size: 8px;">
                    
                </td>
                <td colspan="1" class="fecha_registro" style="font-size: 8px;">
                    '. $saldo_anterior .'
                </td>
            </tr>
            <tr>
                <td class="estilo_celda fondo_gris_titulo">
                    Póliza
                </td>
                <td class="estilo_celda fondo_gris_titulo">
                    Centro de costo
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    Tipo
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    Fecha
                </td>
                <td class="estilo_celda fondo_gris_titulo centrar_texto">
                    DEBE
                </td>
                <td class="estilo_celda fondo_gris_titulo centrar_texto">
                    HABER
                </td>
                <td class="estilo_celda fondo_gris_titulo centrar_texto">
                    SALDO
                </td>
            </tr>';

    $sumatoria_debe = 0;
    $sumatoria_haber = 0;
    $sumatoria_saldo_actual = 0;
    $saldo_actual = 0;
        

    foreach ($key[cuentas] as $cuentas_) {

        if ($cuentas_[monto] > 0) {
            $_debe_crudo = $cuentas_[monto];
            $_haber_crudo = 0;
            $_debe = 'Q' . number_format($cuentas_[monto], 2, '.', ',');
            $_haber = '';
        } else {
            $_debe_crudo = 0;
            $_haber_crudo = $cuentas_[monto] * -1;
            $_debe = '';
            $_haber = 'Q' . number_format(($cuentas_[monto] * -1), 2, '.', ',');
        }


        $nombre_poliza_ = buscar_nombre_poliza($respuesta2[data], $cuentas_[poliza]);
        $saldo_actual = $saldo_anterior + $_debe_crudo - $_haber_crudo;

        $html .= '
            <tr>
                <td class="estilo_celda" style="width: 20%">
                    ' . $cuentas_[numero_documento] . ' 
                </td>
                <td class="estilo_celda" style="width: 30%">
                    ' . $cuentas_[centro_de_costo] . ' 
                </td>
                <td class="estilo_celda" style="text-align: center;width: 15%">
                    '. $nombre_poliza_ .'
                </td>
                <td class="estilo_celda" style="width: 10%;text-align: center;">
                    ' . $cuentas_[fecha] . ' 
                </td>
                <td class="estilo_celda" style="width: 10%;text-align: center;">
                    ' . $_debe . ' 
                </td>
                <td class="estilo_celda" style="text-align: center;width: 15%;">
                    ' . $_haber . ' 
                </td>
                <td class="estilo_celda" style="text-align: center;width: 15%;">
                    Q' . number_format($saldo_actual, 2, '.', ',') . '
                </td>

            </tr>
        ';

        if ($cuentas_[monto] > 0) {
            $sumatoria_debe += $cuentas_[monto];
        } else {
            $sumatoria_haber += ($cuentas_[monto] * -1);
        }

        $saldo_anterior = $saldo_actual;

        $sumatoria_saldo_actual += $saldo_anterior;
        
    }


    $html .= '

    <tr>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        
        
        <td class="estilo_celda fondo_gris_titulo" style="text-align: right;">
            Sumas
        </td>
        <td class="estilo_celda fondo_gris_titulo" style="text-align: center">Q' . number_format($sumatoria_debe, 2, '.', ',') . '</td>
        <td class="estilo_celda fondo_gris_titulo" style="text-align: center" >Q' . number_format($sumatoria_haber, 2, '.', ',') . '</td>
        <td class="estilo_celda fondo_gris_titulo" style="text-align: center"></td>
    </tr>

    <tr>
        <td class="estilo_celda" style="height: 20px;"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
    </tr>
';

}

$html .= '
        </table>
    ';

$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Reporte_Libro_Mayor_por_cuenta.pdf', 'I');
