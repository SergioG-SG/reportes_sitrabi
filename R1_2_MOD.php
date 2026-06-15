<?php
set_time_limit(120); // Aumentar el límite de tiempo de ejecución a 120 segundos
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');


$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$centro_de_costo = 0;
$token = $_GET['token'];
$env = $_GET['env'];
$ip_dev = "192.168.1.68";

//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "http://186.151.206.62/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte-balance-saldos-ssi" : "http://". $ip_dev .":8009/api/contabilidad-transaccion-cabeceras/c/reporte-balance-saldos-ssi";

$url2 = $env == 'p' ? "http://186.151.206.62/app/coope/api/centros-de-costos/c/nombre/" . $centro_de_costo : "http://". $ip_dev .":8009/api/centros-de-costos/c/nombre/" . $centro_de_costo;


// consulta al gparametros para traer el saldo final que se guarda manualmente // tiene un 0 al final porque es el centro de costo que pide pero que se ha desabilitado
// $url3 = $env == 'p' ? "http://186.151.206.62/app/coope/api/gparametros/c/parametros/0" : "http://". $ip_dev .":8009/api/gparametros/c/parametros/0";

$url3 = $env == 'p' ? "http://186.151.206.62/app/coope/api/gparametros/c/inventario_por_trimestre/". $fecha_final : "http://". $ip_dev .":8009/api/gparametros/c/inventario_por_trimestre/". $fecha_final;


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

// echo ": ";
// print_r($respuesta3[inventario_final]);
// die();

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

// $es = buscar_cuenta_no_recursiva($respuesta, '6');
// print_r($respuesta);

// die();



