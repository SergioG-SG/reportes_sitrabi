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
        "codigo" => 613,
        "codigo_formateado" => "6.13",
        "cuenta" => "INGRESOS OTROS NEGOCIOS SIN INTERMEDICACION FINANCIERA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 2,
        "codigo" => 613103,
        "codigo_formateado" => "6.13.1.03",
        "cuenta" => "OTROS INGRESOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 3,
        "codigo" => 61310301,
        "codigo_formateado" => "6.13.1.03.01",
        "cuenta" => "Otros ingresos de asociados",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 4,
        "codigo" => 6131030101,
        "codigo_formateado" => "6.13.1.03.01.01",
        "cuenta" => "Venta de lotes ( diferencial )",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 5,
        "codigo" => 7,
        "codigo_formateado" => "7",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 6,
        "codigo" => 716,
        "codigo_formateado" => "7.16",
        "cuenta" => "GASTOS SECCIÓN LOTIFICACIÓN ( FINANCIEROS)",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 7,
        "codigo" => 7161,
        "codigo_formateado" => "7.16.1",
        "cuenta" => "GASTOS ",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 8,
        "codigo" => 716101,
        "codigo_formateado" => "7.16.1.01",
        "cuenta" => "INTERESES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 9,
        "codigo" => 716101020201,
        "codigo_formateado" => "7.16.1.01.02.02.01",
        "cuenta" => "Intereses por préstamos bancarios ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 10,
        "codigo" => 716102,
        "codigo_formateado" => "7.16.1.02",
        "cuenta" => "ORGANOS DIRECTIVOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 11,
        "codigo" => 7161020104,
        "codigo_formateado" => "7.16.1.02.01.04",
        "cuenta" => "Otros",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 12,
        "codigo" => 7161020201,
        "codigo_formateado" => "7.16.1.02.02.01",
        "cuenta" => "Viáticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 13,
        "codigo" => 716103,
        "codigo_formateado" => "7.16.1.03",
        "cuenta" => "FUNCIONARIOS Y EMPLEADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 14,
        "codigo" => 7161030101,
        "codigo_formateado" => "7.16.1.03.01.01",
        "cuenta" => "Sueldos permanentes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 15,
        "codigo" => 7161030102,
        "codigo_formateado" => "7.16.1.03.01.02",
        "cuenta" => "Sueldos eventuales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 16,
        "codigo" => 7161030201,
        "codigo_formateado" => "7.16.1.03.02.01",
        "cuenta" => "Aguinaldo  ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 17,
        "codigo" => 7161030301,
        "codigo_formateado" => "7.16.1.03.03.01",
        "cuenta" => "Indemnizaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 18,
        "codigo" => 7161030401,
        "codigo_formateado" => "7.16.1.03.04.01",
        "cuenta" => "Bonificación incentivo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 19,
        "codigo" => 7161030402,
        "codigo_formateado" => "7.16.1.03.04.02",
        "cuenta" => "Bonificaciones ( especial)",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 20,
        "codigo" => 7161030501,
        "codigo_formateado" => "7.16.1.03.05.01",
        "cuenta" => "Cuota patronal IGSS",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 21,
        "codigo" => 7161030601,
        "codigo_formateado" => "7.16.1.03.06.01",
        "cuenta" => "Vacaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 22,
        "codigo" => 7161030701,
        "codigo_formateado" => "7.16.1.03.07.01",
        "cuenta" => "Bono catorce",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 23,
        "codigo" => 7161030702,
        "codigo_formateado" => "7.16.1.03.07.02",
        "cuenta" => "Comisiones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 24,
        "codigo" => 7161030703,
        "codigo_formateado" => "7.16.1.03.07.03",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 25,
        "codigo" => 716104,
        "codigo_formateado" => "7.16.1.04",
        "cuenta" => "TRIBUTOS Y OTRAS CUOTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 26,
        "codigo" => 7161040101,
        "codigo_formateado" => "7.16.1.04.01.01",
        "cuenta" => "Impuestos derivados del petróleo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 27,
        "codigo" => 7161040102,
        "codigo_formateado" => "7.16.1.04.01.02",
        "cuenta" => "IVA COSTO",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 28,
        "codigo" => 7161040103,
        "codigo_formateado" => "7.16.1.04.01.03",
        "cuenta" => "I.SR. Trimestral ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 29,
        "codigo" => 7161040104,
        "codigo_formateado" => "7.16.1.04.01.04",
        "cuenta" => "ISO Trimestral ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 30,
        "codigo" => 7161040201,
        "codigo_formateado" => "7.16.1.04.02.01",
        "cuenta" => "Impuestos y arbitrios fiscales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 31,
        "codigo" => 716105,
        "codigo_formateado" => "7.16.1.05",
        "cuenta" => "HONORARIOS PROFESIONALES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 32,
        "codigo" => 7161050101,
        "codigo_formateado" => "7.16.1.05.01.01",
        "cuenta" => "Honorarios profesionales ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 33,
        "codigo" => 716106,
        "codigo_formateado" => "7.16.1.06",
        "cuenta" => "REPARACIONES Y MANTENIMIENTO",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 34,
        "codigo" => 7161060101,
        "codigo_formateado" => "7.16.1.06.01.01",
        "cuenta" => "Reparaciones edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 35,
        "codigo" => 7161060201,
        "codigo_formateado" => "7.16.1.06.02.01",
        "cuenta" => "Mobiliario y equipo de oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 36,
        "codigo" => 7161060202,
        "codigo_formateado" => "7.16.1.06.02.02",
        "cuenta" => "Sistemas informáticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 37,
        "codigo" => 7161060203,
        "codigo_formateado" => "7.16.1.06.02.03",
        "cuenta" => "Vehiculos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 38,
        "codigo" => 7161060301,
        "codigo_formateado" => "7.16.1.06.03.01",
        "cuenta" => "Reparaciones de herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 39,
        "codigo" => 716107,
        "codigo_formateado" => "7.16.1.07",
        "cuenta" => "MERCADEO Y PUBLICIDAD",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 40,
        "codigo" => 7161070101,
        "codigo_formateado" => "7.16.1.07.01.01",
        "cuenta" => "Educación y mercadeo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 41,
        "codigo" => 716108,
        "codigo_formateado" => "7.16.1.08",
        "cuenta" => "PRIMAS DE SEGUROS Y FIANZAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 42,
        "codigo" => 7161080101,
        "codigo_formateado" => "7.16.1.08.01.01",
        "cuenta" => "Seguros y fianzas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 43,
        "codigo" => 716109,
        "codigo_formateado" => "7.16.1.09",
        "cuenta" => "DEPRECIACIONES Y AMORTIZACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 44,
        "codigo" => 716110,
        "codigo_formateado" => "7.16.1.10",
        "cuenta" => "GASTOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 45,
        "codigo" => 7161100101,
        "codigo_formateado" => "7.16.1.10.01.01",
        "cuenta" => "Servicio de seguridad",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 46,
        "codigo" => 7161100201,
        "codigo_formateado" => "7.16.1.10.02.01",
        "cuenta" => "Energia electrica",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 47,
        "codigo" => 7161100301,
        "codigo_formateado" => "7.16.1.10.03.01",
        "cuenta" => "Servicio de telecomunicaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 48,
        "codigo" => 7161100401,
        "codigo_formateado" => "7.16.1.10.04.01",
        "cuenta" => "Alquileres",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 49,
        "codigo" => 7161100402,
        "codigo_formateado" => "7.16.1.10.04.02",
        "cuenta" => "Accesorios para computadoras",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 50,
        "codigo" => 7161100403,
        "codigo_formateado" => "7.16.1.10.04.03",
        "cuenta" => "Varios e imprevistos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 51,
        "codigo" => 7161100404,
        "codigo_formateado" => "7.16.1.10.04.04",
        "cuenta" => "Fletes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 52,
        "codigo" => 7161100405,
        "codigo_formateado" => "7.16.1.10.04.05",
        "cuenta" => "Material de empque",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 53,
        "codigo" => 7161100406,
        "codigo_formateado" => "7.16.1.10.04.06",
        "cuenta" => "Combustibles y lubricantes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 54,
        "codigo" => 716111,
        "codigo_formateado" => "7.16.1.11",
        "cuenta" => "PAPELERÍA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 55,
        "codigo" => 7161110101,
        "codigo_formateado" => "7.16.1.11.01.01",
        "cuenta" => "Papelería y útlies de oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 56,
        "codigo" => 716112,
        "codigo_formateado" => "7.16.1.12",
        "cuenta" => "OTROS GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 57,
        "codigo" => 7161120101,
        "codigo_formateado" => "7.16.1.12.01.01",
        "cuenta" => "Provisiones para indemnización ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 58,
        "codigo" => 7161120102,
        "codigo_formateado" => "7.16.1.12.01.02",
        "cuenta" => "Provisiones para cuentas incobrables ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 59,
        "codigo" => 7161120103,
        "codigo_formateado" => "7.16.1.12.01.03",
        "cuenta" => "Gastos no deducibles",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ]
];




