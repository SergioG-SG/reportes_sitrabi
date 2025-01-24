<?php
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$ids = $_GET['ids'];
$token = $_GET['token'];
$env = $_GET['env'];


$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transacciones?filters[id_random][\$eq]=". $ids . "&pagination[page]=1&pagination[pageSize]=500" : "http://100.78.93.50:8009/api/contabilidad-transacciones?filters[id_random][\$eq]=". $ids . "&pagination[page]=1&pagination[pageSize]=500";


$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras?filters[id_random][\$eq]=" . $ids : "http://100.78.93.50:8009/api/contabilidad-transaccion-cabeceras?filters[id_random][\$eq]=" . $ids;


$url4 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-tipo-de-polizas" : "http://100.78.93.50:8009/api/contabilidad-tipo-de-polizas";


// $data = array(
//     "data" => array(
//         "fecha_inicial" => $fecha_inicial,
//         "fecha_final" => $fecha_final,
//         "tipo_resumen" => $tipo_resumen,
//         "centro_de_costo" => $centro_de_costo,
//         "tipo_de_poliza" => $tipo_de_poliza
//     )
// );


// $opciones = array(
//     'http' => array(
//         'method' => 'POST',
//         'header' => array(
//             'Content-Type: application/json',
//             'Authorization: Bearer ' . $token
//         ),
//         'content' => json_encode($data)
//     )
// );



// CABECERA DE LA POLIZA

$opciones2 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto2 = stream_context_create($opciones2);
$cabecera = json_decode(file_get_contents($url2, false, $contexto2), true);


// print_r($cabecera[data][0][attributes][poliza]);
// die();


// DETALLE DE LA POLIZA
$opciones = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto = stream_context_create($opciones);
$detalle = json_decode(file_get_contents($url, false, $contexto), true);

// NOMBRE POLIZAS

$opciones4 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto4 = stream_context_create($opciones4);
$polizas_ = json_decode(file_get_contents($url4, false, $contexto4), true);


function convertir_nombre_poliza($polizas, $id)
{


    for ($i = 0; $i < count($polizas[data]); $i++) {
        if ($polizas[data][$i][id] == $id) {
            return $polizas[data][$i][attributes][nombre];
            break;
        }
    }
}


function obtener_nombre_cuenta($codigo, $token, $empresa){

    $url_3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/catalogo-de-cuentas?filters[codigo_formateado][\$eq][0]=". $codigo ."&filters[empresa][\$eq][1]=1" : "http://100.78.93.50:8009/api/catalogo-de-cuentas?filters[codigo_formateado][\$eq][0]=". $codigo ."&filters[empresa][\$eq][1]=" . $empresa;

    $opciones_3 = array('http' => array(
        'method' => 'GET',
        'header' => 'Authorization: Bearer ' . $token,
    ));

    $contexto_3 = stream_context_create($opciones_3);
    $datos = json_decode(file_get_contents($url_3, false, $contexto_3), true);


    return $datos[data][0][attributes][nombre_cuenta];

}





$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo">
            POLIZA
        </div>
        <table class="table">

    ';







    $html .= '
            <tr>
                <td colspan=1 class="fecha_registro">
                    TIPO: '. convertir_nombre_poliza($polizas_, $cabecera[data][0][attributes][poliza]) .'
                </td>
                <td colspan=1 class="fecha_registro">
                    FECHA: '. $cabecera[data][0][attributes][fecha] .'
                </td>
                <td colspan=1 class="fecha_registro">
                    No. Doc: '. $cabecera[data][0][attributes][numero_documento] .'
                </td>
                <td colspan=1 class="fecha_registro" style="font-size: 8px;">

                </td>
            </tr>
            <tr>
                <td class="estilo_celda fondo_gris_titulo">
                    No. CUENTA
                </td>
                <td class="estilo_celda fondo_gris_titulo">
                    NOMBRE CUENTA
                </td>
                <td class="estilo_celda fondo_gris_titulo centrar_texto">
                    DEBE
                </td>
                <td class="estilo_celda fondo_gris_titulo centrar_texto">
                    HABER
                </td>
            </tr>';

    $sumatoria_debe = 0;
    $sumatoria_haber = 0;

    foreach ($detalle[data] as $detail) {
        $_debe = 0;
        $_haber = 0;

        if ($detail[attributes][monto] > 0) {
            $_debe = 'Q' . number_format(abs($detail[attributes][monto]), 2, '.', ',');
            $_haber = '';
        } else {
            $_debe = '';
            $_haber = 'Q' . number_format(abs($detail[attributes][monto]), 2, '.', ',');
        }


        $html .= '
                    <tr>
                        <td class="estilo_celda" style="width: 20%";>
                            ' . $detail[attributes][codigo_cuenta] . ' 
                        </td>
                        <td class="estilo_celda" style="width: 50%";>
                            ' . obtener_nombre_cuenta($detail[attributes][codigo_cuenta], $token, 1) . ' 
                        </td>
                        <td class="estilo_celda" style="text-align: center;width: 15%;">
                            ' . $_debe . '
                        </td>
                        <td class="estilo_celda" style="text-align: center;width: 15%;">
                            ' . $_haber . '
                        </td>
        
                    </tr>';

        if ($detail[attributes][monto] > 0) {
            $sumatoria_debe += abs($detail[attributes][monto]);
        } else {
            $sumatoria_haber += abs($detail[attributes][monto]);
        }
    }

    $html .= '
            <tr>
                <td class="estilo_celda"></td>
                <td class="estilo_celda"></td>
                <td class="estilo_celda" style="border-top:1px solid black;"></td>
                <td class="estilo_celda" style="border-top:1px solid black;"></td>
            </tr>
            <tr>
                <td class="estilo_celda">
                    Resumen movimiento
                </td>
                
                <td class="estilo_celda" style="text-align: right;">
                    Sumas iguales: 
                </td>
                <td class="estilo_celda" style="text-align: center">Q' . number_format($sumatoria_debe, 2, '.', ',') . '</td>
                <td class="estilo_celda" style="text-align: center" >Q' . number_format($sumatoria_haber, 2, '.', ',') . '</td>
            </tr>
            <tr>
                <td class="estilo_celda"></td>
                <td class="estilo_celda" style="text-align: right;">
                    
                </td>
                <td class="estilo_celda"></td>
                <td class="estilo_celda"></td>
            </tr>
            <tr>
                <td class="estilo_celda" style="height: 20px;"></td>
                <td class="estilo_celda"></td>
                <td class="estilo_celda"></td>
                <td class="estilo_celda"></td>
            </tr>
        ';


$html .= '
        </table>
    ';


$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Impresion_Partida.pdf', 'I');
