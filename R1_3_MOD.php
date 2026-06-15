<?php
date_default_timezone_set('America/Guatemala');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET');

require_once('mpdf/mpdf.php');

$fecha_inicial = $_GET['fecha_inicial'];
$fecha_final = $_GET['fecha_final'];
$centro_de_costo = 0; // $_GET['centro_de_costo'];
$token = $_GET['token'];
$env = $_GET['env'];
$ip_dev = "192.168.1.68";

//OBTENER ARBOL DE CUENTAS

$url = $env == 'p' ? "https://coopesitrabi.ddns.net/app/coope/api/contabilidad-transaccion-cabeceras/c/reporte-balance-saldos-ssi" : "http://". $ip_dev .":8009/api/contabilidad-transaccion-cabeceras/c/reporte-balance-saldos-ssi";

$url2 = $env == 'p' ? "https://coopesitrabi.ddns.net/app/coope/api/centros-de-costos/c/nombre/" . $centro_de_costo : "http://". $ip_dev .":8009/api/centros-de-costos/c/nombre/" . $centro_de_costo;

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
        "cuenta" => "PRODUCTOS  ",
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
        "cuenta" => "INGRESOS, OTROS NEGOCIOS SIN INTERMEDIACION FINANCIERA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 2,
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
        "pos" => 3,
        "codigo" => 613102,
        "codigo_formateado" => "6.13.1.02",
        "cuenta" => "SERVICIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 4,
        "codigo" => 61310201,
        "codigo_formateado" => "6.13.1.02.01",
        "cuenta" => "SERVICIOS A ASOCIADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 5,
        "codigo" => 6131020101,
        "codigo_formateado" => "6.13.1.02.01.01",
        "cuenta" => "Venta de ticket ( servicio ordinario ) exento",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 6,
        "codigo" => 6131020102,
        "codigo_formateado" => "6.13.1.02.01.02",
        "cuenta" => "Venta de ticket ( servicio expreso ) exento",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 7,
        "codigo" => 6131020103,
        "codigo_formateado" => "6.13.1.02.01.03",
        "cuenta" => "Venta de ticket ( servicio escolar ) exento",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 8,
        "codigo" => 61310202,
        "codigo_formateado" => "6.13.1.02.02.",
        "cuenta" => "SERVICIOS A NO ASOCIADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 9,
        "codigo" => 6131020201,
        "codigo_formateado" => "6.13.1.02.02.01",
        "cuenta" => "Venta de tickets ( servicio ordinario ) gravado",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 10,
        "codigo" => 6131020202,
        "codigo_formateado" => "6.13.1.02.02.02",
        "cuenta" => "Venta de tickets ( servicio expreso ) gravado",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 11,
        "codigo" => 6131020203,
        "codigo_formateado" => "6.13.1.02.02.03",
        "cuenta" => "Venta de tickets ( servicio escolar ) gravado",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 12,
        "codigo" => 6131020204,
        "codigo_formateado" => "6.13.1.02.02.04",
        "cuenta" => "Venta de tickets ( servicio hospital )  gravado",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 13,
        "codigo" => 613104,
        "codigo_formateado" => "6.13.1.04. .",
        "cuenta" => "OTROS PRODUCTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 14,
        "codigo" => 61310401,
        "codigo_formateado" => "6.13.1.04.01.",
        "cuenta" => "Otros Productos de asociados",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 15,
        "codigo" => 6131040102,
        "codigo_formateado" => "6.13.1.04.01.02",
        "cuenta" => "Productos varios sección transporte",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 16,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "Otros Productos de no asociados",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 17,
        "codigo" => 6131040202,
        "codigo_formateado" => "6.13.1.04.02.02",
        "cuenta" => "Productos varios sección transporte",
        "tipo" => "cuenta",
        "posicion" => 2,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 18,
        "codigo" => 0,
        "codigo_formateado" => "0",
        "cuenta" => "T O T A L ",
        "tipo" => "espacio",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 19,
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
        "pos" => 20,
        "codigo" => 715,
        "codigo_formateado" => "7.15. ...",
        "cuenta" => "GASTOS SECCION TRANSPORTE (  FINANCIEROS )",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 21,
        "codigo" => 7151,
        "codigo_formateado" => "7.15.1. ..",
        "cuenta" => "GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 22,
        "codigo" => 715102,
        "codigo_formateado" => "7.15.1.02. .",
        "cuenta" => "ORGANOS DIRECTIVOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 23,
        "codigo" => 71510201,
        "codigo_formateado" => "7.15.1.02.01.",
        "cuenta" => "DIETAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 24,
        "codigo" => 7151020104,
        "codigo_formateado" => "7.15.1.02.01.04",
        "cuenta" => "Otros",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 25,
        "codigo" => 7151020201,
        "codigo_formateado" => "7.15.1.02.02.01",
        "cuenta" => "Viáticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 26,
        "codigo" => 715103,
        "codigo_formateado" => "7.15.1.03. .",
        "cuenta" => "FUNCIONARIOS Y EMPLEADOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 27,
        "codigo" => 7151030101,
        "codigo_formateado" => "7.15.1.03.01.01",
        "cuenta" => "Sueldos permanentes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 28,
        "codigo" => 7151030102,
        "codigo_formateado" => "7.15.1.03.01.02",
        "cuenta" => "Sueldos eventuales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 29,
        "codigo" => 7151030201,
        "codigo_formateado" => "7.15.1.03.02.01",
        "cuenta" => "Aguinaldos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 30,
        "codigo" => 7151030301,
        "codigo_formateado" => "7.15.1.03.03.01",
        "cuenta" => "Indemnizaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 31,
        "codigo" => 7151030401,
        "codigo_formateado" => "7.15.1.03.04.01",
        "cuenta" => "Bonificación incentivo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 32,
        "codigo" => 7151030402,
        "codigo_formateado" => "7.15.1.03.04.02",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 33,
        "codigo" => 7151030501,
        "codigo_formateado" => "7.15.1.03.05.01",
        "cuenta" => "Cuota patronal Igss",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 34,
        "codigo" => 7151030601,
        "codigo_formateado" => "7.15.1.03.06.01",
        "cuenta" => "Vacaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 35,
        "codigo" => 7151030701,
        "codigo_formateado" => "7.15.1.03.07.01",
        "cuenta" => "Bono catorce",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 36,
        "codigo" => 7151030702,
        "codigo_formateado" => "7.15.1.03.07.02",
        "cuenta" => "Comisiones ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 37,
        "codigo" => 7151030703,
        "codigo_formateado" => "7.15.1.03.07.03",
        "cuenta" => "Bonificaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 38,
        "codigo" => 715104,
        "codigo_formateado" => "7.15.1.04. .",
        "cuenta" => "TRIBUTOS Y OTRAS CUOTAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 39,
        "codigo" => 7151040101,
        "codigo_formateado" => "7.15.1.04.01.01",
        "cuenta" => "Impuesto derivado del petroleo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 40,
        "codigo" => 7151040102,
        "codigo_formateado" => "7.15.1.04.01.02",
        "cuenta" => "IVA costo   ",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 41,
        "codigo" => 7151040103,
        "codigo_formateado" => "7.15.1.04.01.03",
        "cuenta" => "I.S.R TRIMESTRAL",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 42,
        "codigo" => 7151040104,
        "codigo_formateado" => "7.15.1.04.01.04",
        "cuenta" => "ISO TRIMESTRAL",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 43,
        "codigo" => 7151040201,
        "codigo_formateado" => "7.15.1.04.02.01",
        "cuenta" => "Impuesto y arbitrios fiscales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 44,
        "codigo" => 715105,
        "codigo_formateado" => "7.15.1.05. .",
        "cuenta" => "HONORARIOS PROFESIONALES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 45,
        "codigo" => 7151050101,
        "codigo_formateado" => "7.15.1.05.01.01",
        "cuenta" => "Honorarios profesionales",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 46,
        "codigo" => 7151060101,
        "codigo_formateado" => "7.15.1.06.01.01",
        "cuenta" => "Reparaciones de edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 47,
        "codigo" => 7151060201,
        "codigo_formateado" => "7.15.1.06.02.01",
        "cuenta" => "Mobiliario y equipo de oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 48,
        "codigo" => 7151060202,
        "codigo_formateado" => "7.15.1.06.02.02",
        "cuenta" => "Sistemas informáticos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 49,
        "codigo" => 7151060203,
        "codigo_formateado" => "7.15.1.06.02.03",
        "cuenta" => "Vehiculos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 50,
        "codigo" => 7151060301,
        "codigo_formateado" => "7.15.1.06.03.01",
        "cuenta" => "Reparaciones de herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 51,
        "codigo" => 715107,
        "codigo_formateado" => "7.15.1.07. .",
        "cuenta" => "MERCACEO Y PUBLICIDAD",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 52,
        "codigo" => 7151070101,
        "codigo_formateado" => "7.15.1.07.01.01",
        "cuenta" => "Educación y mercadeo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 53,
        "codigo" => 715108,
        "codigo_formateado" => "7.15.1.08. .",
        "cuenta" => "PRIMAS DE SEGUROS Y FIANZAS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 54,
        "codigo" => 7151080101,
        "codigo_formateado" => "7.15.1.08.01.01",
        "cuenta" => "Seguros y fianzas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 55,
        "codigo" => 715109,
        "codigo_formateado" => "7.15.1.09. .",
        "cuenta" => "DEPRECIACIONES Y AMORTIZACIONES",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 56,
        "codigo" => 7151090101,
        "codigo_formateado" => "7.15.1.09.01.01",
        "cuenta" => "Edificios",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 57,
        "codigo" => 7151090102,
        "codigo_formateado" => "7.15.1.09.01.02",
        "cuenta" => "Mobiliario y equipo",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 58,
        "codigo" => 7151090103,
        "codigo_formateado" => "7.15.1.09.01.03",
        "cuenta" => "Vehiculos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 59,
        "codigo" => 7151090201,
        "codigo_formateado" => "7.15.1.09.02.01",
        "cuenta" => "Equipo de computación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 60,
        "codigo" => 7151090202,
        "codigo_formateado" => "7.15.1.09.02.02",
        "cuenta" => "Herramientas",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 61,
        "codigo" => 7151099901,
        "codigo_formateado" => "7.15.1.09.99.01",
        "cuenta" => "Amortizaciones gstos de instalación",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 62,
        "codigo" => 7151099902,
        "codigo_formateado" => "7.15.1.09.99.02",
        "cuenta" => "Amortizaciones cuentas por amortizar",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 63,
        "codigo" => 715110,
        "codigo_formateado" => "7.15.1.10. .",
        "cuenta" => "GASTOS VARIOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 64,
        "codigo" => 7151100101,
        "codigo_formateado" => "7.15.1.10.01.01",
        "cuenta" => "Servicio de seguridad",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 65,
        "codigo" => 7151100201,
        "codigo_formateado" => "7.15.1.10.02.01",
        "cuenta" => "Energia electrica",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 66,
        "codigo" => 7151100301,
        "codigo_formateado" => "7.15.1.10.03.01",
        "cuenta" => "Servicio de telecomunicaciones",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 67,
        "codigo" => 7151100401,
        "codigo_formateado" => "7.15.1.10.04.01",
        "cuenta" => "Alquileres",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 68,
        "codigo" => 7151100402,
        "codigo_formateado" => "7.15.1.10.04.02",
        "cuenta" => "Accesorios para computadoras",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 69,
        "codigo" => 7151100403,
        "codigo_formateado" => "7.15.1.10.04.03",
        "cuenta" => "Varios e imprevistos",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 70,
        "codigo" => 7151100404,
        "codigo_formateado" => "7.15.1.10.04.04",
        "cuenta" => "Fletes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 71,
        "codigo" => 7151100405,
        "codigo_formateado" => "7.15.1.10.04.05",
        "cuenta" => "Material de empque",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 72,
        "codigo" => 7151100406,
        "codigo_formateado" => "7.15.1.10.04.06",
        "cuenta" => "Combustibles y lubricantes",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 73,
        "codigo" => 715111,
        "codigo_formateado" => "7.15.1.11. .",
        "cuenta" => "PAPELERÍA",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 74,
        "codigo" => 7151110101,
        "codigo_formateado" => "7.15.1.11.01.01",
        "cuenta" => "Papelería y útlies de oficina",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 75,
        "codigo" => 715112,
        "codigo_formateado" => "7.15.1.12. .",
        "cuenta" => "OTROS GASTOS",
        "tipo" => "titulo",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 76,
        "codigo" => 7151120101,
        "codigo_formateado" => "7.15.1.12.01.01",
        "cuenta" => "Provisiones para indemnización ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 77,
        "codigo" => 7151120102,
        "codigo_formateado" => "7.15.1.12.01.02",
        "cuenta" => "Provisiones para cuentas incobrables ( gasto )",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ],
    [
        "pos" => 78,
        "codigo" => 7151120103,
        "codigo_formateado" => "7.15.1.12.01.03",
        "cuenta" => "Gastos no deducibles",
        "tipo" => "cuenta",
        "posicion" => 1,
        "sub_total_col_1" => 0,
        "sub_total_col_2" => 0,
        "sub_total_col_3" => 0
    ]
];