// $listado[10]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6131010150')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010151')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010202')['saldo_final'];

$listado[4]['sub_total_col_3'] = buscar_cuenta_no_recursiva($respuesta, '6131030101')['saldo_final'];
$listado[9]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '716101020201')['saldo_final'];
$listado[11]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161020104')['saldo_final'];
$listado[12]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161020201')['saldo_final'];

$listado[15]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161030101')['saldo_final'] +  buscar_cuenta_no_recursiva($respuesta, '7161030102')['saldo_final'];

$listado[17]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161030301')['saldo_final'];


$listado[19]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161030401')['saldo_final'] +  buscar_cuenta_no_recursiva($respuesta, '7161030402')['saldo_final'];

$listado[20]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161030501')['saldo_final'];
$listado[21]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161030601')['saldo_final'];

$listado[24]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161030701')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161030702')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161030703')['saldo_final'];

//$listado[27]['sub_total_col_2'] = ;

$listado[29]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161040101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161040102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161040103')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161040104')['saldo_final'];

$listado[30]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161040201')['saldo_final'];

$listado[36]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161060201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161060202')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161060203')['saldo_final'];

$listado[37]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161060301')['saldo_final'];
$listado[39]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161070101')['saldo_final'];
$listado[41]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161080101')['saldo_final'];
//$listado[44]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161100101')['saldo_final'];
$listado[45]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161100101')['saldo_final'];
$listado[46]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161100201')['saldo_final'];