$listado = [
    [
        "sel" => 0,
        "codigo" => 6,
        "codigo_formateado" => 6,
        "cuenta" => "PRODUCTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 613,
        "codigo_formateado" => 6.13,
        "cuenta" => "INGRESOS, OTROS NEGOCIOS SIN INTERMEDIACIÓN FINANCIERA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131,
        "codigo_formateado" => "6.13.1",
        "cuenta" => "INGRESOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 613101,
        "codigo_formateado" => "6.13.1.01",
        "cuenta" => "VENTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 61310101,
        "codigo_formateado" => "6.13.1.01.01",
        "cuenta" => "VENTAS ASOCIADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131010150,
        "codigo_formateado" => "6.13.1.01.01.50",
        "cuenta" => "Ventas al contado ",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131010151,
        "codigo_formateado" => "6.13.1.01.01.51",
        "cuenta" => "Ventas al crédito",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 61310102,
        "codigo_formateado" => "6.13.1.01.02",
        "cuenta" => "VENTAS A NO ASOCIADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131010201,
        "codigo_formateado" => "6.13.1.01.02.01",
        "cuenta" => "Ventas al contado ",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131010202,
        "codigo_formateado" => "6.13.1.01.02.02",
        "cuenta" => "Ventas al crédito",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 0,
        "cuenta" => "SUB-TOTAL",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 61310199,
        "codigo_formateado" => "6.13.1.01.99.",
        "cuenta" => "MENOS:  Devoluciones y descuentos",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131019901,
        "codigo_formateado" => "6.13.1.01.99.01",
        "cuenta" => " Descuentos sobre ventas",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131019902,
        "codigo_formateado" => "6.13.1.01.99.02",
        "cuenta" => "Devoluciones y rebajas sobre ventas",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7,
        "codigo_formateado" => "7.....",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 713,
        "codigo_formateado" => "7.13....",
        "cuenta" => "COSTOS Y GASTOS DE NEGOCIOS  ",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7131,
        "codigo_formateado" => "7.13.1...",
        "cuenta" => "COSTOS Y GASTOS  ",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 713101,
        "codigo_formateado" => "7.13.1.01..",
        "cuenta" => "COSTO DE VENTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 71310101,
        "codigo_formateado" => "7.13.1.01.01.",
        "cuenta" => "COMPRAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7131010101,
        "codigo_formateado" => "7.13.1.01.01.01",
        "cuenta" => "Compras al contado",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7131010102,
        "codigo_formateado" => "7.13.1.01.01.02",
        "cuenta" => "Compras al crédito",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 71310199,
        "codigo_formateado" => "7.13.1.01.99.",
        "cuenta" => "OTROS COSTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7131019903,
        "codigo_formateado" => "7.13.1.01.99.03",
        "cuenta" => "IVA Costo compras",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "SUB-TOTAL",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 71310101,
        "codigo_formateado" => "7.13.1.01.01.",
        "cuenta" => "COMPRAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7131010105,
        "codigo_formateado" => "7.13.1.01.01.05",
        "cuenta" => "MENOS : Devoluciones y rebajas sobre compras",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "SUB-TOTAL",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 113106,
        "codigo_formateado" => "1.13.1.06..",
        "cuenta" => " INVENTARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 1131060101, //1131060101
        "codigo_formateado" => "1.13.1.06.01.01",
        "cuenta" => "Inventario de mercaderías ( compras )",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "Total mercaderia disponible",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 113106,
        "codigo_formateado" => "1.13.1.06..",
        "cuenta" => "INVENTARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 1,
        "codigo" => 1131060101,
        "codigo_formateado" => "1.13.1.06.01.01",
        "cuenta" => " Inventario final de mercaderías",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => "34-111",
        "cuenta" => "EXCEDENTE BRUTO EN VENTAS",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131,
        "codigo_formateado" => "6.13.1...",
        "cuenta" => "INGRESOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 613104,
        "codigo_formateado" => "6.13.1.04..",
        "cuenta" => "OTROS PRODUCTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 61310401,
        "codigo_formateado" => "6.13.1.04.01.",
        "cuenta" => "OTROS PRODUCTOS DE ASOCIADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131040101,
        "codigo_formateado" => "6.13.1.04.01.01",
        "cuenta" => "Productos varios sección consumo",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 613106,
        "codigo_formateado" => "6.13.1.06..",
        "cuenta" => "MAS: COMISIONES SECCION CONSUMO",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 61310601,
        "codigo_formateado" => "6.13.1.06.01.",
        "cuenta" => "COMISIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131060101,
        "codigo_formateado" => "6.13.1.06.01.01",
        "cuenta" => "Comisiones por manejo de créditos consumo",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 6131060102,
        "codigo_formateado" => "6.13.1.06.01.02",
        "cuenta" => "Fletes",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 61310402,
        "codigo_formateado" => "6.13.1.04.02.",
        "cuenta" => "OTROS PRODUCTOS DE NO ASOCIADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => "42-319",
        "cuenta" => "Productos varios sección consumo",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => "34-111",
        "cuenta" => "TOTAL EXCEDENTE",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7,
        "codigo_formateado" => "7.....",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714,
        "codigo_formateado" => "7.14....",
        "cuenta" => "GASTOS SECCION CONSUMO (  FINANCIEROS )",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141,
        "codigo_formateado" => "7.14.1...",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714101,
        "codigo_formateado" => "7.14.1.01..",
        "cuenta" => "INTERESES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714101015001,
        "codigo_formateado" => "7.14.1.01.01.50.01",
        "cuenta" => "Intereses por aportaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714101020201,
        "codigo_formateado" => "7.14.1.01.02.02.01",
        "cuenta" => "Intereses por prestamos bancarios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714102,
        "codigo_formateado" => "7.14.1.02..",
        "cuenta" => "ORGANOS DIRECTIVOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141020104,
        "codigo_formateado" => "7.14.1.02.01.04",
        "cuenta" => "Otros",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141020201,
        "codigo_formateado" => "7.14.1.02.02.01",
        "cuenta" => "viáticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714103,
        "codigo_formateado" => "7.14.1.03..",
        "cuenta" => "FUNCIONARIOS Y EMPLEADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030101,
        "codigo_formateado" => "7.14.1.03.01.01",
        "cuenta" => "Sueldos permanentes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030102,
        "codigo_formateado" => "7.14.1.03.01.02",
        "cuenta" => "Sueldos eventuales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030201,
        "codigo_formateado" => "7.14.1.03.02.01",
        "cuenta" => "Aguinaldos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030301,
        "codigo_formateado" => "7.14.1.03.03.01",
        "cuenta" => "Indemnizaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030401,
        "codigo_formateado" => "7.14.1.03.04.01",
        "cuenta" => "Bonificacion incentivo ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030402,
        "codigo_formateado" => "7.14.1.03.04.02",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030501,
        "codigo_formateado" => "7.14.1.03.05.01",
        "cuenta" => "Cuota patronal Igss",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030601,
        "codigo_formateado" => "7.14.1.03.06.01",
        "cuenta" => "Vacaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030701,
        "codigo_formateado" => "7.14.1.03.07.01",
        "cuenta" => "Bono catorce",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030702,
        "codigo_formateado" => "7.14.1.03.07.02",
        "cuenta" => "Comisiones ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141030703,
        "codigo_formateado" => "7.14.1.03.07.03",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714104,
        "codigo_formateado" => "7.14.1.04..",
        "cuenta" => "TRIBUTOS Y OTRAS CUOTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141040101,
        "codigo_formateado" => "7.14.1.04.01.01",
        "cuenta" => "Impuesto derivado del Petroleo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141040102,
        "codigo_formateado" => "7.14.1.04.01.02",
        "cuenta" => "IVA costo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141040103,
        "codigo_formateado" => "7.14.1.04.01.03",
        "cuenta" => "I.S.R TRIMESTRAL",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141040104,
        "codigo_formateado" => "7.14.1.04.01.04",
        "cuenta" => "ISO TRIMESTRAL",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141040201,
        "codigo_formateado" => "7.14.1.04.02.01",
        "cuenta" => "Impuesto y arbitrios fiscales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 71410501,
        "codigo_formateado" => "7.14.1.05.01.",
        "cuenta" => "OTROS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141050101,
        "codigo_formateado" => "7.14.1.05.01.01",
        "cuenta" => "Honorarios profesionales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714106,
        "codigo_formateado" => "7.14.1.06..",
        "cuenta" => "REPARACIONES Y MANTENIMIENTO",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141060101,
        "codigo_formateado" => "7.14.1.06.01.01",
        "cuenta" => "Reparaciones de edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141060201,
        "codigo_formateado" => "7.14.1.06.02.01",
        "cuenta" => "Reparaciones de mobiliario y equipo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141060203,
        "codigo_formateado" => "7.14.1.06.02.03",
        "cuenta" => "Reparaciones de vehículos ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141060301,
        "codigo_formateado" => "7.14.1.06.03.01",
        "cuenta" => "Reparaciones de herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714107,
        "codigo_formateado" => "7.14.1.07..",
        "cuenta" => "MERCADEO Y PUBLICIDAD",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141070101,
        "codigo_formateado" => "7.14.1.07.01.01",
        "cuenta" => "Educación y mercadeo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714108,
        "codigo_formateado" => "7.14.1.08..",
        "cuenta" => "PRIMAS DE SEGUROS Y FIANZAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141080101,
        "codigo_formateado" => "7.14.1.08.01.01",
        "cuenta" => "Seguros y fianzas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714109,
        "codigo_formateado" => "7.14.1.09..",
        "cuenta" => "DEPRECIACIONES Y AMORTIZACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141090101,
        "codigo_formateado" => "7.14.1.09.01.01",
        "cuenta" => "Edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141090102,
        "codigo_formateado" => "7.14.1.09.01.02",
        "cuenta" => "Mobiliario y Equipo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141090103,
        "codigo_formateado" => "7.14.1.09.01.03",
        "cuenta" => "Vehículos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141090201,
        "codigo_formateado" => "7.14.1.09.02.01",
        "cuenta" => "Equipo de computación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141090202,
        "codigo_formateado" => "7.14.1.09.02.02",
        "cuenta" => "Herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141099901,
        "codigo_formateado" => "7.14.1.09.99.01",
        "cuenta" => "Amortización gastos de instalación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141099902,
        "codigo_formateado" => "7.14.1.09.99.02",
        "cuenta" => "Amortización cuentas por amortizar",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714110,
        "codigo_formateado" => "7.14.1.10..",
        "cuenta" => "GASTOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100101,
        "codigo_formateado" => "7.14.1.10.01.01",
        "cuenta" => "Servicio de seguridad",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100201,
        "codigo_formateado" => "7.14.1.10.02.01",
        "cuenta" => "Energía eléctrica ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100301,
        "codigo_formateado" => "7.14.1.10.03.01",
        "cuenta" => "Servicio de telecomunicaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100401,
        "codigo_formateado" => "7.14.1.10.04.01",
        "cuenta" => "Alquileres",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100402,
        "codigo_formateado" => "7.14.1.10.04.02",
        "cuenta" => "Accesorios para computadora",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100403,
        "codigo_formateado" => "7.14.1.10.04.03",
        "cuenta" => "Varios e imprevistos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100404,
        "codigo_formateado" => "7.14.1.10.04.04",
        "cuenta" => "Fletes ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100405,
        "codigo_formateado" => "7.14.1.10.04.05",
        "cuenta" => "Material de empaque",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141100406,
        "codigo_formateado" => "7.14.1.10.04.06",
        "cuenta" => "Combustibles y lubricantes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714111,
        "codigo_formateado" => "7.14.1.11..",
        "cuenta" => "PAPELERÍA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141110101,
        "codigo_formateado" => "7.14.1.11.01.01",
        "cuenta" => "Papelería y útiles oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 714112,
        "codigo_formateado" => "7.14.1.12..",
        "cuenta" => "OTROS GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141120101,
        "codigo_formateado" => "7.14.1.12.01.01",
        "cuenta" => "Provisiones para indemnización ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141120102,
        "codigo_formateado" => "7.14.1.12.01.02",
        "cuenta" => "Provisiones para eventualidades ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "sel" => 0,
        "codigo" => 7141120103,
        "codigo_formateado" => "7.14.1.12.01.03",
        "cuenta" => "Provisiones para cuentas incobrables ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ]
];





$listado[10]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6131010150')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010151')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010202')['saldo_final'];


