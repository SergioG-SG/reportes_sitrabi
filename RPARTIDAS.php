<?php

date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');



require_once('mpdf/mpdf.php');


// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$centro_de_costo = $_GET['cc'];
$tipo_de_poliza =$_GET['tp'];
$empresa = $_GET['emp'];
$token = $_GET['token'];
$env = $_GET['env'];




//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/r/filtro_polizas?fecha_inicial=". $fecha_inicial ."&fecha_final=". $fecha_final ."&empresa=". $empresa ."&centro_de_costo=". $centro_de_costo ."&tipo_de_poliza=" . $tipo_de_poliza : "http://100.78.93.50:8009/api/contabilidad-transaccion-cabeceras/r/filtro_polizas?fecha_inicial=". $fecha_inicial ."&fecha_final=". $fecha_final ."&empresa=". $empresa ."&centro_de_costo=". $centro_de_costo ."&tipo_de_poliza=" . $tipo_de_poliza;




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

$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-tipo-de-polizas?filters[empresa][\$eq]=" . $empresa : "http://100.78.93.50:8009/api/contabilidad-tipo-de-polizas?filters[empresa][\$eq]=" . $empresa;


$opciones2 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto2 = stream_context_create($opciones2);
$respuesta2 = json_decode(file_get_contents($url2, false, $contexto2), true);


// OBTENER NOMBRES DE CENTROS DE COSTO

$url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/centros-de-costos?filters[empresa][\$eq]=" . $empresa : "http://100.78.93.50:8009/api/centros-de-costos?filters[empresa][\$eq]=" . $empresa;


$opciones3 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
));

$contexto3 = stream_context_create($opciones3);
$respuesta3 = json_decode(file_get_contents($url3, false, $contexto3), true);



function buscar_nombre_poliza($array, $codigo){

    foreach ($array as $key) {
        
        if($codigo == $key[id]){
            return $key[attributes][nombre];
        }

    }

}




function buscar_nombre_centro_de_costo($array, $codigo){

    foreach ($array as $key) {
        
        if($codigo == $key[attributes][codigo]){
            return $key[attributes][nombre];
        }

    }

}

$name_centro_costo = $centro_de_costo == 'general' ?  'GENERAL': buscar_nombre_centro_de_costo($respuesta3[data], $centro_de_costo);
$name_tipo_poliza = $tipo_de_poliza == 'general' ? 'GENERAL': buscar_nombre_poliza($respuesta2[data], $tipo_de_poliza);

$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo">
            REPORTE POLIZAS ' . date('d/m/Y', strtotime($fecha_inicial)) . ' al ' . date('d/m/Y', strtotime($fecha_final)) . ' <br>
        </div>
        <div class="titulo_sub">
        CENTRO DE COSTO: '. $name_centro_costo .' ****
            TIPO DE PÓLIZA: '. $name_tipo_poliza .'
            
        </div>
        <table class="table">
            
    ';


// foreach ($respuesta as $key) {
    $html .= '
            <tr>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
                <td colspan="1" class="fecha_registro">
                    
                </td>
            </tr>
            <tr>
                <td class="estilo_celda fondo_gris_titulo">
                    Documento
                </td>
                <td class="estilo_celda fondo_gris_titulo">
                    Tipo póliza
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    Centro de costo
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    Fecha
                </td>
                <td class="estilo_celda fondo_gris_titulo centrar_texto">
                    Monto
                </td>
            </tr>';

    $sumatoria_debe = 0;
    $sumatoria_haber = 0;
        

    foreach ($respuesta[data] as $key) {

        // if ($cuentas_[monto] > 0) {
        //     $_debe = 'Q' . number_format($cuentas_[monto], 2, '.', ',');
        //     $_haber = '';
        // } else {
        //     $_debe = '';
        //     $_haber = 'Q' . number_format(($cuentas_[monto] * -1), 2, '.', ',');
        // }


        $nombre_poliza_ = buscar_nombre_poliza($respuesta2[data], $key[poliza]);
        $nombre_centro_de_costo = buscar_nombre_centro_de_costo($respuesta3[data], $key[centro_de_costo]);

        $html .= '
            <tr>
                <td class="estilo_celda" style="width: 20%">
                    ' . $key[numero_documento] . ' 
                </td>
                <td class="estilo_celda" style="width: 15%">
                    '. $nombre_poliza_ .'
                </td>
                <td class="estilo_celda" style="text-align: center;width: 30%">
            '. $nombre_centro_de_costo .'
                </td>
                <td class="estilo_celda" style="width: 10%;text-align: center;">
                    '. $key[fecha] .'
                </td>
                <td class="estilo_celda" style="width: 25%;text-align: center;">
                   Q'. number_format($key[monto], 2, '.', ',') .'
                </td>


            </tr>
        ';

   
            $sumatoria_debe += $key[monto];
   
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
    </tr>

    <tr>
        <td class="estilo_celda" style="height: 20px;"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
        <td class="estilo_celda"></td>
    </tr>
';

// }

$html .= '
        </table>
    ';

$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Reportes_Partidas.pdf', 'I');
