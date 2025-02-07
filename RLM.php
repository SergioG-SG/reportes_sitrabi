<?php
set_time_limit(120);

date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');



require_once('mpdf/mpdf.php');


// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$centro_de_costo = $_GET['cc'];
$tipo_de_poliza = $_GET['tp'];
$cod_cuenta = $_GET['cuenta'];
$empresa = $_GET['emp'];
$token = $_GET['token'];
$env = $_GET['env'];


//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transacciones/c/libro_mayor?fecha_inicial=". $fecha_inicial ."&fecha_final=" . $fecha_final ."&centro_de_costo=" . $centro_de_costo ."&tipo_de_poliza=". $tipo_de_poliza ."&empresa=". $empresa . "&cuenta=" . $cod_cuenta : "http://100.78.93.50:8009/api/contabilidad-transacciones/c/libro_mayor?fecha_inicial=". $fecha_inicial ."&fecha_final=" . $fecha_final ."&centro_de_costo=" . $centro_de_costo ."&tipo_de_poliza=". $tipo_de_poliza ."&empresa=". $empresa . "&cuenta=" . $cod_cuenta;


// OBTENER CATALOGO DE CUENTAS


$opciones = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
    'timeout' => 10
));

$contexto = stream_context_create($opciones);
$respuesta = json_decode(@file_get_contents($url, false, $contexto), true);


$gran_suma_debe = 0;
$gran_suma_haber = 0;
$gran_total = $gran_suma_debe - $gran_suma_haber;

// OBTENER NOMBRES DE POLIZAS

$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-tipo-de-polizas" : "http://100.78.93.50:8009/api/contabilidad-tipo-de-polizas";


$opciones2 = array('http' => array(
    'method' => 'GET',
    'header' => 'Authorization: Bearer ' . $token,
     'timeout' => 10
));

$contexto2 = stream_context_create($opciones2);
$respuesta2 = json_decode(@file_get_contents($url2, false, $contexto2), true);



function buscar_nombre_poliza($array, $codigo){

    foreach ($array as $key) {
        
        if($codigo == $key[id]){
            return $key[attributes][nombre];
        }

    }

}

$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo">
            CONSOLIDADO DE PARTIDAS DE DIARIO ' . date('d/m/Y', strtotime($fecha_inicial)) . ' al ' . date('d/m/Y', strtotime($fecha_final)) . '
        </div>
        <table class="table">
            
    ';


