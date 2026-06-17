<?php
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$cuenta = $_GET['cuenta'];
$token = $_GET['token'];
$env = $_GET['env'];

$url = $env == 'p' ? "http://coopesitrabi.ddns.net/app/coope/api/bancos-transacciones/c/listado_notas_credito?fi=" . $fecha_inicial . "&ff=" . $fecha_final . "&cuenta=" . $cuenta : "http://localhost:8009/api/bancos-transacciones/c/listado_notas_credito?fi=" . $fecha_inicial . "&ff=" . $fecha_final . "&cuenta=" . $cuenta;


$query_listado = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto = stream_context_create($query_listado);
$respuesta = json_decode(file_get_contents($url, false, $contexto), true);


$suma_total = 0;

// print_r($respuesta[data]);
// die();


$html = '
    
    <div class="titulo">
        COOPERATIVA SITRABI, R.L.
    </div>
    <div class="titulo_sub">
        LISTADO DE NOTAS DE CRÉDITO DEL ' . date('d/m/Y', strtotime($fecha_inicial)) . ' AL ' . date('d/m/Y', strtotime($fecha_final)) . '
    </div>

    <div style="margin-top: 20px;">
    </div>

    <table class="table">

        <tr>
            <td class="estilo_celda">
                Banco: ' . $respuesta[data][info][nombre_banco] . '
            </td>   
            <td class="estilo_celda">
                
            </td>   
        </tr>
        <tr>
            <td class="estilo_celda">
                Cuenta: ' . $respuesta[data][info][cuenta] . '
            </td>
            <td class="estilo_celda">
                
            </td>   
        </tr>
        <tr>
            <td class="estilo_celda">
                Valor expresado en: ' . $respuesta[data][info][moneda] . '
            </td>
            <td class="estilo_celda">
                
            </td>   
        </tr>

    </table>

    <div style="margin-top: 20px;">
    </div>

    <table border="1" class="table">
        <thead>
            <tr>
                <td class="fecha_registro" style="width: 25%;text-align: left;">
                    Documento
                </td>
                <td class="fecha_registro" style="width: 10%;">
                    Fecha
                </td>
                <td class="fecha_registro" style="width: 20%;">
                    Monto
                </td>
                <td class="fecha_registro" style="width: 45%;text-align: left;">
                    Motivo
                </td>
            </tr>
        </thead>
        <tbody>';

foreach ($respuesta[data][consulta] as $key) {

    $html .= '
        <tr>
            <td class="estilo_celda" style="width: 25%;">
                ' . $key[numero_documento] . '
            </td>
            <td class="estilo_celda" style="width: 10%;text-align: center;">
                ' . date('d/m/Y', strtotime($key[fecha])) . '
            </td>
            <td class="estilo_celda" style="width: 20%;text-align: center;">
                Q' . number_format($key[monto], 2, '.', ',') . '
            </td>
            <td class="estilo_celda" style="width: 45%;">
                ' . $key[comentarios] . '
            </td>
        </tr>
    ';


    $suma_total += $key[monto];
}



$html .=  '

        <tr>
            <td style="width: 25%;">

            </td>
            <td class="estilo_celda" style="width: 10%;text-align: center;">
                TOTAL
            </td>
            <td class="estilo_celda" style="width: 20%;text-align: center;">
                Q' . number_format($suma_total, 2, '.', ',') . '
            </td>
            <td style="width: 45%;">
                
            </td>
        </tr>


    </tbody>
        
    </table>


';

$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Reporte_Notas_de_Debito.pdf', 'I');
