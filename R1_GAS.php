<?php
set_time_limit(120); // Aumentar el límite de tiempo de ejecución a 120 segundos
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

// $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNzE3MTg5OTQzLCJleHAiOjE3MTk3ODE5NDN9.p8hRKiWAZRXFSkhSuBjq3_kI_7OIroYziOYgZVQPiAM";

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];

// echo $fecha_inicial;
// die();

$centro_de_costo = 0;
$token = $_GET['token'];
$env = $_GET['env'];
$ip = '192.168.10.14';


//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos" : "http://". $ip .":8009/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos";

$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/centros-de-costos/c/nombre/" . $centro_de_costo : "http://". $ip .":8009/api/centros-de-costos/c/nombre/" . $centro_de_costo;


// consulta al gparametros para traer el saldo final que se guarda manualmente // tiene un 0 al final porque es el centro de costo que pide pero que se ha desabilitado
// $url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/gparametros/c/parametros/0" : "http://". $ip .":8009/api/gparametros/c/parametros/0";

$url3 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/gparametros/c/inventario_por_trimestre/" . $fecha_final : "http://". $ip .":8009/api/gparametros/c/inventario_por_trimestre/" . $fecha_final;


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



function suma_saldos($resp, $array)
{

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
        "pos" => 0,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "seccion estacion",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 1,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "VENTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 2,
        "codigo" => 61310101,
        "codigo_formateado" => "6.13.1.01.01",
        "cuenta" => "VENTAS COMBUSTIBLE AL CONTADO ASOCIADO",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 3,
        "codigo" => 61310102,
        "codigo_formateado" => "6.13.1.01.02",
        "cuenta" => "VENTAS COMBUSTBILE AL COTNADO NO ASOCIADO",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 4,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "SUB-TOTAL",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 5,
        "codigo" => 6131019901,
        "codigo_formateado" => "6.13.1.01.99.01",
        "cuenta" => "DESCUENTOS SOBRE VENTAS",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 6,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "COSTO DE VENTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 7,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "COMRAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 8,
        "codigo" => 7131010107,
        "codigo_formateado" => "7.13.1.01.01.07",
        "cuenta" => "COMPRAS AL CONTADO",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 9,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "IVA COSTO",
        "tipo" => "titulo",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 10,
        "codigo" => 7131019903,
        "codigo_formateado" => "7.13.1.01.99.03",
        "cuenta" => "iva costo",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 11,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "SUB-TOTAL",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 12,
        "codigo" => 7131010105,
        "codigo_formateado" => "7.13.1.01.01.05",
        "cuenta" => "devoluciones y rebajas sobre compras",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 13,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "SUB-TOTAL",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 14,
        "codigo" => 1131060101,
        "codigo_formateado" => "1.13.1.06.01.01",
        "cuenta" => "INVENTARIOS DE MERCADERÍA ( COMPRAS)",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 15,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "Total combustible disponible",
        "tipo" => "espacio",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 16,
        "codigo" => 1131060105,
        "codigo_formateado" => "1.13.1.06.01.05",
        "cuenta" => "INVENTARIO DE COMBUSTIBLE GASOLINA REGULAR",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 17,
        "codigo" => 1131060106,
        "codigo_formateado" => "1.13.1.06.01.06",
        "cuenta" => "INVENTARIO DE COMBUSTIBLE GASOLINA SUPER",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 18,
        "codigo" => 1131060107,
        "codigo_formateado" => "1.13.1.06.01.07",
        "cuenta" => "INVENTARIO DE COMBUSTIBLE DIESEL",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 19,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "excedente bruto en ventas",
        "tipo" => "espacio",
        "posicion" => 3,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 20,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "TOTAL EXCEDENTE",
        "tipo" => "espacio",
        "posicion" => 3,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 21,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 22,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "SECCION CONSUMO",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 23,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "SUELDOS",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 24,
        "codigo" => 7171030101,
        "codigo_formateado" => "7.17.1.03.01.01",
        "cuenta" => "SUELDOS PERMANENTES",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 25,
        "codigo" => 7171030402,
        "codigo_formateado" => "7.17.1.03.04.02",
        "cuenta" => "bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 26,
        "codigo" => 7171030401,
        "codigo_formateado" => "7.17.1.03.04.01",
        "cuenta" => "bonificacion incentivo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 27,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "PRESTAMOS LABORALES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 28,
        "codigo" => 7171030501,
        "codigo_formateado" => "7.17.1.03.05.01",
        "cuenta" => "cuenta patronal igss",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 29,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "REPARACIONES Y ACCESORIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 30,
        "codigo" => 7171060203,
        "codigo_formateado" => "7.17.1.06.02.03",
        "cuenta" => "reparacion de vehiculos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 31,
        "codigo" => 7171060301,
        "codigo_formateado" => "7.17.1.06.03.01",
        "cuenta" => "repaciones de herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 32,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "GASTOS DE ADMINSITRACION",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 33,
        "codigo" => 7171060301,
        "codigo_formateado" => "7.17.1.06.03.01",
        "cuenta" => "calibracion de bombas de combustible",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 34,
        "codigo" => 7171060303,
        "codigo_formateado" => "7.17.1.06.03.03",
        "cuenta" => "reparacion de bombas de combustible",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 35,
        "codigo" => 7171060304,
        "codigo_formateado" => "7.17.1.06.03.04",
        "cuenta" => "mermas de combustible",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 36,
        "codigo" => 7171110101,
        "codigo_formateado" => "7.17.1.11.01.01",
        "cuenta" => "papelerias y útiles de oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 37,
        "codigo" => 7171100301,
        "codigo_formateado" => "7.17.1.10.03.01",
        "cuenta" => "servicio de telecomunicaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 38,
        "codigo" => 7171100201,
        "codigo_formateado" => "7.17.1.10.02.01",
        "cuenta" => "energia electrica",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 39,
        "codigo" => 7171100101,
        "codigo_formateado" => "7.17.1.10.01.01",
        "cuenta" => "servicio de seguridad",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 40,
        "codigo" => 7171100402,
        "codigo_formateado" => "7.17.1.10.04.02",
        "cuenta" => "accesorios para computadoras",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 41,
        "codigo" => 7171100403,
        "codigo_formateado" => "7.17.1.10.04.03",
        "cuenta" => "varios e imprevistos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 42,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 43,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "gastos de operacion",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 44,
        "codigo" => 7171020201,
        "codigo_formateado" => "7.17.1.02.02.01",
        "cuenta" => "viaticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 45,
        "codigo" => 7171100404,
        "codigo_formateado" => "7.17.1.10.04.04",
        "cuenta" => "fletes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 46,
        "codigo" => 7171100406,
        "codigo_formateado" => "7.17.1.10.04.06",
        "cuenta" => "combustibles y lubricantes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 47,
        "codigo" => 7171040201,
        "codigo_formateado" => "7.17.1.04.02.01",
        "cuenta" => "impuestos y arbitrios fiscales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 48,
        "codigo" => 7171040101,
        "codigo_formateado" => "7.17.1.04.01.01",
        "cuenta" => "ipmuestos derivados del petroleo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 49,
        "codigo" => 7171100407,
        "codigo_formateado" => "7.17.1.10.04.07",
        "cuenta" => "por uso de tarjeta de credito",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 50,
        "codigo" => 7171040102,
        "codigo_formateado" => "7.17.1.04.01.02",
        "cuenta" => "iva costo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 51,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "espacio",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 52,
        "codigo" => 0,
        "codigo_formateado" => 0,
        "cuenta" => "excedente",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ]
];