// ESTAS CUENTAS SE AGREGAN A LA CUENTA 7131010101 PARA QUE CUADRE EL REPORTE
$cuenta_compras_contado_tda_7 = buscar_cuenta_no_recursiva($respuesta, '7131010103')['saldo_final'];
$cuenta_compras_credito_tda_7 = buscar_cuenta_no_recursiva($respuesta, '7131010104')['saldo_final'];


$listado[23]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7131010101')['saldo_final'] + $cuenta_compras_contado_tda_7 + buscar_cuenta_no_recursiva($respuesta, '7131010102')['saldo_final'] + $cuenta_compras_credito_tda_7 + buscar_cuenta_no_recursiva($respuesta, '7131019903')['saldo_final'];


$listado[26]['sub_total_col_2'] = abs($listado[23]['sub_total_col_2']) - abs(buscar_cuenta_no_recursiva($respuesta, '7131010105')['saldo_final']);

$listado[29]['sub_total_col_2'] = abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final']) + abs($listado[26]['sub_total_col_2']);

$listado[48]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '714101015001')['saldo_final'];
$listado[49]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '714101020201')['saldo_final'];

$listado[52]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141020104')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141020201')['saldo_final'];


$listado[64]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141030101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030301')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030401')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030402')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030501')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030601')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030701')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030702')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141030703')['saldo_final'];

