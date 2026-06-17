<?php
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$tipo_resumen = $_GET['tipo_resumen'];
$centro_de_costo = $_GET['centro_de_costo'];
$tipo_de_poliza = $_GET['tipo_de_poliza'];
$token = $_GET['token'];
$env = $_GET['env'];


$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_libro_diario" : "http://100.78.93.50:8009/api/contabilidad-transaccion-cabeceras/c/reporte_partida_inicial";
$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-tipo-de-polizas" : "http://100.78.93.50:8009/api/contabilidad-tipo-de-polizas";

$data = array(
    "data" => array(
        "fecha_inicial" => $fecha_inicial,
        "fecha_final" => $fecha_final,
        "tipo_resumen" => $tipo_resumen,
        "centro_de_costo" => $centro_de_costo,
        "tipo_de_poliza" => $tipo_de_poliza
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


// $opciones = array('http' => array(
//     'method' => 'GET',
//     'header' => 'Authorization: Bearer ' . $token,
// ));

$contexto = stream_context_create($opciones);
$respuesta = json_decode(file_get_contents($url, false, $contexto), true);



// NOMBRES DE POLIZAS

$opciones2 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto2 = stream_context_create($opciones2);
$polizas_ = json_decode(file_get_contents($url2, false, $contexto2), true);




function convertir_nombre_poliza($polizas, $id)
{


    for ($i = 0; $i < count($polizas[data]); $i++) {
        if ($polizas[data][$i][id] == $id) {
            return $polizas[data][$i][attributes][nombre];
            break;
        }
    }
}


$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo">
            PARTIDA DE APERTURA 
        </div>
        <table class="table">

    ';

foreach ($respuesta[data] as $key) {


    $_tipo_documento = '';

    if ($key[sistema] == 'CONTA') {
        $_tipo_documento = 'POLIZA';
    } else {
        $prefijo = substr($key[sistema], 0, 3) . "-" . substr($key[modulo], 0, 3);
        $_tipo_documento = $prefijo;
    }


    $html .= '
            <tr>
                <td colspan=1 class="fecha_registro">
                    TIPO: ' . convertir_nombre_poliza($polizas_, $key[poliza]) . '
                </td>
                <td colspan=1 class="fecha_registro">
                    ' . $_tipo_documento . '-' . $key['numero_documento'] . '
                </td>
                <td colspan=1 class="fecha_registro">
                    ' . $key[fecha] . '
                </td>
                <td colspan=1 class="fecha_registro" style="font-size: 8px;">
                    CC: ' . $key[centro_de_costo] . '
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

    foreach ($key[detalle] as $detail) {
        $_debe = 0;
        $_haber = 0;

        if ($detail[monto] > 0) {
            $_debe = 'Q' . number_format($detail[monto], 2, '.', ',');
            $_haber = '';
        } else {
            $_debe = '';
            $_haber = 'Q' . number_format(($detail[monto] * -1), 2, '.', ',');
        }


        $html .= '
                    <tr>
                        <td class="estilo_celda" style="width: 20%";>
                            ' . $detail[codigo_cuenta] . ' 
                        </td>
                        <td class="estilo_celda" style="width: 50%";>
                            ' . $detail[nombre_cuenta] . ' 
                        </td>
                        <td class="estilo_celda" style="text-align: center;width: 15%;">
                            ' . $_debe . '
                        </td>
                        <td class="estilo_celda" style="text-align: center;width: 15%;">
                            ' . $_haber . '
                        </td>
        
                    </tr>';

        if ($detail[monto] > 0) {
            $sumatoria_debe += $detail[monto];
        } else {
            $sumatoria_haber += ($detail[monto] * -1);
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
                <td class="estilo_celda"></td>
                
                <td class="estilo_celda" style="text-align: right;">
                    Sumas iguales: 
                </td>
                <td class="estilo_celda" style="text-align: center">Q' . number_format($sumatoria_debe, 2, '.', ',') . '</td>
                <td class="estilo_celda" style="text-align: center" >Q' . number_format($sumatoria_haber, 2, '.', ',') . '</td>
            </tr>
            <tr>
                <td class="estilo_celda"></td>
                <td class="estilo_celda" style="text-align: right;">
                    Descuadre: 
                </td>
                <td class="estilo_celda"></td>
                <td class="estilo_celda"></td>
            </tr>
            <tr>
                <td class="estilo_celda" style="font-weight: bold ;">Comentarios:</td>
                <td colspan=3 class="estilo_celda">' . $key[comentarios]  . '</td>
            </tr>
            <tr>
                <td class="estilo_celda" style="height: 20px;"></td>
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

$mpdf->Output('Reporte_Libro_Diario.pdf', 'I');
