<?php

date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');



require_once('mpdf/mpdf.php');


// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$centro_de_costo = $_GET['centro_de_costo'];
$token = $_GET['token'];
$env = $_GET['env'];
$ip_dev = "192.168.1.68";


//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://coopesitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos" : "http://" . $ip_dev . ":8009/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos";

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


// NOMBRES DE CENTROS DE COSTO

$url5 = $env == 'p' ? "https://coopesitrabi.ddns.net/app/coope/api/centros-de-costos/c/nombre/" . $centro_de_costo : "http://" . $ip_dev . ":8009/api/centros-de-costos/c/nombre/" . $centro_de_costo;

$opciones5 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
    'timeout' => 10
));

$contexto5 = stream_context_create($opciones5);
$ccostos = json_decode(@file_get_contents($url5, false, $contexto5), true);

$nombre_centro_de_costo = $centro_de_costo == 0 ? "GENERAL" : $ccostos[0][nombre];


// OBTENER CATALOGO DE CUENTAS


// $opciones = array('http' => array(
//     'method' => 'GET',
//     'header' => 'Authorization: Bearer ' . $token,
// ));

$contexto = stream_context_create($opciones);
$respuesta = json_decode(file_get_contents($url, false, $contexto), true);


$suma_debe = 0;
$suma_haber = 0;

// print_r($respuesta);
// die();

function imprimirDatos($filas, $level = 0)
{
    $html = '';
    foreach ($filas as $item) {

        $saldo_anterior = $item[saldo_anterior] == 0 ? '' : number_format($item[saldo_anterior], 2, '.', ',');
        $debe = $item[debe] == 0 ? '' : number_format($item[debe], 2, '.', ',');
        $haber = $item[haber] == 0 ? '' : number_format($item[haber], 2, '.', ',');
        $saldo_final = $item[saldo_final] == 0 ? '' : number_format($item[saldo_final], 2, '.', ',');

        global $suma_debe, $suma_haber;

        // $suma_debe += $item[tipo_de_cuenta] == "D" ? $item[debe] : 0;
        // $suma_haber += $item[tipo_de_cuenta] == "D" ? $item[haber] : 0;
        $suma_debe += ($item['tipo_de_cuenta'] == "M" && strlen($item['codigo']) == 1) ? $item['debe'] : 0;
        $suma_haber += ($item['tipo_de_cuenta'] == "M" && strlen($item['codigo']) == 1) ? $item['haber'] : 0;


        if ($item['tipo_de_cuenta'] == "M") {
            $html .= '<tr>';
            $html .= '<td class="estilo_celda2 fondo_gris_titulo estilo_bold">' . $item['codigo'] . '</td>';
            $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold">' . $item['nombre_cuenta'] . '</td>';
            $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold texto_derecha">' . $saldo_anterior . '</td>';
            $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold texto_derecha">' . $debe . '</td>';
            $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold texto_derecha">' . $haber . '</td>';
            $html .= '<td class="estilo_celda fondo_gris_titulo estilo_bold texto_derecha">' . $saldo_final . '</td>';
            $html .= '</tr>';
        } else {


            $html .= '<tr>';
            $html .= '<td class="estilo_celda2">' . $item['codigo'] . '</td>';
            $html .= '<td class="estilo_celda">' . $item['nombre_cuenta'] . '</td>';
            $html .= '<td class="estilo_celda texto_derecha">' . $saldo_anterior . '</td>';
            $html .= '<td class="estilo_celda texto_derecha">' . $debe . '</td>';
            $html .= '<td class="estilo_celda texto_derecha">' . $haber . '</td>';
            $html .= '<td class="estilo_celda texto_derecha">' . $saldo_final . '</td>';
            $html .= '</tr>';
        }



        if (!empty($item['detalle'])) {
            $html .= imprimirDatos($item['detalle'], $level + 1);
        }
    }

    return $html;
}


$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo">
            BALANCE DE SALDOS ' . date('d/m/Y', strtotime($fecha_inicial)) . ' al ' . date('d/m/Y', strtotime($fecha_final)) . '
        </div>
        <div class="titulo">
            ** ' . $nombre_centro_de_costo . ' **
        </div>
        <table class="table">
            <tr>
                <td class="estilo_celda fecha_registro" style="width: 15%;">
                    CÓDIGO
                </td>
                <td class="estilo_celda fecha_registro" style="width: 35%; text-align: left !important;">
                    NOMBRE CUENTA
                </td>
                <td class="estilo_celda fecha_registro centrar_texto" style="width: 12.5%;">
                    S.INICIAL
                </td>
                <td class="estilo_celda fecha_registro centrar_texto" style="width: 12.5%;">
                    DEBE
                </td>
                <td class="estilo_celda fecha_registro centrar_texto" style="width: 12.5%;">
                    HABER
                </td>
                <td class="estilo_celda fecha_registro centrar_texto" style="width: 12.5%;">
                    SALDO
                </td>
            </tr>';




$html .= imprimirDatos($respuesta);


$html .= '
                <tr>
                    <td class="estilo_celda" style="width: 15%;">
                        
                    </td>
                    <td class="estilo_celda" style="width: 35%; text-align: left !important;">
                        
                    </td>
                    <td class="estilo_celda texto_derecha" style="width: 12.5%;border-top: 1px solid #000;">
                        0.00
                    </td>
                    <td class="estilo_celda texto_derecha" style="width: 12.5%;border-top: 1px solid #000;">
                        ' . number_format($suma_debe, 2, '.', ',') . '
                    </td>
                    <td class="estilo_celda texto_derecha" style="width: 12.5%;border-top: 1px solid #000;">
                        ' . number_format($suma_haber, 2, '.', ',') . '
                    </td>
                    <td class="estilo_celda texto_derecha" style="width: 12.5%;border-top: 1px solid #000;">
                        0.00
                    </td>
                </tr>
            ';


$html .= '        
        </table>
    ';


$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Reporte_Balance_Saldos.pdf', 'I');