// $listado[10]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '6131010150')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010151')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131010202')['saldo_final'];

$listado[17]['sub_total_col_3'] = buscar_cuenta_no_recursiva($respuesta, '6131020101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131020102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131020103')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131020201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131020202')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131020203')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '6131020204')['saldo_final'];

$listado[18]['sub_total_col_3'] = $listado[17]['sub_total_col_3'];

$listado[24]['sub_total_col_3'] = buscar_cuenta_no_recursiva($respuesta, '7151020104')['saldo_final'];

$listado[25]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151020104')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151020201')['saldo_final'];

$listado[28]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151030101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151030102')['saldo_final'];

$listado[29]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151030201')['saldo_final'];
$listado[30]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151030301')['saldo_final'];

$listado[32]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151030401')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151030402')['saldo_final'];

$listado[34]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151030501')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151030601')['saldo_final'];

$listado[37]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151030701')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151030702')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151030703')['saldo_final'];

$listado[42]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151040101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151040102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151040103')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151040104')['saldo_final'];

$listado[43]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151040201')['saldo_final'];

$listado[45]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151050101')['saldo_final'];
$listado[46]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151060101')['saldo_final'];

$listado[49]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151060201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151060202')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151060203')['saldo_final'];

$listado[50]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151060301')['saldo_final'];
$listado[52]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151070101')['saldo_final'];
$listado[54]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151080101')['saldo_final'];