$listado[70]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141040101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141040102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141040103')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141040104')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141040201')['saldo_final'];

$listado[72]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141050101')['saldo_final'];

$listado[77]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141060101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141060201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141060203')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141060301')['saldo_final'];

$listado[79]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141070101')['saldo_final'];

$listado[81]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141080101')['saldo_final'];

$listado[89]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141090101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141090102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141090103')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141090201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141090202')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141099901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141099902')['saldo_final'];

$listado[99]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141100101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100301')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100401')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100402')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100403')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100404')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100405')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141100406')['saldo_final'];

$listado[101]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7141110101')['saldo_final'];

$listado[105]['sub_total_col_3'] = $listado[52]['sub_total_col_2'] + $listado[64]['sub_total_col_2'] + $listado[70]['sub_total_col_2'] + $listado[72]['sub_total_col_2'] + $listado[77]['sub_total_col_2'] + $listado[89]['sub_total_col_2'] + $listado[99]['sub_total_col_2'] + $listado[101]['sub_total_col_2'];


$listado[13]['sub_total_col_3'] = (abs($listado[10]['sub_total_col_2']) - abs(buscar_cuenta_no_recursiva($respuesta, '6131019901')['saldo_final'])) - abs(buscar_cuenta_no_recursiva($respuesta, '6131019902')['saldo_final']);

