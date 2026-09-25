<?php

require_once __DIR__ . '/../includes/confianza.php';

$origen      = origen_viaje((string) ($_GET['origen'] ?? ''));
$origenId    = $origen['id'] ?? 0;
$enColectivo = ($_GET['transporte'] ?? 'auto') === 'colectivo';
$destinoId   = (int) ($_GET['destino'] ?? 0);
$personas    = max(1, min(20, (int) ($_GET['personas'] ?? 2)));
$dias        = max(1, min(7, (int) ($_GET['dias'] ?? 2)));
$presupuesto = (float) solo_digitos($_GET['presupuesto'] ?? '');
$intereses   = array_values(array_filter(
    explode(',', $_GET['intereses'] ?? ''),
    fn($i) => isset(CATEGORIAS[$i])
));
$modo    = ($_GET['modo'] ?? 'armar') === 'sorpresa' ? 'sorpresa' : 'armar';
$excluir = (int) ($_GET['excluir'] ?? 0);
$noches  = $dias - 1;

if (!$origen) {
    error_json(t('Elegí desde dónde salís.'));
}
if ($presupuesto <= 0) {
    error_json(t('Escribí cuánto querés gastar.'));
}

$destinos = db()->query('SELECT * FROM localidades WHERE es_destino = 1')->fetchAll();

$porLocalidad = fn(array $filas) => array_reduce($filas, function ($acc, $f) {
    $acc[$f['localidad_id']][] = $f;
    return $acc;
}, []);