$listado[58]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151090101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151090102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151090103')['saldo_final'];

$listado[60]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151090201')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151090202')['saldo_final'];

$listado[62]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151099901')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151099902')['saldo_final'];

$listado[64]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151100101')['saldo_final'];
$listado[65]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151100201')['saldo_final'];
$listado[66]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151100301')['saldo_final'];

$listado[72]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151100401')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151100402')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151100403')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151100404')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151100405')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151100406')['saldo_final'];

$listado[74]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151110101')['saldo_final'];

$listado[78]['sub_total_col_2'] = buscar_cuenta_no_recursiva($respuesta, '7151120101')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151120102')['saldo_final'] + buscar_cuenta_no_recursiva($respuesta, '7151120103')['saldo_final'];

$listado[78]['sub_total_col_3'] = $listado[24]['sub_total_col_2'] + $listado[25]['sub_total_col_2'] + $listado[28]['sub_total_col_2'] + $listado[29]['sub_total_col_2'] + $listado[30]['sub_total_col_2'] + $listado[32]['sub_total_col_2'] + $listado[34]['sub_total_col_2'] + $listado[37]['sub_total_col_2'] + $listado[42]['sub_total_col_2'] + $listado[43]['sub_total_col_2'] + $listado[45]['sub_total_col_2'] + $listado[46]['sub_total_col_2'] + $listado[49]['sub_total_col_2'] + $listado[50]['sub_total_col_2'] + $listado[52]['sub_total_col_2'] + $listado[54]['sub_total_col_2'] + $listado[58]['sub_total_col_2'] + $listado[60]['sub_total_col_2'] + $listado[62]['sub_total_col_2'] + $listado[64]['sub_total_col_2'] + $listado[65]['sub_total_col_2'] + $listado[66]['sub_total_col_2'] + $listado[72]['sub_total_col_2'] + $listado[74]['sub_total_col_2'] + $listado[78]['sub_total_col_2'];


$EXEDENTE_FINAL = abs($listado[17]['sub_total_col_3']) - abs($listado[78]['sub_total_col_3']);

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
            ESTADO DE PRODUCTOS Y GASTOS, SECCION TRANSPORTE
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
                <td class="estilo_celda2" style="width: ' . $row_1 . '%;">' . '</td>
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
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_4 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
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
                    <td class="estilo_celda centrar_texto" style="width: ' . $row_5 . '%;">Q' . number_format(abs($resultado_['saldo_final']), 2, '.', ',') . '</td>
                </tr>
            ';
        }
    }

    // $pos += 1;
}






$html .= '<tr>
                <td class="estilo_celda2 fondo_gris_titulo estilo_bold" style="width: ' . $row_1 . '%;">34-123</td>
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