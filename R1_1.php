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

$url = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos" : "http://". $ip_dev .":8009/api/contabilidad-transaccion-cabeceras/c/reporte_balance_saldos";

$url2 = $env == 'p' ? "https://cooperativasitrabi.ddns.net/app/coope/api/centros-de-costos/c/nombre/" . $centro_de_costo : "http://". $ip_dev .":8009/api/centros-de-costos/c/nombre/" . $centro_de_costo;

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

// $es = buscar_cuenta_no_recursiva($respuesta, '6');
// print_r($respuesta);

// die();





$listado = [
    [
        "pos" => 0,
        "codigo" => 6,
        "codigo_formateado" => "6",
        "cuenta" => "PRODUCTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 1,
        "codigo" => 601,
        "codigo_formateado" => "6.01",
        "cuenta" => "PRODUCTOS FINANCIEROS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 2,
        "codigo" => 6011,
        "codigo_formateado" => "6.01.1",
        "cuenta" => "PRODUCTOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 3,
        "codigo" => 601101,
        "codigo_formateado" => "6.01.1.01",
        "cuenta" => "INTERESES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 4,
        "codigo" => 60110103,
        "codigo_formateado" => "6.01.1.01.03",
        "cuenta" => "CARTERA DE CRÉDITOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 5,
        "codigo" => 601101030210,
        "codigo_formateado" => "6.01.1.01.03.02.10",
        "cuenta" => "Intereses por préstamos ordinarios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 6,
        "codigo" => 601101030410,
        "codigo_formateado" => "6.01.1.01.03.04.10",
        "cuenta" => "Intereses por préstamos lotificación Amates",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 7,
        "codigo" => 601101030411,
        "codigo_formateado" => "6.01.1.01.03.04.11",
        "cuenta" => "Intereses por préstamos lotificación Rancho Grande",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 8,
        "codigo" => 601101030412,
        "codigo_formateado" => "6.01.1.01.03.04.12",
        "cuenta" => "Intereses por préstamos lotificación Atlantis",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 9,
        "codigo" => 601101030413,
        "codigo_formateado" => "6.01.1.01.03.04.13",
        "cuenta" => "Intereses por préstamos lotificación La Vigia",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 10,
        "codigo" => 60110104,
        "codigo_formateado" => "6.01.1.01.04.",
        "cuenta" => "INTERESES POR MORA  ",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 11,
        "codigo" => 601101040210,
        "codigo_formateado" => "6.01.1.01.04.02.10",
        "cuenta" => "Intereses por mora, préstamos ordinarios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 12,
        "codigo" => 601101040402,
        "codigo_formateado" => "6.01.1.01.04.04.02",
        "cuenta" => "Intereses por mora, préstamos lotificación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 13,
        "codigo" => 602,
        "codigo_formateado" => "6.02",
        "cuenta" => "PRODUCTOS POR SERVICIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 14,
        "codigo" => 6021,
        "codigo_formateado" => "6.02.1",
        "cuenta" => "COMISIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 15,
        "codigo" => 602101,
        "codigo_formateado" => "6.02.1.01",
        "cuenta" => "COMISIONES POR SERVICIOS DIVERSOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 16,
        "codigo" => 6021019901,
        "codigo_formateado" => "6.02.1.01.99.01",
        "cuenta" => "Comisiones por manejo de créditos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 17,
        "codigo" => 6021019902,
        "codigo_formateado" => "6.02.1.01.99.02",
        "cuenta" => "Comisiones por gastos de administración",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 18,
        "codigo" => 6021019903,
        "codigo_formateado" => "6.02.1.01.99.03",
        "cuenta" => "Productos varios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 19,
        "codigo" => 7,
        "codigo_formateado" => "7.",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 20,
        "codigo" => 701,
        "codigo_formateado" => "7.01",
        "cuenta" => "GASTOS FINANCIEROS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 21,
        "codigo" => 7011,
        "codigo_formateado" => "7.01.1",
        "cuenta" => "GASTOS PAGO INTERESES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 22,
        "codigo" => 701101,
        "codigo_formateado" => "7.01.1.01",
        "cuenta" => "INTERESES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 23,
        "codigo" => 701101010201,
        "codigo_formateado" => "7.01.1.01.01.02.01",
        "cuenta" => "Intereses por depósitos de ahorro familiar",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 24,
        "codigo" => 701101010202,
        "codigo_formateado" => "7.01.1.01.01.02.02",
        "cuenta" => "Intereses por depósitos de ahorro especial",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 25,
        "codigo" => 701101010203,
        "codigo_formateado" => "7.01.1.01.01.02.03",
        "cuenta" => "Intereses por depósitos de ahorro vacacional",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 26,
        "codigo" => 701101010301,
        "codigo_formateado" => "7.01.1.01.01.03.01",
        "cuenta" => "Intereses por depósitos de ahorro plazo fijo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 27,
        "codigo" => 701101015001,
        "codigo_formateado" => "7.01.1.01.01.50.01",
        "cuenta" => "Intereses por depósitos de ahorro corriente y apartación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 28,
        "codigo" => 701101020201,
        "codigo_formateado" => "7.01.1.01.02.02.01",
        "cuenta" => "Intereses por prestámos bancarios Interbanco",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 29,
        "codigo" => 701101020202,
        "codigo_formateado" => "7.01.1.01.02.02.02",
        "cuenta" => "Intereses por prestámos bancarios Bantrab",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 30,
        "codigo" => 706,
        "codigo_formateado" => "7.06",
        "cuenta" => "GASTOS DE ADMINISTRACION",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 31,
        "codigo" => 7061,
        "codigo_formateado" => "7.06.1",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 32,
        "codigo" => 706101,
        "codigo_formateado" => "7.06.1.01",
        "cuenta" => "ORGANOS DIRECTIVOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 33,
        "codigo" => 70610101,
        "codigo_formateado" => "7.06.1.01.01.",
        "cuenta" => "Dietas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 34,
        "codigo" => 70610103,
        "codigo_formateado" => "7.06.1.01.03.",
        "cuenta" => "Transporte y viáticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 35,
        "codigo" => 706102,
        "codigo_formateado" => "7.06.1.02",
        "cuenta" => "FUNCIONARIOS Y EMPLEADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 36,
        "codigo" => 70610201,
        "codigo_formateado" => "7.06.1.02.01.",
        "cuenta" => "SUELDOS ORDINARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 37,
        "codigo" => 7061020101,
        "codigo_formateado" => "7.06.1.02.01.01",
        "cuenta" => "Sueldos permanentes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 38,
        "codigo" => 7061020102,
        "codigo_formateado" => "7.06.1.02.01.02",
        "cuenta" => "Sueldos eventuales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 39,
        "codigo" => 70610203,
        "codigo_formateado" => "7.06.1.02.03.",
        "cuenta" => "AGUINALDOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 40,
        "codigo" => 7061020301,
        "codigo_formateado" => "7.06.1.02.03.01",
        "cuenta" => "Aguinaldo   ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 41,
        "codigo" => 70610204,
        "codigo_formateado" => "7.06.1.02.04.",
        "cuenta" => "INDEMNIZACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 42,
        "codigo" => 7061020401,
        "codigo_formateado" => "7.06.1.02.04.01",
        "cuenta" => "Indemnizaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 43,
        "codigo" => 70610205,
        "codigo_formateado" => "7.06.1.02.05.",
        "cuenta" => "BONIFICACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 44,
        "codigo" => 7061020501,
        "codigo_formateado" => "7.06.1.02.05.01",
        "cuenta" => "Bonificación incentivo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 45,
        "codigo" => 7061020502,
        "codigo_formateado" => "7.06.1.02.05.02",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 46,
        "codigo" => 70610209,
        "codigo_formateado" => "7.06.1.02.09.",
        "cuenta" => "CUOTA PATRONAL IGSS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 47,
        "codigo" => 7061020901,
        "codigo_formateado" => "7.06.1.02.09.01",
        "cuenta" => "Cuota patronal Igss",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 48,
        "codigo" => 70610210,
        "codigo_formateado" => "7.06.1.02.10.",
        "cuenta" => "VACACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 49,
        "codigo" => 7061021001,
        "codigo_formateado" => "7.06.1.02.10.01",
        "cuenta" => "Vacaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 50,
        "codigo" => 70610299,
        "codigo_formateado" => "7.06.1.02.99.",
        "cuenta" => "OTRAS PRESTACIONES LABORALES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 51,
        "codigo" => 7061029901,
        "codigo_formateado" => "7.06.1.02.99.01",
        "cuenta" => "Bono catorce",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 52,
        "codigo" => 7061029902,
        "codigo_formateado" => "7.06.1.02.99.02",
        "cuenta" => "Comisiones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 53,
        "codigo" => 7061029903,
        "codigo_formateado" => "7.06.1.02.99.03",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 54,
        "codigo" => 706103,
        "codigo_formateado" => "7.06.1.03",
        "cuenta" => "TRIBUTOS Y OTRAS CUOTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 55,
        "codigo" => 70610301,
        "codigo_formateado" => "7.06.1.03.01.",
        "cuenta" => "IMPUESTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 56,
        "codigo" => 7061030101,
        "codigo_formateado" => "7.06.1.03.01.01",
        "cuenta" => "Impuesto derivado del Petroleo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 57,
        "codigo" => 7061030102,
        "codigo_formateado" => "7.06.1.03.01.02",
        "cuenta" => "IVA costo  ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 58,
        "codigo" => 7061030103,
        "codigo_formateado" => "7.06.1.03.01.03",
        "cuenta" => "I.S.R. Trimestral ( gasto)",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 59,
        "codigo" => 7061030104,
        "codigo_formateado" => "7.06.1.03.01.04",
        "cuenta" => "ISO Trimestral ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 60,
        "codigo" => 70610302,
        "codigo_formateado" => "7.06.1.03.02.",
        "cuenta" => "ARBITRIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 61,
        "codigo" => 7061030201,
        "codigo_formateado" => "7.06.1.03.02.01",
        "cuenta" => "Impuestos y arbitrios fiscales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 62,
        "codigo" => 70610499,
        "codigo_formateado" => "7.06.1.04.99",
        "cuenta" => "OTROS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 63,
        "codigo" => 7061049901,
        "codigo_formateado" => "7.06.1.04.99.01",
        "cuenta" => "Honorarios profesionales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 64,
        "codigo" => 706106,
        "codigo_formateado" => "7.06.1.06",
        "cuenta" => "REPARACIONES Y MANTENIMIENTO",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 65,
        "codigo" => 7061060101,
        "codigo_formateado" => "7.06.1.06.01.01",
        "cuenta" => "Reparaciones de edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 66,
        "codigo" => 7061060201,
        "codigo_formateado" => "7.06.1.06.02.01",
        "cuenta" => "Reparaciones de mobiliario y equipo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 67,
        "codigo" => 7061060204,
        "codigo_formateado" => "7.06.1.06.02.04",
        "cuenta" => "Reparaciones de vehículos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 68,
        "codigo" => 7061069901,
        "codigo_formateado" => "7.06.1.06.99.01",
        "cuenta" => "Reparaciones de herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 69,
        "codigo" => 706107,
        "codigo_formateado" => "7.06.1.07",
        "cuenta" => "MERCADEO Y PUBLICIDAD",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 70,
        "codigo" => 7061070201,
        "codigo_formateado" => "7.06.1.07.02.01",
        "cuenta" => "Educación y mercadeo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 71,
        "codigo" => 706108,
        "codigo_formateado" => "7.06.1.08",
        "cuenta" => "PRIMAS DE SEGUROS Y FIANZAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 72,
        "codigo" => 7061080101,
        "codigo_formateado" => "7.06.1.08.01.01",
        "cuenta" => "Seguros y fianzas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 73,
        "codigo" => 706109,
        "codigo_formateado" => "7.06.1.09",
        "cuenta" => "DEPRECIACIONES Y AMORTIZACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 74,
        "codigo" => 7061090101,
        "codigo_formateado" => "7.06.1.09.01.01",
        "cuenta" => "Edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 75,
        "codigo" => 7061090102,
        "codigo_formateado" => "7.06.1.09.01.02",
        "cuenta" => "Mobiliario y Equipo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 76,
        "codigo" => 7061090106,
        "codigo_formateado" => "7.06.1.09.01.06",
        "cuenta" => "Vehículos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 77,
        "codigo" => 706109019901,
        "codigo_formateado" => "7.06.1.09.01.99.01",
        "cuenta" => "Equipo de computación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 78,
        "codigo" => 706109019902,
        "codigo_formateado" => "7.06.1.09.01.99.02",
        "cuenta" => "Herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 79,
        "codigo" => 7061099901,
        "codigo_formateado" => "7.06.1.09.99.01",
        "cuenta" => "Amortización gastos de instalación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 80,
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
        "pos" => 81,
        "codigo" => 706110,
        "codigo_formateado" => "7.06.1.10",
        "cuenta" => "PAPELERÍA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 82,
        "codigo" => 7061100101,
        "codigo_formateado" => "7.06.1.10.01.01",
        "cuenta" => "Papelería y útiles de oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 83,
        "codigo" => 706199,
        "codigo_formateado" => "7.06.1.99",
        "cuenta" => "GASTOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 84,
        "codigo" => 7061990101,
        "codigo_formateado" => "7.06.1.99.01.01",
        "cuenta" => "Servicio de seguridad",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 85,
        "codigo" => 7061990901,
        "codigo_formateado" => "7.06.1.99.09.01",
        "cuenta" => "Energía eléctrica",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 86,
        "codigo" => 7061999901,
        "codigo_formateado" => "7.06.1.99.99.01",
        "cuenta" => "Alquileres",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 87,
        "codigo" => 7061999902,
        "codigo_formateado" => "7.06.1.99.99.02",
        "cuenta" => "Accesorios para computadora",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 88,
        "codigo" => 7061999903,
        "codigo_formateado" => "7.06.1.99.99.03",
        "cuenta" => "Varios e imprevistos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 89,
        "codigo" => 7061999904,
        "codigo_formateado" => "7.06.1.99.99.04",
        "cuenta" => "Fletes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 90,
        "codigo" => 7061999905,
        "codigo_formateado" => "7.06.1.99.99.05",
        "cuenta" => "Material de empaque",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 91,
        "codigo" => 7061999906,
        "codigo_formateado" => "7.06.1.99.99.06",
        "cuenta" => "Combustibles y lubricantes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 92,
        "codigo" => 709199,
        "codigo_formateado" => "7.09.1.99",
        "cuenta" => "OTROS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 93,
        "codigo" => 7091991201,
        "codigo_formateado" => "7.09.1.99.12.01",
        "cuenta" => "Servicio de telecomunicaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 94,
        "codigo" => 713103,
        "codigo_formateado" => "7.13.1.03",
        "cuenta" => "OTROS GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 95,
        "codigo" => 7131030101,
        "codigo_formateado" => "7.13.1.03.01.01",
        "cuenta" => "Provisiones para indemnización ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 96,
        "codigo" => 7131030102,
        "codigo_formateado" => "7.13.1.03.01.02",
        "cuenta" => "Provisiones para eventualidades ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 97,
        "codigo" => 7131030103,
        "codigo_formateado" => "7.13.1.03.01.03",
        "cuenta" => "Provisiones para cuentas incobrables ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ]
];






// $listado[10]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6131010150')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010151')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010202')['saldo_final'];

$listado[12]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '601101030210')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '601101030410')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '601101030411')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '601101030412')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '601101030413')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '601101040210')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '601101040402')['saldo_final'];