//ORIGINAL
//$listado[31]['sub_total_col_3'] = abs($listado[29]['sub_total_col_2']) - $respuesta3[0]['inventario_final'];//abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final']);

//PRUEBAS
$listado[31]['sub_total_col_3'] = abs($listado[29]['sub_total_col_2']) - $respuesta3['inventario_final'];//abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final']);

$listado[32]['sub_total_col_3'] = abs($listado[13]['sub_total_col_3']) - abs($listado[31]['sub_total_col_3']);

$listado[36]['sub_total_col_3'] = buscar_cuenta_no_recursiva($respuesta, '6131040101')['saldo_final'];

$listado[40]['sub_total_col_3'] = buscar_cuenta_no_recursiva($respuesta, '6131060101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131060102')['saldo_final'];

// RESUTALDO DEL TOTAL DEL EXCEDENTE LINEA 43 (se deja en valor absoluto porque vienen de forma negativa desde balance de saldos)
$listado[43]['sub_total_col_3'] = abs($listado[32]['sub_total_col_3']) + abs($listado[36]['sub_total_col_3']) + abs($listado[40]['sub_total_col_3']);


$EXEDENTE_FINAL = abs($listado[43]['sub_total_col_3']) - abs($listado[105]['sub_total_col_3']);

$row_1 = 20;
$row_2 = 35;
$row_3 = 15;
$row_4 = 15;
$row_5 = 15;

$pos = 0;


$html = '
        
        <div class="titulo">
            COOPERATIVA SITRABI, R.L.
        </div>
        <div class="titulo_sub">
            ESTADO DE PRODUCTOS Y GASTOS, SECCION CONSUMO
        </div>
        <div class="titulo_sub">
            DEL ' . date('d/m/Y', strtotime($fecha_inicial)) . ' AL ' . date('d/m/Y', strtotime($fecha_final)) . ' 
        </div>
        <div class="titulo_sub">
            (CIFRAS EN QUETZALES)
        </div>

        <table class="table">';


