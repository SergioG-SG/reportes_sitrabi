<?php
    date_default_timezone_set('America/Guatemala');

    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET');

    require_once('mpdf/mpdf.php');

    //$token = $_GET['token'];

    $url = "";
    // $opciones = array('http' => array(
    //     'method' => 'GET',
    //     'header' => 'Authorization: Bearer ' . $token,
    // ));

    // $contexto = stream_context_create($opciones);
    // $respuesta = json_decode(file_get_contents($url, false, $contexto), true);


    $html = '
        <h1>Línea 2</h1>    

    ';

    $html .= '
        <h1>Línea 1</h1>
    
    ';

    $mpdf = new mPDF('', 'letter', '', '', 5, 5, 0, 0);
    $css = file_get_contents('css/estilos.css');
    $mpdf->writeHTML($css, 1);
    $mpdf->writeHTML($html);

    $mpdf->Output('Reporte.pdf', 'I');


?>