$listado[17]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6021019901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6021019902')['saldo_final'];

$listado[18]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6021019903')['saldo_final'];

$listado[29]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '701101010201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '701101010202')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '701101010203')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '701101010301')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '701101015001')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '701101020201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '701101020202')['saldo_final'];

$listado[34]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '70610101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '70610103')['saldo_final'];

$listado[38]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061020101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061020102')['saldo_final'];

$listado[40]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061020301')['saldo_final'];

$listado[42]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061020401')['saldo_final'];

$listado[45]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061020501')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061020502')['saldo_final'];

$listado[47]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061020901')['saldo_final'];

$listado[49]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061021001')['saldo_final'];

$listado[53]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061029901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061029902')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061029903')['saldo_final'];

$listado[59]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061030101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061030102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061030103')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061030104')['saldo_final'];

$listado[61]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061030201')['saldo_final'];

$listado[63]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061049901')['saldo_final'];

$listado[68]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061060101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061060201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061060204')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061069901')['saldo_final'];

$listado[70]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061070201')['saldo_final'];

$listado[72]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061080101')['saldo_final'];

$listado[80]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061090101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061090102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061090106')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '706109019901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '706109019902')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061099901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7141099902')['saldo_final'];

$listado[82]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061100101')['saldo_final'];

$listado[91]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7061990101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061990901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061999901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061999902')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061999903')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061999904')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061999905')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7061999906')['saldo_final'];