$listado[53]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161100401')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161100402')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161100403')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161100404')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161100405')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161100406')['saldo_final'];

$listado[54]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161110101')['saldo_final'];

$listado[59]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7161120101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161120102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7161120103')['saldo_final'];

$listado[59]['sub_total_col_3'] = $listado[9]['sub_total_col_2'] + $listado[11]['sub_total_col_2'] + $listado[12]['sub_total_col_2'] + $listado[15]['sub_total_col_2'] + $listado[17]['sub_total_col_2'] + $listado[19]['sub_total_col_2'] + $listado[20]['sub_total_col_2'] + $listado[21]['sub_total_col_2'] + $listado[24]['sub_total_col_2'] + $listado[28]['sub_total_col_2'] + $listado[29]['sub_total_col_2'] + $listado[30]['sub_total_col_2'] + $listado[36]['sub_total_col_2'] + $listado[37]['sub_total_col_2'] + $listado[39]['sub_total_col_2'] + $listado[41]['sub_total_col_2'] + $listado[44]['sub_total_col_2'] + $listado[45]['sub_total_col_2'] + $listado[46]['sub_total_col_2'] + $listado[53]['sub_total_col_2'] + $listado[54]['sub_total_col_2'] + $listado[58]['sub_total_col_2'];

$EXEDENTE_FINAL = abs($listado[4]['sub_total_col_3']) - abs($listado[59]['sub_total_col_3']);

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
            ESTADO DE PRODUCTOS Y GASTOS, LOTIFICACIÓN
        </div>
        <div class="titulo_sub">
            DEL ' . date('d/m/Y', strtotime($fecha_inicial)) . ' AL ' . date('d/m/Y', strtotime($fecha_final)) . '
        </div>
        <div class="titulo_sub">
            
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
                    <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">' . $codigo_cuenta_ . '</td>
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
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q' . number_format($resultado_['saldo_final'], 2, '.', ',') . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">' . $sub_total_col_3_ . '</td>
                </tr>
            ';
        } else {
            $html .= '
                <tr>
                    <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . $codigo_cuenta_ . '</td>
                    <td class="estilo_celda" style="width: ' . $row_2 . '%;">' . $nombre_cuenta_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_3 . '%;">' . $sub_total_col_1_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">' . $sub_total_col_2_ . '</td>
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">Q' . number_format($resultado_['saldo_final'], 2, '.', ',') . '</td>
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

?>