foreach ($listado as $key) {

    $resultado_ = buscar_cuenta_no_recursiva($respuesta, (string)$key['codigo']);
    $nombre_cuenta_ = $resultado_['nombre_cuenta'] == null ? mb_strtoupper($key['cuenta'], 'UTF-8') : $resultado_['nombre_cuenta'];
    $codigo_cuenta_ = $resultado_['codigo'] == null ? $key['codigo_formateado'] : $resultado_['codigo'];
    $sub_total_col_1_ = $key['sub_total_col_1'] == 0 ? '' : 'Q' . number_format(abs($key['sub_total_col_1']), 2, '.', ',');
    $sub_total_col_2_ = $key['sub_total_col_2'] == 0 ? '' : 'Q' . number_format(abs($key['sub_total_col_2']), 2, '.', ',');
    $sub_total_col_3_ = $key['sub_total_col_3'] == 0 ? '' : 'Q' . number_format(abs($key['sub_total_col_3']), 2, '.', ',');

    if ($key['tipo']  == 'titulo') {


        $html .= '
                <tr>
                    <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">'. $codigo_cuenta_ . '</td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_5 . '%;"></td>
                </tr>';
    } elseif ($key['tipo']  == 'espacio') {

        $html .= '<tr>
                <td class="estilo_celda2" style="width: ' . $row_1 . '%;"></td>
                <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $key['cuenta'] . '</td>
                <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
            </tr>';
    } else {

        if ($key['posicion'] == 1) {
            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">'. $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
                </tr>
            ';
        } elseif ($key['posicion'] == 2) {

            if ((string)$key['sel'] == 1) {
                //$set_variable = $respuesta3[0]['inventario_final'];
                $set_variable = $respuesta3['inventario_final'];
            }else{
                // SE AGREGA ESTA CONDICIONAL A PEDIDO DEL CONTADOR, YA QUE HAY QUE SUMAR DOS CUENTAS MÁS

                if ((string)$key['codigo'] == '7131010101') {
                    
                    //CUENTA 7131010103 => COMPRAS AL CONTADO TDA, 07
                    $set_variable = $resultado_['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7131010103')['saldo_final'];

                }elseif((string)$key['codigo'] == '7131010102'){

                    //CUENTA 7131010103 => COMPRAS AL CRÉDITO TDA. 07
                    $set_variable = $resultado_['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7131010104')['saldo_final'];

                }else{
                    $set_variable = $resultado_['saldo_final'];
                }

            }

            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">'. $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q' . number_format(abs($set_variable), 2, '.', ',') . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
                </tr>
            ';
        } else {
            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">'. $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                </tr>
            ';
        }
    }

    // $pos += 1;
}






$html .= '<tr>
                <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">34-111</td>
                <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">EXCEDENTES</td>
                <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
                <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
                <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_5 . '%;">Q' . number_format($EXEDENTE_FINAL, 2, '.', ',') . '</td>
            </tr>';


$html .= '<tr>
                        <td class="estilo_celda2" style="width: ' . $row_1 . '%;height: 20px;"></td>
                        <td class="estilo_celda3" style="width: ' . $row_2 . '%"></td>
                        <td class="estilo_celda3 centrar_texto" style="width: ' . $row_3 . '%;"></td>
                        <td class="estilo_celda3 centrar_texto" style="width: ' . $row_4 . '%;"></td>
                        <td class="estilo_celda3 centrar_texto" style="width: ' . $row_5 . '%;"></td>
                    </tr>
            ';




$html .= '<tr>
                        <td colspan="5" class="estilo_celda4">El infrascrito Perito Contador, registrado en la Superintendencia de Administración Tributaria, con nit 7584104-5, CERTIFICA :</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="estilo_celda4">Que el presente estado de resultado, de la Cooperativa de Ahorro, Crédito y Servicios Varios</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="estilo_celda4">"SITRABI", R. L., por el primer trimestre comprendido de 01 enero al 31 marzo 2024, se encuentra elaborado de acuerdo a las</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="estilo_celda4">Normas Internacionales de contabilidad.</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="estilo_celda4" style="height: 13px";></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    <tr>
                        <td colspan="5" class="estilo_celda4">Morales Izabal, 24 de enero de 2024</td>
                    </tr>

                    <tr>
                        <td colspan="5" class="estilo_celda4" style="height: 13px";></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="estilo_celda4" style="height: 13px";></td>
                    </tr>

                    <tr>
                        <td colspan="5" class="estilo_celda4" style="height: 13px";></td>
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