$listado[93]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7091991201')['saldo_final'];


$listado[97]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7131030101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7131030102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7131030103')['saldo_final'];


$listado[18]['sub_total_col_3'] = $listado[12]['sub_total_col_2'] + $listado[17]['sub_total_col_2'] + $listado[18]['sub_total_col_2'];

$listado[97]['sub_total_col_3'] = $listado[29]['sub_total_col_2'] + $listado[33]['sub_total_col_2'] + $listado[34]['sub_total_col_2'] + $listado[37]['sub_total_col_2'] + $listado[38]['sub_total_col_2'] + $listado[40]['sub_total_col_2'] + $listado[42]['sub_total_col_2'] + $listado[45]['sub_total_col_2'] + $listado[47]['sub_total_col_2'] + $listado[49]['sub_total_col_2'] + $listado[53]['sub_total_col_2'] + $listado[59]['sub_total_col_2'] + $listado[61]['sub_total_col_2'] + $listado[63]['sub_total_col_2'] + $listado[68]['sub_total_col_2'] + $listado[70]['sub_total_col_2'] + $listado[72]['sub_total_col_2'] + $listado[73]['sub_total_col_2'] + $listado[80]['sub_total_col_2'] + $listado[82]['sub_total_col_2'] + $listado[91]['sub_total_col_2'] + $listado[93]['sub_total_col_2'] + $listado[97]['sub_total_col_2'];

$EXEDENTE_FINAL = abs($listado[18]['sub_total_col_3']) - abs($listado[97]['sub_total_col_3']);

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
            ESTADO DE PRODUCTOS Y GASTOS, SECCION AHORRO Y CREDITO
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
                    <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">'  . $codigo_cuenta_ . '</td>
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
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
                </tr>
            ';
        } elseif ($key['posicion'] == 2) {
            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">'. $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
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
                <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">34-11</td>
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