foreach ($respuesta as $key) {
    $html .= '
            <tr>
                <td class="fecha_registro">
                    ' .$key[codigo_titulo] . '
                </td>
                <td class="fecha_registro">
                    ' .$key[nombre_cuenta] . '
                </td>esti
                <td class="fecha_registro">
                    
                </td>
                <td class="fecha_registro">
                    
                </td>
                <td class="fecha_registro">
                    
                </td>
                <td class="fecha_registro" style="font-size: 8px;">
                    
                </td>
                <td class="fecha_registro" style="font-size: 8px;">

                </td>
            </tr>
            <tr>
                <td class="estilo_celda fondo_gris_titulo">
                    Fecha
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    No.Documento
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    Centro de costo
                </td>
                <td class="estilo_celda fondo_gris_titulo" style="text-align: center;">
                    Póliza
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
    
    
    foreach ($key[registros_por_fecha] as $cuentas_) {
        
        
        $sumatoria_saldo_actual = 0;
        $saldo_actual = 0;
        
        foreach ($cuentas_[registros] as $regs_) {


            if ($regs_[monto] > 0) {
                $_debe_crudo = $regs_[monto];
                $_haber_crudo = 0;
                $_debe = 'Q' . number_format($regs_[monto], 2, '.', ',');
                $_haber = '';
            } else {
                $_debe_crudo = 0;
                $_haber_crudo = $regs_[monto] * -1;
                $_debe = '';
                $_haber = 'Q' . number_format(($regs_[monto] * -1), 2, '.', ',');
            }
    
    
            $nombre_poliza_ = buscar_nombre_poliza($respuesta2[data], $regs_[poliza]);
            $saldo_actual = $_debe_crudo - $_haber_crudo;

            $html .= '
                <tr>
                    <td class="estilo_celda" style="width: 20%;">
                        ' . date('d/m/Y', strtotime($regs_[fecha])) . ' 
                    </td>
                    <td class="estilo_celda" style="width: 10%;text-align: center;">
                        ' . $regs_[numero_documento] . ' 
                    </td>
                    <td class="estilo_celda" style="text-align: center;width: 15%">
                        ' . $regs_[centro_de_costo] . ' 
                    </td>
                    <td class="estilo_celda" style="width: 10%;text-align: center;">
                        '. $nombre_poliza_ .'
                    </td>
                    <td class="estilo_celda" style="width: 15%;text-align: center;">
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

           
            if ($regs_[monto] > 0) {
                $sumatoria_debe += $regs_[monto];
            } else {
                $sumatoria_haber += ($regs_[monto] * -1);
            }


            $sumatoria_saldo_actual += $saldo_actual;

        }

        $html .= '
        <tr>
                <td>

                </td>
                <td>
                    
                </td>
                <td >
                    
                </td>
                <td >

                </td>
                <td style="border-bottom: 1px solid #e9e9e9;">
                    
                </td>
                <td style="border-bottom: 1px solid #e9e9e9;">
                
                </td>
                <td style="border-bottom: 1px solid #e9e9e9;">
                    
                </td>
            </tr>
            <tr>
                <td class="estilo_celda fondo_gris_titulo">
                    TOTAL DEL DIA:
                </td>
                <td>
                    
                </td>
                <td >
                    
                </td>
                <td >

                </td>
                <td class="estilo_celda" style="text-align: center;font-weight: bold;font-size:12px;">
                    Q'.  number_format($cuentas_[sub_suma_debe], 2, '.', ',') .'
                </td>
                <td class="estilo_celda" style="text-align: center;font-weight: bold;font-size:12px;">
                    Q'.  number_format($cuentas_[sub_suma_haber], 2, '.', ',') .'
                </td>
                <td class="estilo_celda" style="text-align: center;font-weight: bold;font-size:12px;">
                    Q('.  number_format($sumatoria_saldo_actual, 2, '.', ',') .')
                </td>
            </tr>
             <tr>
                <td style="height: 20px;">
                </td>
                <td>
                    
                </td>
                <td >
                    
                </td>
                <td >

                </td>
                <td>
                    
                </td>
                <td>
                
                </td>
                <td>
                    
                </td>
            </tr>

        ';


       
    }


    $gran_suma_debe += $sumatoria_debe;
    $gran_suma_haber += $sumatoria_haber;
    


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
        <td class="estilo_celda fondo_gris_titulo" style="text-align: center" ></td>
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

// $html .= '

//      <tr>
//         <td class="estilo_celda" style="height: 20px;"></td>
//         <td class="estilo_celda"></td>
//         <td class="estilo_celda"></td>
//         <td class="estilo_celda">GRAN TOTAL</td>
//         <td class="estilo_celda">Q'. number_format($gran_suma_debe, 2, '.', ',')  .'</td>
//         <td class="estilo_celda">Q'. number_format($gran_suma_haber, 2, '.', ',') .'</td>
//         <td class="estilo_celda"></td>
//     </tr>
// ';



}

$html .= '

        <tr>
            <td class="estilo_celda" style="height: 20px;"></td>
            <td class="estilo_celda"></td>
            <td class="estilo_celda"></td>
            <td class="estilo_celda" style="font-size: 15px;font-weight: bold;">GRAN TOTAL</td>
            <td class="estilo_celda" style="font-size: 15px;font-weight: bold;">Q'. number_format($gran_suma_debe, 2, '.', ',')  .'</td>
            <td class="estilo_celda" style="font-size: 15px;font-weight: bold;">Q'. number_format($gran_suma_haber, 2, '.', ',') .'</td>
            <td class="estilo_celda" style="font-size: 15px;font-weight: bold;">Q'. number_format($gran_total, 2, '.', ',') .'</td>
        </tr>

        </table>
    ';

$mpdf = new mPDF('', 'letter', '', '', 7, 7, 7, 7);
$css = file_get_contents('css/estilos.css');
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);

$mpdf->Output('Reporte_Libro_Mayor.pdf', 'I');