$alojamientos = $porLocalidad(db()->query("
    SELECT a.*, u.email_verificado, u.dni, u.cuit,
           (SELECT AVG(r.puntuacion) FROM resenas r WHERE r.alojamiento_id = a.id) AS puntuacion,
           " . SQL_STATS_PRESTADOR . "
    FROM alojamientos a
    JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.estado = 'activo' AND u.estado = 'activo'
")->fetchAll());

$actividades = $porLocalidad(db()->query("
    SELECT x.id, x.localidad_id, x.nombre, x.nombre_en, x.categoria, x.precio_persona AS costo, x.duracion_horas,
           'actividad' AS tipo
    FROM actividades x
    LEFT JOIN usuarios u ON u.id = x.usuario_id
    WHERE x.estado = 'activo' AND (u.id IS NULL OR u.estado = 'activo')
")->fetchAll());

$lugares = $porLocalidad(db()->query("
    SELECT id, localidad_id, nombre, nombre_en, categoria, costo_persona AS costo, destacado, 'lugar' AS tipo
    FROM lugares
")->fetchAll());

$nafta     = parametro('precio_nafta_litro');
$consumo   = parametro('consumo_litros_100km');
$comidaDia = parametro('comida_persona_dia');
$pasajeKm  = parametro('pasaje_colectivo_km');
$pasajeMin = parametro('pasaje_colectivo_minimo');

$evaluados = [];

foreach ($destinos as $d) {
    if ((int) $d['id'] === $origenId || ($destinoId && (int) $d['id'] !== $destinoId)) {
        continue;
    }

    $km = distancia_ruta_km($origen['lat'], $origen['lng'], $d['lat'], $d['lng']);
    if ($enColectivo) {
        $pasaje     = round(max($pasajeMin, $km * $pasajeKm), -2);
        $transporte = $pasaje * 2 * $personas;
    } else {
        $pasaje     = 0;
        $autos      = (int) ceil($personas / 5);
        $transporte = round($km * 2 * $consumo / 100 * $nafta * $autos);
    }
    $comida = $comidaDia * $personas * $dias;
    $base   = $transporte + $comida;

    $aloj = null;
    $alojBarato = null;
    $costoAloj = 0;
    if ($noches > 0) {
        $candidatos = [];
        foreach ($alojamientos[$d['id']] ?? [] as $a) {
            if ($a['capacidad'] < $personas) {
                continue;
            }
            $a['costo'] = costo_alojamiento($a, $personas, $noches);
            $a['nivel'] = nivel_confianza($a);
            $candidatos[] = $a;
        }
        if (!$candidatos) {
            continue;
        }
        usort($candidatos, fn($x, $y) => $x['costo'] <=> $y['costo']);
        $alojBarato = $candidatos[0];

        $entran = array_filter($candidatos, fn($a) => $base + $a['costo'] <= $presupuesto);
        if ($entran) {
            usort($entran, fn($x, $y) =>
                [$y['nivel']['nivel'], (float) $y['puntuacion'], -$y['costo']]
                <=> [$x['nivel']['nivel'], (float) $x['puntuacion'], -$x['costo']]);
            $aloj = reset($entran);
        } else {
            $aloj = $alojBarato;
        }
        $costoAloj = $aloj['costo'];
    }

    $opciones = array_merge($actividades[$d['id']] ?? [], $lugares[$d['id']] ?? []);
    $gastronomia = array_values(array_filter($opciones, fn($o) => $o['categoria'] === 'gastronomia'));
    $paseos      = array_values(array_filter($opciones, fn($o) => $o['categoria'] !== 'gastronomia'));

    $coincide = fn($o) => !$intereses || in_array($o['categoria'], $intereses, true);
    usort($paseos, fn($x, $y) =>
        [$coincide($y), (int) ($y['destacado'] ?? 0), -$y['costo']]
        <=> [$coincide($x), (int) ($x['destacado'] ?? 0), -$x['costo']]);

    $lugaresPorDia = $dias === 1 ? 2 : $dias * 2 - 1;
    $elegidos = [];
    $costoAct = 0;
    foreach ($paseos as $p) {
        if (count($elegidos) >= $lugaresPorDia) {
            break;
        }
        $costo = $p['costo'] * $personas;
        if ($costo > 0 && $base + $costoAloj + $costoAct + $costo > $presupuesto) {
            continue;
        }
        $p['costo_total'] = $costo;
        $elegidos[] = $p;
        $costoAct += $costo;
    }

    $total = $transporte + $costoAloj + $comida + $costoAct;

    $categoriasCubiertas = array_unique(array_map(fn($o) => $o['categoria'], array_filter($paseos, $coincide)));
    $coincidencias = $intereses ? count(array_intersect($intereses, $categoriasCubiertas)) : 0;
    $destacados = count(array_filter($paseos, fn($o) => !empty($o['destacado'])));
    $holgura = $presupuesto > 0 ? ($presupuesto - $total) / $presupuesto : 0;
    $puntaje = $coincidencias * 30 + count($elegidos) * 4 + $destacados * 3 + $holgura * 20 - $km / 40;

    $evaluados[] = [
        'd' => $d, 'km' => $km, 'transporte' => $transporte, 'pasaje' => $pasaje, 'comida' => $comida,
        'aloj' => $aloj, 'aloj_barato' => $alojBarato, 'costo_aloj' => $costoAloj,
        'elegidos' => $elegidos, 'gastronomia' => $gastronomia, 'costo_act' => $costoAct,
        'total' => $total, 'entra' => $total <= $presupuesto, 'puntaje' => $puntaje,
        'coincidencias' => array_values(array_intersect($intereses, $categoriasCubiertas)),
    ];
}

$viaje = [
    'origen' => $origen, 'personas' => $personas, 'dias' => $dias, 'noches' => $noches,
    'presupuesto' => $presupuesto, 'intereses' => $intereses, 'colectivo' => $enColectivo,
];

$aviso = null;
if ($evaluados) {
    $horasMin = min(array_map(fn($e) => horas_viaje($e['km'], $enColectivo), $evaluados));
    if ($horasMin > 5) {
        $aviso = t('Desde {origen} son ≈ {horas} de viaje por tramo: te conviene viajar al menos {n} días.', [
            'origen' => $origen['nombre'], 'horas' => texto_horas($horasMin), 'n' => $horasMin > 10 ? 4 : 3,
        ]);
    }
}

$consejosExtranjero = [];
if ($origen['extranjero']) {
    if (($_GET['origen'] ?? '') === 'ext:asuncion') {
        $consejosExtranjero[] = t('Desde Asunción se cruza por el puente San Ignacio de Loyola, en Clorinda. Ahí están los controles de migraciones y aduana.');
    }
    $consejosExtranjero[] = t('Llevá pasaporte. Si sos de un país del Mercosur, alcanza con tu documento de identidad.');
    $consejosExtranjero[] = t('En Formosa se paga en pesos argentinos (ARS). En los pueblos hay pocos cajeros: llevá efectivo.');
    $consejosExtranjero[] = t('Los montos del plan son aproximados y están en pesos argentinos.');
}

if ($destinoId) {
    if (!$evaluados) {
        error_json(t('Ese plan ya no está disponible. Armá uno nuevo con el formulario.'));
    }
    responder_json([
        'ok' => true, 'modo' => 'compartido', 'aviso' => $aviso, 'extranjero' => $consejosExtranjero,
        'destinos' => [formatear_destino($evaluados[0], $viaje)],
    ]);
}

$viables = array_values(array_filter($evaluados, fn($e) => $e['entra']));
usort($viables, fn($x, $y) => $y['puntaje'] <=> $x['puntaje']);

if (!$viables) {
    usort($evaluados, fn($x, $y) => $x['total'] <=> $y['total']);
    $masBarato = $evaluados[0] ?? null;
    responder_json([
        'ok'         => true,
        'modo'       => $modo,
        'aviso'      => $aviso,
        'extranjero' => $consejosExtranjero,
        'destinos'   => [],
        'sugerencia' => $masBarato ? [
            'destino' => $masBarato['d']['nombre'],
            'total'   => $masBarato['total'],
            'falta'   => $masBarato['total'] - $presupuesto,
        ] : null,
    ]);
}

if ($modo === 'sorpresa') {
    $pool = array_slice(array_values(array_filter($viables, fn($v) => (int) $v['d']['id'] !== $excluir)), 0, 4)
        ?: array_slice($viables, 0, 1);
    $viables = [$pool[array_rand($pool)]];
} else {
    $viables = array_slice($viables, 0, 5);
}

responder_json([
    'ok'       => true,
    'modo'     => $modo,
    'aviso'    => $aviso,
    'extranjero' => $consejosExtranjero,
    'destinos' => array_map(fn($e) => formatear_destino($e, $viaje), $viables),
    'todos_nombres' => array_map(fn($e) => $e['d']['nombre'], $evaluados),
]);

function horas_viaje(float $km, bool $colectivo): float
{
    return $km / ($colectivo ? 65 : 80);
}

function texto_horas(float $horas): string
{
    return $horas < 1 ? t('{n} min', ['n' => round($horas * 60)]) : t('{n} h', ['n' => round($horas, 1)]);
}

function formatear_destino(array $e, array $viaje): array
{
    ['origen' => $origen, 'personas' => $personas, 'dias' => $dias, 'noches' => $noches,
     'presupuesto' => $presupuesto, 'colectivo' => $colectivo] = $viaje;
    $d = $e['d'];
    $horas = horas_viaje($e['km'], $colectivo);
    $horasTxt = texto_horas($horas);

    $razones = [];
    $razones[] = $e['entra']
        ? t('Entra en tu presupuesto (te sobran {monto})', ['monto' => pesos($presupuesto - $e['total'])])
        : t('Se pasa de tu presupuesto por {monto}', ['monto' => pesos($e['total'] - $presupuesto)]);
    if ($e['aloj']) {
        $razones[] = t('Tiene lugar para dormir para {n} personas: {lugar}', ['n' => $personas, 'lugar' => $e['aloj']['nombre']]);
    }
    if ($e['coincidencias']) {
        $razones[] = t('Tiene lo que te gusta: {lista}', ['lista' => implode(', ', array_map(
            fn($c) => t(CATEGORIAS[$c]['nombre']), $e['coincidencias']))]);
    }
    $razones[] = t('Está a {km} km de {origen} (≈ {horas} de viaje)', ['km' => $e['km'], 'origen' => $origen['nombre'], 'horas' => $horasTxt]);
    if ($dias > 1) {
        $razones[] = t('Ideal para {n} días', ['n' => $dias]);
    } elseif ($horas <= 3) {
        $razones[] = t('Ideal para ir y volver en el día');
    }

    $ahorro = null;
    if ($e['aloj'] && $e['aloj_barato'] && $e['aloj_barato']['id'] !== $e['aloj']['id']) {
        $dif = $e['aloj']['costo'] - $e['aloj_barato']['costo'];
        if ($dif > 0) {
            $ahorro = t('Podés ahorrar {monto} ({pct}%) si elegís {lugar}.', [
                'monto' => pesos($dif), 'pct' => round($dif / $e['total'] * 100), 'lugar' => $e['aloj_barato']['nombre'],
            ]);
        }
    }

    $items = $e['elegidos'];
    $comidas = $e['gastronomia'];
    $itinerario = [];
    $iconoViaje = $colectivo ? 'colectivo' : 'auto';
    for ($dia = 1; $dia <= $dias; $dia++) {
        $pasos = [];
        if ($dia === 1) {
            $salida = ['origen' => $origen['nombre'], 'km' => $e['km'], 'horas' => $horasTxt];
            $pasos[] = ['icono' => $iconoViaje, 'texto' => $colectivo
                ? t('Salida en colectivo desde {origen} ({km} km, ≈ {horas})', $salida)
                : t('Salida desde {origen} ({km} km, ≈ {horas})', $salida)];
            if ($e['aloj']) {
                $pasos[] = ['icono' => 'cama', 'texto' => t('Llegada a {lugar}', ['lugar' => $e['aloj']['nombre']])];
            }
        }
        $cuantos = ($dias === 1) ? 2 : (($dia === 1 || $dia === $dias) ? 1 : 2);
        for ($i = 0; $i < $cuantos && $items; $i++) {
            $it = array_shift($items);
            $precio = match (true) {
                $it['costo'] === null   => t('consultar precio'),
                $it['costo_total'] > 0  => pesos($it['costo_total']),
                default                 => t('gratis'),
            };
            $pasos[] = ['icono' => CATEGORIAS[$it['categoria']]['icono'], 'texto' => traducido($it, 'nombre') . " ($precio)"];
        }
        if ($comidas) {
            $c = $comidas[($dia - 1) % count($comidas)];
            $lugar = ['lugar' => traducido($c, 'nombre')];
            $pasos[] = ['icono' => 'cubiertos', 'texto' => $dia === $dias ? t('Almuerzo en {lugar}', $lugar) : t('Cena en {lugar}', $lugar)];
        }
        if ($dia === $dias) {
            $pasos[] = ['icono' => $iconoViaje, 'texto' => t('Regreso a {origen}', ['origen' => $origen['nombre']])];
        }
        $itinerario[] = ['dia' => $dia, 'pasos' => $pasos];
    }

    return [
        'id'          => (int) $d['id'],
        'nombre'      => $d['nombre'],
        'descripcion' => traducido($d, 'descripcion'),
        'km'          => $e['km'],
        'horas'       => $horasTxt,
        'transporte'  => $colectivo ? 'colectivo' : 'auto',
        'pasaje'      => $e['pasaje'],
        'desglose'    => [
            'transporte'  => $e['transporte'],
            'alojamiento' => $e['costo_aloj'],
            'comida'      => $e['comida'],
            'actividades' => $e['costo_act'],
        ],
        'total'       => $e['total'],
        'sobra'       => $presupuesto - $e['total'],
        'alojamiento' => $e['aloj'] ? [
            'id'          => (int) $e['aloj']['id'],
            'nombre'      => $e['aloj']['nombre'],
            'tipo'        => t(TIPOS_ALOJAMIENTO[$e['aloj']['tipo']]['nombre']),
            'icono'       => TIPOS_ALOJAMIENTO[$e['aloj']['tipo']]['icono'],
            'costo'       => $e['aloj']['costo'],
            'noches'      => $noches,
            'nivel_texto' => $e['aloj']['nivel']['texto'],
            'nivel_icono' => $e['aloj']['nivel']['icono'],
            'nivel_clase' => $e['aloj']['nivel']['clase'],
        ] : null,
        'razones'     => $razones,
        'ahorro'      => $ahorro,
        'itinerario'  => $itinerario,
    ];
}