// +++

$listado[4]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '61310101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '61310102')['saldo_final'];

$listado[5]['sub_total_col_3'] =  abs($listado[4]['sub_total_col_2']) - abs(buscar_cuenta_no_recursiva($respuesta, '6131019901')['saldo_final']);

$listado[11]['sub_total_col_2'] = abs(buscar_cuenta_no_recursiva($respuesta, '7131010107')['saldo_final']) + abs(buscar_cuenta_no_recursiva($respuesta, '7131019903')['saldo_final']);

$listado[13]['sub_total_col_2'] = $listado[11]['sub_total_col_2'] - abs(buscar_cuenta_no_recursiva($respuesta, '7131010105')['saldo_final']);

$listado[15]['sub_total_col_2'] = $listado[13]['sub_total_col_2'] + abs(buscar_cuenta_no_recursiva($respuesta, '1131060101')['saldo_final']);

$listado[18]['sub_total_col_3'] = $listado[15]['sub_total_col_2'] - abs(buscar_cuenta_no_recursiva($respuesta, '1131060105')['saldo_final']) - abs(buscar_cuenta_no_recursiva($respuesta, '1131060106')['saldo_final']) - abs(buscar_cuenta_no_recursiva($respuesta, '1131060107')['saldo_final']);

$listado[19]['sub_total_col_3'] = $listado[5]['sub_total_col_3'] - $listado[18]['sub_total_col_3'];


$EXEDENTE_FINAL =  0;

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
            ESTADO DE PRODUCTOS Y GASTOS, ESTACION DE SERVICIO
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
                    <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">' . $pos . '- ' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_3 . '%;"></td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_4 . '%;"></td>
                    <td class="estilo_celda fondo_gris_titulo estilo_bold centrar_texto" style="width: ' . $row_5 . '%;"></td>
                </tr>';
    } elseif ($key['tipo']  == 'espacio') {

        $html .= '<tr>
                <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $pos . ' - </td>
                <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $key['cuenta'] . '</td>
                <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
            </tr>';
    } else {

        if ($key['posicion'] == 1) {
            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $pos . '- ' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
                </tr>
            ';
        } elseif ($key['posicion'] == 2) {

            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $pos . '- ' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
                </tr>
            ';
        } else {
            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $pos . '- ' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                </tr>
            ';
        }
    }

    $pos += 1;
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
