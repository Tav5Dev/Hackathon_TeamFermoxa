USE turismo_fermosa;
SET NAMES utf8mb4;

INSERT INTO parametros (clave, valor, descripcion) VALUES
('precio_nafta_litro',       1650.00, 'Precio del litro de nafta súper (ARS)'),
('consumo_litros_100km',        8.00, 'Consumo promedio de un auto (litros cada 100 km)'),
('comida_persona_dia',      15000.00, 'Gasto estimado en comida por persona por día (ARS)'),
('pasaje_colectivo_km',        70.00, 'Pasaje de colectivo estimado: pesos por km, por persona y por tramo'),
('pasaje_colectivo_minimo',  5000.00, 'Pasaje de colectivo mínimo por persona y por tramo (ARS)'),
('factor_ruta',                 1.30, 'Multiplicador de distancia en línea recta a distancia por ruta'),
('radio_cercanos_km',          20.00, 'Radio para "¿Qué hacer cerca?" (km)'),
('umbral_reportes',             3.00, 'Reportes para ocultar una publicación automáticamente'),
('precio_sospechoso_pct',      40.00, 'Precio por debajo de este % del promedio de la zona = sospechoso'),
('max_publicaciones_nueva',     3.00, 'Máximo de publicaciones para una cuenta nueva'),
('dias_cuenta_nueva',           7.00, 'Días que una cuenta se considera nueva'),
('nivel3_min_resenas',          3.00, 'Reseñas mínimas para Nivel 3 Confiable'),
('nivel3_min_promedio',         4.00, 'Promedio mínimo de reseñas para Nivel 3 Confiable');

INSERT INTO localidades (id, nombre, slug, descripcion, lat, lng, es_destino) VALUES
(1, 'Formosa Capital', 'formosa-capital',
 'Capital provincial a orillas del río Paraguay. Costanera, museos, gastronomía litoraleña y la reserva urbana Laguna Oca.',
 -26.177500, -58.178100, 1),
(2, 'Herradura', 'herradura',
 'Localidad turística junto a la laguna Herradura, a unos 45 km de la capital. Pesca, balneario y descanso.',
 -26.487000, -58.310000, 1),
(3, 'Clorinda', 'clorinda',
 'Ciudad de frontera frente a Asunción del Paraguay. Costanera, comercio y cultura de frontera.',
 -25.284400, -57.718500, 1),
(4, 'Laguna Blanca', 'laguna-blanca',
 'Puerta de entrada al Parque Nacional Río Pilcomayo y sede de la Fiesta Nacional del Pomelo.',
 -25.128700, -58.251700, 1),
(5, 'Pirané', 'pirane',
 'Ciudad del centro-este provincial sobre la Ruta Nacional 81.',
 -25.732000, -59.108300, 0),
(6, 'El Colorado', 'el-colorado',
 'Localidad a orillas del río Bermejo, conocida por la pesca y la producción agrícola.',
 -26.308300, -59.372200, 1),
(7, 'Las Lomitas', 'las-lomitas',
 'Base para visitar el Bañado La Estrella, uno de los humedales más grandes de Argentina.',
 -24.709400, -60.594000, 1),
(8, 'Ingeniero Juárez', 'ingeniero-juarez',
 'Ciudad del oeste formoseño, en el corazón del monte chaqueño.',
 -23.899400, -61.855000, 0);

INSERT INTO lugares (localidad_id, nombre, categoria, descripcion, lat, lng, costo_persona, destacado) VALUES
(1, 'Paseo Costanero Vuelta Fermoza', 'naturaleza',
 'Costanera sobre el río Paraguay para caminar, andar en bici y ver el atardecer.', -26.186000, -58.165000, 0, 1),
(1, 'Reserva Urbana Laguna Oca', 'fauna',
 'Humedal urbano con senderos y gran variedad de aves.', -26.215000, -58.155000, 0, 1),
(1, 'Museo Histórico Regional', 'cultura',
 'Museo en el centro histórico con la historia de la ciudad y la provincia.', -26.185000, -58.174000, 0, 0),
(1, 'Comedor Sabores del Litoral', 'gastronomia',
 'Pescados de río, milanesa de surubí y chipá. (Comercio ficticio de demo)', -26.182000, -58.172000, 18000, 0),
(1, 'Chipería La Abuela', 'gastronomia',
 'Chipá, sopa paraguaya y mbeyú recién hechos. (Comercio ficticio de demo)', -26.179000, -58.180000, 6000, 0),
(2, 'Laguna Herradura', 'pesca',
 'Espejo de agua ideal para pesca deportiva y paseos en kayak.', -26.495000, -58.300000, 0, 1),
(2, 'Balneario de Herradura', 'playa',
 'Playa de arena con sombra, ideal para ir en familia en verano.', -26.490000, -58.303000, 2000, 1),
(2, 'Comedor El Pescador', 'gastronomia',
 'Parrilla y pescados de la laguna. (Comercio ficticio de demo)', -26.486000, -58.309000, 16000, 0),
(3, 'Costanera del Río Paraguay', 'naturaleza',
 'Paseo ribereño con vista a la costa paraguaya.', -25.290000, -57.710000, 0, 0),
(3, 'Puente San Ignacio de Loyola', 'cultura',
 'Paso fronterizo internacional que une Clorinda con Paraguay.', -25.300000, -57.700000, 0, 0),
(3, 'Parrillada La Frontera', 'gastronomia',
 'Asado y cocina de frontera. (Comercio ficticio de demo)', -25.285000, -57.719000, 17000, 0),
(4, 'Parque Nacional Río Pilcomayo', 'naturaleza',
 'Área protegida con esteros, palmares y fauna silvestre.', -25.080000, -58.140000, 0, 1),
(4, 'Estero Poí', 'fauna',
 'Sector del parque con pasarelas sobre el estero para observar aves y yacarés.', -25.120000, -58.120000, 0, 1),
(4, 'Fiesta Nacional del Pomelo', 'cultura',
 'Fiesta popular con música, artesanías y productos regionales.', -25.129000, -58.251000, 0, 0),
(4, 'Comedor Los Pomelos', 'gastronomia',
 'Cocina casera y dulces regionales. (Comercio ficticio de demo)', -25.127000, -58.250000, 14000, 0),
(6, 'Río Bermejo', 'pesca',
 'Río de aguas rojizas con buena pesca de dorados y surubíes.', -26.300000, -59.360000, 0, 1),
(6, 'Comedor Ribera del Bermejo', 'gastronomia',
 'Pescado frito y guisos de río. (Comercio ficticio de demo)', -26.307000, -59.371000, 15000, 0),
(7, 'Bañado La Estrella', 'naturaleza',
 'Enorme humedal con árboles muertos en pie, aves y paisajes únicos al atardecer.', -24.370000, -60.620000, 0, 1),
(7, 'Mirador de la Ruta 28 sobre el Bañado', 'fauna',
 'Terraplén que atraviesa el bañado: uno de los mejores puntos para fotografía y aves.', -24.450000, -60.630000, 0, 1),
(7, 'Comedor El Algarrobo', 'gastronomia',
 'Comida criolla y chivito al asador. (Comercio ficticio de demo)', -24.710000, -60.593000, 15000, 0);

INSERT INTO usuarios (id, nombre, apellido, email, password_hash, telefono, dni, cuit, email_verificado, creado_en) VALUES
(1, 'Marta',  'Benítez', 'marta@demo.com',  '$2y$10$0TabSWfxHwcEvkhTrqKIO.ZmDV3LftGv3rtgYx4Vq.3TRGzl7u6QK', '3704000001', '28987654', '27289876540', 1, '2026-03-10 10:00:00'),
(2, 'Carlos', 'Duarte',  'carlos@demo.com', '$2y$10$0TabSWfxHwcEvkhTrqKIO.ZmDV3LftGv3rtgYx4Vq.3TRGzl7u6QK', '3704000002', '30123456', '20301234563', 1, '2026-05-02 10:00:00'),
(3, 'Rubén',  'Sosa',    'ruben@demo.com',  '$2y$10$0TabSWfxHwcEvkhTrqKIO.ZmDV3LftGv3rtgYx4Vq.3TRGzl7u6QK', '3704000003', '35111222', '20351112221', 1, '2026-04-15 10:00:00'),
(4, 'Laura',  'Giménez', 'laura@demo.com',  '$2y$10$0TabSWfxHwcEvkhTrqKIO.ZmDV3LftGv3rtgYx4Vq.3TRGzl7u6QK', '3704000004', NULL,       NULL,          1, '2026-09-20 10:00:00');

INSERT INTO alojamientos
(id, usuario_id, localidad_id, tipo, nombre, descripcion, precio, modalidad_precio, capacidad, habitaciones,
 pileta, parrilla, wifi, aire, mascotas, estacionamiento, direccion, lat, lng, telefono_contacto, estado, motivo_revision) VALUES
(1, 1, 2, 'cabana', 'Cabaña lo de Lalo', 'Cabañas equipadas a 200 m de la laguna, con galería y parque.',
 85000, 'por_noche', 5, 2, 1, 1, 1, 1, 1, 1, 'Costanera s/n', -26.490000, -58.305000, '3704000001', 'activo', NULL),
(2, 2, 2, 'camping', 'Camping Herradura Verde', 'Parcelas con sombra, baños con agua caliente y fogones.',
 7000, 'por_persona', 40, 0, 0, 1, 0, 0, 1, 1, 'Acceso a la laguna', -26.493000, -58.302000, '3704000002', 'activo', NULL),
(4, 3, 1, 'hotel', 'Hotel Costanera Sur', 'Habitaciones dobles frente a la costanera, con desayuno.',
 55000, 'por_noche', 2, 1, 0, 0, 1, 1, 0, 1, 'Av. Costanera 1200', -26.184000, -58.170000, '3704000003', 'activo', NULL),
(5, 4, 1, 'departamento', 'Departamento Centro Formosa', 'Departamento de 2 ambientes a pasos de la plaza principal.',
 45000, 'por_noche', 4, 2, 0, 0, 1, 1, 0, 0, 'Centro', -26.180000, -58.176000, '3704000004', 'activo', NULL),
(7, 4, 3, 'cabana', 'Cabaña Río Paraguay', 'Cabaña con vista al río, parrilla y patio cerrado.',
 70000, 'por_noche', 4, 2, 0, 1, 0, 1, 1, 1, 'Costanera', -25.290000, -57.712000, '3704000004', 'activo', NULL),
(8, 1, 4, 'cabana', 'Cabañas El Pomelar', 'Cabañas entre plantaciones de pomelo, con pileta y quincho.',
 65000, 'por_noche', 5, 2, 1, 1, 1, 1, 0, 1, 'Ruta 86 km 5', -25.130000, -58.248000, '3704000001', 'activo', NULL),
(9, 2, 4, 'camping', 'Camping Estero Poí', 'Camping rústico cerca del parque nacional. Ideal para madrugar y ver aves.',
 6000, 'por_persona', 30, 0, 0, 1, 0, 0, 1, 1, 'Camino al parque', -25.115000, -58.200000, '3704000002', 'activo', NULL),
(12, 2, 7, 'camping', 'Camping La Estrella', 'Camping municipal con fogones y sombra de algarrobos.',
 5000, 'por_persona', 25, 0, 0, 1, 0, 0, 1, 1, 'Acceso norte', -24.712000, -60.598000, '3704000002', 'activo', NULL),
(14, 4, 2, 'cabana', 'SUPER OFERTA cabaña frente a la laguna', 'Precio increíble, seña por transferencia para reservar.',
 9000, 'por_noche', 6, 3, 1, 1, 1, 1, 1, 1, 'Consultar', -26.491000, -58.306000, '3704000004', 'revision',
 'Precio 88% por debajo del promedio de la zona y 3 reportes de la comunidad');

INSERT INTO actividades
(usuario_id, localidad_id, nombre, categoria, descripcion, duracion_horas, precio_persona, edad_minima, lat, lng) VALUES
(1,    7, 'Atardecer en el Bañado La Estrella', 'naturaleza',
 'Salida guiada al bañado para ver el atardecer entre los árboles sumergidos. Incluye mate y chipá.', 4.0, 25000, 6, -24.400000, -60.620000),
(NULL, 7, 'Avistamiento de aves en el Bañado', 'fauna',
 'Recorrido por el terraplén de la Ruta 28 con más de 100 especies registradas. Llevar binoculares.', 3.0, 0, 8, -24.450000, -60.630000),
(3,    2, 'Pesca deportiva en Laguna Herradura', 'pesca',
 'Salida en lancha con guía, equipo incluido. Modalidad con devolución.', 5.0, 30000, 12, -26.495000, -58.300000),
(2,    2, 'Kayak en la laguna Herradura', 'aventura',
 'Paseo en kayak doble con instructor. Chaleco incluido.', 2.0, 18000, 10, -26.492000, -58.301000),
(NULL, 1, 'Avistamiento de aves en Laguna Oca', 'fauna',
 'Circuito autoguiado por los senderos de la reserva urbana. Ideal temprano a la mañana.', 2.0, 0, 0, -26.215000, -58.155000),
(4,    1, 'Recorrido gastronómico formoseño', 'gastronomia',
 'Degustación de chipá, sopa paraguaya, chipá guazú y mbeyú en tres paradas del centro.', 3.0, 22000, 0, -26.181000, -58.175000),
(NULL, 1, 'Circuito histórico por el centro', 'cultura',
 'Caminata por la plaza, la catedral, el museo y la costanera.', 2.0, 0, 0, -26.185000, -58.174000),
(NULL, 4, 'Senderismo en el Estero Poí', 'naturaleza',
 'Sendero y pasarelas dentro del Parque Nacional Río Pilcomayo.', 3.0, 0, 6, -25.120000, -58.120000),
(2,    3, 'Kayak en el río Paraguay', 'aventura',
 'Remada guiada por la costa del río Paraguay con vista a la otra orilla.', 2.5, 20000, 12, -25.290000, -57.708000),
(3,    6, 'Pesca en el río Bermejo', 'pesca',
 'Jornada completa de pesca de dorados con guía local y almuerzo en la costa.', 6.0, 35000, 12, -26.300000, -59.360000);

INSERT INTO consultas (id, alojamiento_id, nombre, email, telefono, fecha_desde, fecha_hasta, personas, mensaje, estado, token_resena, creado_en) VALUES
(1, 1,  'Jorge P.',   'jorge@ejemplo.com',  NULL, '2026-07-10', '2026-07-12', 4, 'Vamos en familia', 'cerrada', MD5('demo-consulta-1'), '2026-07-01 09:00:00'),
(2, 8,  'Silvia R.',  'silvia@ejemplo.com', NULL, '2026-07-20', '2026-07-22', 2, NULL,               'cerrada', MD5('demo-consulta-2'), '2026-07-12 18:00:00'),
(4, 1,  'Ana L.',     'ana@ejemplo.com',    NULL, '2026-08-15', '2026-08-17', 5, NULL,               'cerrada', MD5('demo-consulta-4'), '2026-08-02 15:00:00'),
(7, 1,  'Sofía T.',   'sofia@ejemplo.com',  NULL, '2026-10-10', '2026-10-12', 4, '¿Aceptan perros?', 'nueva',   MD5('demo-consulta-7'), '2026-09-22 21:00:00');

INSERT INTO resenas (consulta_id, alojamiento_id, puntuacion, comentario, creado_en) VALUES
(1, 1,  5, 'Hermoso lugar, muy limpio y Marta súper atenta.',            '2026-07-13 10:00:00'),
(2, 8,  5, 'La pileta entre los pomelos es un lujo. Volveremos.',        '2026-07-23 10:00:00'),
(4, 1,  4, 'Todo muy lindo, el wifi un poco lento.',                     '2026-08-18 10:00:00');

INSERT INTO reportes (tipo_entidad, entidad_id, motivo, detalle, ip_hash, creado_en) VALUES
('alojamiento', 14, 'fraude',          'Pide seña por transferencia antes de mostrar el lugar', SHA2('demo-ip-1', 256), '2026-09-21 10:00:00'),
('alojamiento', 14, 'precio_enganoso', 'Precio irreal para la zona',                             SHA2('demo-ip-2', 256), '2026-09-21 15:00:00'),
('alojamiento', 14, 'fotos_falsas',    'Las fotos son de otra publicación',                      SHA2('demo-ip-3', 256), '2026-09-22 09:00:00');

INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5, orden) VALUES
('alojamiento', 12, 'uploads/images/Bañado la estrella.jpeg', '9f590f128209b9444dc6b05988a62ee5', 0),
('alojamiento', 5, 'uploads/images/departamento centro.jpeg', '510528f9ddcfb8f71954a9d7ed797915', 0),
('alojamiento', 4, 'uploads/images/hotel costanera fsa.jpeg', '79b880cf1e3056ea21e60d4d95863652', 0),
('alojamiento', 1, 'uploads/images/Cabaña lo de lalo herradura.png', '082e96a028e0832001a27c9ca75de2d0', 0),
('alojamiento', 9, 'uploads/images/camping estero poi.jpeg', 'c544c6419612a94907ceb074c2c0b454', 0),
('alojamiento', 8, 'uploads/images/hotel laguna blanca.jpeg', '1d1aeb5dfdff3772361c65d607fbcb24', 0),
('alojamiento', 7, 'uploads/images/Cabañas la esmeralda.jpeg', '88f7204733e828faf1406740471d5ec0', 0),
('alojamiento', 2, 'uploads/images/Camping caperucita roja.webp', '07e800b57967eeac67fcd494d388b17c', 0);

UPDATE localidades SET imagen = 'uploads/images/portada-ciudad-formosa-8.jpg.jpeg' WHERE id = 1;
UPDATE localidades SET imagen = 'uploads/images/Entrada el Colorado.jpeg' WHERE id = 6;
UPDATE localidades SET imagen = 'uploads/images/las-lomitas.jpg.jpeg' WHERE id = 7;
UPDATE localidades SET imagen = 'uploads/images/laguna-blanca-entrada.jpg.jpeg' WHERE id = 4;
UPDATE localidades SET imagen = 'uploads/images/clorinda entrada.jpg' WHERE id = 3;
UPDATE localidades SET imagen = 'uploads/images/herradura-portada.jpg' WHERE id = 2;

UPDATE localidades SET descripcion_en = CASE id
    WHEN 1 THEN 'Provincial capital on the banks of the Paraguay River. Riverside promenade, museums, river cuisine and the Laguna Oca urban nature reserve.'
    WHEN 2 THEN 'Small tourist town next to Laguna Herradura, about 45 km from the capital. Fishing, a lakeside beach and relaxation.'
    WHEN 3 THEN 'Border city across the river from Asunción, Paraguay. Riverside promenade, shopping and border culture.'
    WHEN 4 THEN 'Gateway to Río Pilcomayo National Park and home of the National Grapefruit Festival.'
    WHEN 5 THEN 'Town in the center-east of the province on National Route 81.'
    WHEN 6 THEN 'Town on the banks of the Bermejo River, known for fishing and farming.'
    WHEN 7 THEN 'Base for visiting Bañado La Estrella, one of the largest wetlands in Argentina.'
    WHEN 8 THEN 'City in western Formosa, in the heart of the Chaco forest.'
END;

UPDATE lugares SET
    nombre_en = CASE id
        WHEN 1  THEN 'Vuelta Fermoza Riverside Promenade'
        WHEN 2  THEN 'Laguna Oca Urban Reserve'
        WHEN 3  THEN 'Regional History Museum'
        WHEN 7  THEN 'Herradura Beach'
        WHEN 9  THEN 'Paraguay River Promenade'
        WHEN 10 THEN 'San Ignacio de Loyola Bridge'
        WHEN 12 THEN 'Río Pilcomayo National Park'
        WHEN 14 THEN 'National Grapefruit Festival'
        WHEN 16 THEN 'Bermejo River'
        WHEN 19 THEN 'Route 28 Viewpoint over the Wetland'
        ELSE NULL END,
    descripcion_en = CASE id
        WHEN 1  THEN 'Riverside walk along the Paraguay River for walking, cycling and watching the sunset.'
        WHEN 2  THEN 'Urban wetland with trails and a great variety of birds.'
        WHEN 3  THEN 'Museum in the historic center about the history of the city and the province.'
        WHEN 4  THEN 'River fish, surubí milanesa and chipá. (Fictional demo business)'
        WHEN 5  THEN 'Freshly made chipá, sopa paraguaya and mbeyú. (Fictional demo business)'
        WHEN 6  THEN 'Lake ideal for sport fishing and kayaking.'
        WHEN 7  THEN 'Sandy beach with shade, great for families in summer.'
        WHEN 8  THEN 'Grill and fish from the lake. (Fictional demo business)'
        WHEN 9  THEN 'Riverside walk with views of the Paraguayan shore.'
        WHEN 10 THEN 'International border crossing linking Clorinda with Paraguay.'
        WHEN 11 THEN 'Barbecue and border cuisine. (Fictional demo business)'
        WHEN 12 THEN 'Protected area with marshes, palm groves and wildlife.'
        WHEN 13 THEN 'Part of the park with boardwalks over the marsh to watch birds and caimans.'
        WHEN 14 THEN 'Popular festival with music, crafts and regional products.'
        WHEN 15 THEN 'Home cooking and regional sweets. (Fictional demo business)'
        WHEN 16 THEN 'Reddish-water river with good fishing for dorado and surubí.'
        WHEN 17 THEN 'Fried fish and river stews. (Fictional demo business)'
        WHEN 18 THEN 'Huge wetland with standing dead trees, birds and unique landscapes at sunset.'
        WHEN 19 THEN 'Causeway across the wetland: one of the best spots for photography and birdwatching.'
        WHEN 20 THEN 'Creole food and spit-roasted goat. (Fictional demo business)'
    END;

UPDATE actividades SET
    nombre_en = CASE id
        WHEN 1  THEN 'Sunset at Bañado La Estrella'
        WHEN 2  THEN 'Birdwatching at the Wetland'
        WHEN 3  THEN 'Sport Fishing at Laguna Herradura'
        WHEN 4  THEN 'Kayaking on Laguna Herradura'
        WHEN 5  THEN 'Birdwatching at Laguna Oca'
        WHEN 6  THEN 'Formosa Food Tour'
        WHEN 7  THEN 'Historic Downtown Walk'
        WHEN 8  THEN 'Hiking at Estero Poí'
        WHEN 9  THEN 'Kayaking on the Paraguay River'
        WHEN 10 THEN 'Fishing on the Bermejo River'
    END,
    descripcion_en = CASE id
        WHEN 1  THEN 'Guided trip to the wetland to watch the sunset among the submerged trees. Includes mate and chipá.'
        WHEN 2  THEN 'Walk along the Route 28 causeway, with more than 100 recorded bird species. Bring binoculars.'
        WHEN 3  THEN 'Boat trip with a guide, equipment included. Catch and release.'
        WHEN 4  THEN 'Double kayak ride with an instructor. Life jacket included.'
        WHEN 5  THEN 'Self-guided circuit along the trails of the urban reserve. Best early in the morning.'
        WHEN 6  THEN 'Tasting of chipá, sopa paraguaya, chipá guazú and mbeyú at three stops downtown.'
        WHEN 7  THEN 'Walk through the main square, the cathedral, the museum and the riverside promenade.'
        WHEN 8  THEN 'Trail and boardwalks inside Río Pilcomayo National Park.'
        WHEN 9  THEN 'Guided paddle along the Paraguay River with views of the other shore.'
        WHEN 10 THEN 'Full-day dorado fishing with a local guide and lunch on the riverbank.'
    END;

INSERT INTO info_localidad
(localidad_id, mejor_epoca, mejor_epoca_en, acceso, acceso_en, estado_ruta, estado_ruta_nota, estado_ruta_nota_en,
 ruta_actualizado, senal, senal_nota, senal_nota_en, combustible, combustible_en) VALUES
(1, 'Todo el año. De abril a septiembre hace menos calor.',
    'All year round. April to September is less hot.',
    'Por Ruta Nacional 11 desde Resistencia (≈ 170 km) o desde Clorinda. El aeropuerto tiene vuelos a Buenos Aires.',
    'Via National Route 11 from Resistencia (≈ 170 km) or from Clorinda. The airport has flights to Buenos Aires.',
    'bueno', 'Ruta asfaltada.', 'Paved road.', '2026-09-20',
    'buena', NULL, NULL,
    'Varias estaciones de servicio en la ciudad.', 'Several gas stations in the city.'),
(2, 'Todo el año para pescar. De diciembre a marzo para el balneario.',
    'Fishing all year round. December to March for the beach.',
    'A unos 45 km al sur de la capital: Ruta Nacional 11 y después el acceso señalizado a Herradura.',
    'About 45 km south of the capital: National Route 11, then the signposted road to Herradura.',
    'bueno', 'Después de lluvias fuertes, consultá el estado del acceso.', 'After heavy rain, check the access road.', '2026-09-20',
    'parcial', 'Hay señal en el pueblo; puede fallar cerca de la laguna.', 'There is signal in town; it may drop near the lake.',
    'Cargá combustible en Formosa capital antes de salir.', 'Fill up in Formosa city before you go.'),
(3, 'De abril a octubre.', 'April to October.',
    'Por Ruta Nacional 11, unos 115 km al norte de la capital. Puente internacional a Paraguay: llevá DNI o pasaporte.',
    'Via National Route 11, about 115 km north of the capital. International bridge to Paraguay: bring your ID or passport.',
    'bueno', 'Ruta asfaltada.', 'Paved road.', '2026-09-20',
    'buena', NULL, NULL,
    'Estaciones de servicio en la ciudad.', 'Gas stations in the city.'),
(4, 'De mayo a septiembre, la mejor época para ver fauna en el Parque Nacional.',
    'May to September, the best time to see wildlife in the National Park.',
    'Por Ruta Nacional 86 desde Clorinda (≈ 60 km). El Parque Nacional Río Pilcomayo está a pocos kilómetros del pueblo.',
    'Via National Route 86 from Clorinda (≈ 60 km). Río Pilcomayo National Park is a few kilometers from town.',
    'regular', 'Los caminos dentro del parque son de tierra: después de lluvias pueden cerrarse.',
    'Roads inside the park are dirt roads: they may close after rain.', '2026-09-20',
    'parcial', 'Buena en el pueblo; poca o nula dentro del parque.', 'Good in town; weak or none inside the park.',
    'Cargá en Clorinda o en el pueblo antes de entrar al parque.', 'Fill up in Clorinda or in town before entering the park.'),
(6, 'De abril a septiembre, cuando hace menos calor.', 'April to September, when it is less hot.',
    'Unos 150 km al oeste de la capital por rutas asfaltadas.',
    'About 150 km west of the capital on paved roads.',
    'bueno', NULL, NULL, '2026-09-20',
    'parcial', 'Hay señal en el pueblo; puede fallar sobre el río.', 'There is signal in town; it may drop on the river.',
    'Salí con el tanque lleno y cargá en las localidades grandes del camino.',
    'Leave with a full tank and fill up in the larger towns along the way.'),
(7, 'De mayo a septiembre: el Bañado tiene más agua y hace menos calor.',
    'May to September: the wetland has more water and it is less hot.',
    'Por Ruta Nacional 81, unos 300 km al oeste de la capital. Al Bañado La Estrella se llega por la Ruta Provincial 28, al norte de Las Lomitas.',
    'Via National Route 81, about 300 km west of the capital. Bañado La Estrella is reached via Provincial Route 28, north of Las Lomitas.',
    'regular', 'Algunos tramos de la Ruta 28 pueden ser de tierra o ripio: si llovió, consultá antes de ir.',
    'Some stretches of Route 28 may be dirt or gravel: if it has rained, check before going.', '2026-09-20',
    'nula', 'Hay señal en Las Lomitas, pero en el Bañado casi no hay. Avisá a alguien tu recorrido.',
    'There is signal in Las Lomitas, but almost none at the wetland. Tell someone your route.',
    'Cargá el tanque lleno en Las Lomitas: en el Bañado no hay estaciones de servicio.',
    'Fill your tank in Las Lomitas: there are no gas stations at the wetland.');

INSERT INTO eventos (localidad_id, nombre, nombre_en, tipo, descripcion, descripcion_en, lugar, fecha_desde, fecha_hasta, fecha_confirmada, gratis) VALUES
(1, 'Fiesta Nacional del Río', 'National River Festival', 'fiesta',
    'Espectáculos musicales, feria de artesanos y comidas típicas a orillas del río Paraguay.',
    'Live music, a crafts fair and traditional food on the banks of the Paraguay River.',
    'Costanera Vuelta Fermoza', '2026-11-13', '2026-11-15', 0, 1),
(1, 'Carnaval Formoseño', 'Formosa Carnival', 'carnaval',
    'Corsos con comparsas, murgas y carrozas. Traé espuma y ganas de bailar.',
    'Parades with dance troupes, street bands and floats. Bring foam spray and dancing shoes.',
    'Centro de la ciudad', '2027-02-06', '2027-02-09', 0, 1),
(3, 'Corsos de Clorinda', 'Clorinda Carnival Parades', 'carnaval',
    'Comparsas y música en los corsos de la ciudad de frontera.',
    'Dance troupes and music at the carnival parades of the border city.',
    NULL, '2027-02-13', '2027-02-14', 0, 1),
(1, 'Aniversario de la ciudad de Formosa', 'Formosa City Anniversary', 'cultural',
    'La ciudad celebra su fundación (8 de abril de 1879) con actos, música y actividades en la costanera.',
    'The city celebrates its founding (April 8, 1879) with ceremonies, music and activities on the riverside.',
    'Costanera Vuelta Fermoza', '2027-04-08', '2027-04-08', 1, 1),
(1, 'Noche de San Juan', 'St. John''s Night', 'cultural',
    'Tradición del litoral: fogatas, juegos con fuego y comidas típicas la noche del 23 de junio.',
    'A tradition of northeastern Argentina: bonfires, fire games and traditional food on the night of June 23.',
    NULL, '2027-06-23', '2027-06-23', 0, 1),
(1, 'Fiesta Patronal de la Virgen del Carmen', 'Patron Saint Festival of Our Lady of Mount Carmel', 'religioso',
    'Fiesta de la patrona de la ciudad: procesión, misa y actividades en la Catedral.',
    'Festival of the city''s patron saint: procession, mass and activities at the Cathedral.',
    'Catedral Nuestra Señora del Carmen', '2027-07-16', '2027-07-16', 1, 1),
(4, 'Fiesta Nacional del Pomelo', 'National Grapefruit Festival', 'fiesta',
    'Música en vivo, feria de productores, elección de la reina y todo lo que se hace con pomelo.',
    'Live music, a producers'' fair, the festival queen election and everything made with grapefruit.',
    NULL, '2027-08-20', '2027-08-22', 0, 1);

INSERT INTO actividades
(usuario_id, localidad_id, nombre, nombre_en, categoria, descripcion, descripcion_en, duracion_horas, precio_persona, edad_minima, lat, lng) VALUES
(NULL, 1, 'Paseo en piraguas por el río Paraguay', 'Canoe Trip on the Paraguay River', 'aventura',
 'Remada en piragua por el río Paraguay, con vista a la costanera y a la costa paraguaya. Apto para principiantes: te enseñan antes de salir. Consultá días, horarios y costo antes de ir.',
 'Canoe paddle on the Paraguay River, with views of the riverside promenade and the Paraguayan shore. Beginner-friendly: you get a short lesson before setting off. Check days, times and price before you go.',
 2.0, NULL, 10, -26.188000, -58.164000),
(NULL, 1, 'Feria turística, artesanal, regional y gastronómica', 'Tourism, Crafts, Regional and Food Fair', 'cultura',
 'Puestos de artesanos, productores regionales y comidas típicas formoseñas en un solo lugar. Ideal para comprar recuerdos y probar chipá, sopa paraguaya y dulces regionales. Entrada libre.',
 'Stalls with local artisans, regional producers and traditional Formosa food, all in one place. Great for buying souvenirs and trying chipá, sopa paraguaya and regional sweets. Free entry.',
 3.0, 0, 0, -26.184500, -58.174500),
(NULL, 1, 'Zumba en tu ciudad', 'Zumba in Your City', 'deporte',
 'Clases abiertas de zumba al aire libre en el Mástil Municipal. No hace falta inscribirse ni tener experiencia: llevá agua, ropa cómoda y sumate. Consultá los horarios en la Municipalidad.',
 'Open-air zumba classes at the Municipal Flagpole (Mástil Municipal). No sign-up or experience needed: bring water and comfortable clothes and join in. Check the schedule with the city council.',
 1.0, 0, 0, -26.185800, -58.166000);

UPDATE actividades SET estado = 'oculto' WHERE nombre LIKE 'Avistamiento de aves%';
UPDATE actividades SET estado = 'oculto' WHERE nombre = 'Circuito histórico por el centro';

INSERT INTO lugares (localidad_id, nombre, nombre_en, categoria, descripcion, descripcion_en, lat, lng, costo_persona, destacado) VALUES
(1, 'Pesca en la Costanera', 'Shore Fishing on the Riverside Promenade', 'pesca',
 'Tramos de la costanera sobre el río Paraguay donde se pesca desde la orilla: bogas, bagres y patíes. Fácil de llegar y bueno para ir en familia. Antes de ir, averiguá si hay veda y si necesitás permiso de pesca.',
 'Stretches of the riverside promenade on the Paraguay River where people fish from the shore: boga, catfish and patí. Easy to reach and good for families. Before you go, check for closed seasons and whether you need a fishing permit.',
 -26.190500, -58.162500, 0, 0),
(1, 'Zona del Puerto de Formosa', 'Port of Formosa Area', 'pesca',
 'Zona del puerto sobre el río Paraguay, conocida entre los pescadores de la ciudad por los dorados y surubíes, desde la costa o en embarcación. Averiguá si hay veda y si necesitás permiso de pesca.',
 'Port area on the Paraguay River, known among local anglers for dorado and surubí, from the shore or by boat. Check for closed seasons and whether you need a fishing permit.',
 -26.179500, -58.161200, 0, 0),
(1, 'Isla de Oro', NULL, 'pesca',
 'Isla sobre el río Paraguay frente a la ciudad, a la que se llega en lancha. Playas de arena para pasar el día y buena pesca en sus alrededores. Consultá el costo y los horarios del cruce.',
 'Island on the Paraguay River across from the city, reached by boat. Sandy beaches for a day out and good fishing around it. Check the price and times of the boat crossing.',
 -26.165000, -58.145000, NULL, 1);

UPDATE lugares SET imagen = 'uploads/images/isla-de-oro.jpg' WHERE nombre = 'Isla de Oro';
UPDATE lugares SET imagen = 'uploads/images/pesca-zona-puerto-formosa.jpg' WHERE nombre = 'Zona del Puerto de Formosa';
UPDATE lugares SET imagen = 'uploads/images/pesca-costanera-formosa.jpg' WHERE nombre = 'Pesca en la Costanera';
UPDATE lugares SET imagen = 'uploads/images/museo-historico-regional.jpg' WHERE nombre = 'Museo Histórico Regional';
UPDATE lugares SET imagen = 'uploads/images/banado-la-estrella.jpg' WHERE nombre = 'Bañado La Estrella';
UPDATE lugares SET imagen = 'uploads/images/costanera-rio-paraguay-clorinda.jpg' WHERE nombre = 'Costanera del Río Paraguay';
UPDATE lugares SET imagen = 'uploads/images/fiesta-nacional-pomelo.jpg' WHERE nombre = 'Fiesta Nacional del Pomelo';
UPDATE lugares SET imagen = 'uploads/images/laguna-herradura.jpg' WHERE nombre = 'Laguna Herradura';
UPDATE lugares SET imagen = 'uploads/images/parque-nacional-rio-pilcomayo.jpg' WHERE nombre = 'Parque Nacional Río Pilcomayo';
UPDATE lugares SET imagen = 'uploads/images/paseo-costanero-vuelta-fermoza.jpg' WHERE nombre = 'Paseo Costanero Vuelta Fermoza';
UPDATE lugares SET imagen = 'uploads/images/reserva-laguna-oca.jpg' WHERE nombre = 'Reserva Urbana Laguna Oca';

INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5)
SELECT 'actividad', id, 'uploads/images/feria-turistica-formosa.jpg', '561d2f09981e9009bc3dc9d483471f33'
FROM actividades WHERE nombre = 'Feria turística, artesanal, regional y gastronómica';

DELETE FROM lugares WHERE nombre IN ('Estero Poí', 'Mirador de la Ruta 28 sobre el Bañado',
    'Puente San Ignacio de Loyola', 'Comedor El Algarrobo', 'Comedor Los Pomelos', 'Chipería La Abuela',
    'Comedor Ribera del Bermejo', 'Comedor El Pescador', 'Parrillada La Frontera', 'Comedor Sabores del Litoral');
DELETE FROM actividades WHERE nombre = 'Senderismo en el Estero Poí';

UPDATE lugares SET imagen = 'uploads/images/rio-bermejo.jpg' WHERE id = 16;

INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5, orden)
SELECT 'actividad', id, 'uploads/images/zumba-en-tu-ciudad.jpg', '59fc8a4c88059876d56bea2341c518bc', 1 FROM actividades WHERE nombre = 'Zumba en tu ciudad';

UPDATE lugares SET imagen = 'uploads/images/balneario-herradura.jpg' WHERE nombre = 'Balneario de Herradura';

INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5, orden)
SELECT 'actividad', id, 'uploads/images/kayak-laguna-herradura.jpg', 'ae2d89ec225d8dadf3597ca72ae6b4f6', 1 FROM actividades WHERE nombre = 'Kayak en la laguna Herradura';

INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5, orden) VALUES ('actividad', 3, 'uploads/images/pesca-laguna-herradura.jpg', '311180404369925adb775c7ef695eef8', 1);

UPDATE actividades SET estado = 'oculto' WHERE id IN (1, 6, 9, 10);

INSERT INTO fotos (tipo_entidad, entidad_id, ruta, hash_md5, orden)
SELECT 'actividad', id, 'uploads/images/paseo-piraguas-rio-paraguay.jpg', '97d4aa0da959c167600f532cdfdb1330', 1 FROM actividades WHERE nombre LIKE 'Paseo en piraguas%' LIMIT 1;

INSERT INTO lugares (localidad_id, nombre, nombre_en, categoria, descripcion, descripcion_en, lat, lng, costo_persona, destacado)
SELECT 1, 'Reserva de Fauna Guaycolec', 'Guaycolec Wildlife Reserve', 'fauna',
 'Centro de conservación de fauna autóctona a pocos kilómetros de la capital, sobre la Ruta Nacional 11. Se pueden conocer de cerca animales del monte y los esteros formoseños que están en recuperación, y aprender sobre su cuidado. Ideal para ir en familia. Consultá días y horarios de visita antes de ir.',
 'Native wildlife conservation center a few kilometers from the capital, on National Route 11. You can see animals from Formosa''s forests and wetlands that are being cared for and learn about their protection. Great for families. Check visiting days and times before you go.',
 -26.050000, -58.200000, NULL, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM lugares WHERE nombre = 'Reserva de Fauna Guaycolec');

UPDATE lugares SET imagen = 'uploads/images/reserva-guaycolec.jpg' WHERE nombre LIKE '%Guaycolec%';

INSERT INTO lugares (localidad_id, nombre, nombre_en, categoria, descripcion, descripcion_en, lat, lng, costo_persona, destacado)
SELECT 1, 'Balneario Parque Arena', 'Parque Arena Beach', 'playa',
 'Balneario con playa de arena en Formosa Capital, para refrescarse y pasar el día en familia en la temporada de verano. Llevá protector solar, agua y respetá las indicaciones de los guardavidas. Consultá días, horarios y condiciones antes de ir.',
 'Sandy beach in Formosa city, a place to cool off and spend the day with family during the summer season. Bring sunscreen and water, and follow the lifeguards'' instructions. Check days, opening times and conditions before you go.',
 -26.193000, -58.160000, NULL, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM lugares WHERE nombre = 'Balneario Parque Arena');

UPDATE lugares SET imagen = 'uploads/images/balneario-parque-arena.jpg' WHERE nombre = 'Balneario Parque Arena';

INSERT INTO lugares (localidad_id, nombre, nombre_en, categoria, descripcion, descripcion_en, lat, lng, costo_persona, destacado)
SELECT * FROM (
    SELECT 1 AS localidad_id, 'Mostaza' AS nombre, NULL AS nombre_en, 'gastronomia' AS categoria,
     'Cadena argentina de hamburguesas: menú rápido y económico, ideal para comer algo sin demoras. Consultá la dirección y los horarios del local.' AS descripcion,
     'Argentine burger chain: quick, affordable menu, great for a fast meal. Check the address and opening times of the restaurant.' AS descripcion_en,
     -26.184000 AS lat, -58.174500 AS lng, NULL AS costo_persona, 0 AS destacado
    UNION ALL SELECT 1, 'Puerto de Palo', NULL, 'gastronomia',
     'Lugar para comer en Formosa Capital recomendado por los formoseños. Consultá el menú, los horarios y la dirección antes de ir.',
     'A place to eat in Formosa city, recommended by locals. Check the menu, opening times and address before you go.',
     -26.186500, -58.168000, NULL, 0
    UNION ALL SELECT 1, 'Alma Verde', NULL, 'gastronomia',
     'Lugar para comer en Formosa Capital recomendado por los formoseños. Consultá el menú, los horarios y la dirección antes de ir.',
     'A place to eat in Formosa city, recommended by locals. Check the menu, opening times and address before you go.',
     -26.182000, -58.176500, NULL, 0
    UNION ALL SELECT 1, 'Tatane', NULL, 'gastronomia',
     'Lugar para comer en Formosa Capital recomendado por los formoseños. Consultá el menú, los horarios y la dirección antes de ir.',
     'A place to eat in Formosa city, recommended by locals. Check the menu, opening times and address before you go.',
     -26.180000, -58.172000, NULL, 0
) AS nuevos
WHERE NOT EXISTS (SELECT 1 FROM lugares g WHERE g.nombre = nuevos.nombre AND g.localidad_id = 1);

UPDATE lugares SET imagen = 'uploads/images/alma-verde.jpg' WHERE nombre = 'Alma Verde';

UPDATE lugares SET imagen = 'uploads/images/puerto-de-palo.jpg' WHERE nombre = 'Puerto de Palo';

UPDATE lugares SET imagen = 'uploads/images/mostaza.jpg' WHERE nombre = 'Mostaza';

UPDATE lugares SET imagen = 'uploads/images/tatane.jpg' WHERE nombre = 'Tatane';

UPDATE lugares SET
    nombre = 'Tatané',
    descripcion = 'Cervecería artesanal en Formosa Capital: cervezas propias en un local con estilo de campo, para ir con amigos. Consultá el menú, los horarios y la dirección antes de ir.',
    descripcion_en = 'Craft brewery in Formosa city: house-made beers in a countryside-style venue, great with friends. Check the menu, opening times and address before you go.'
WHERE nombre = 'Tatane' AND localidad_id = 1;

UPDATE localidades SET es_destino = 1, imagen = 'uploads/images/pirane.jpg' WHERE id = 5;

INSERT INTO info_localidad
(localidad_id, mejor_epoca, mejor_epoca_en, acceso, acceso_en, estado_ruta, estado_ruta_nota, estado_ruta_nota_en,
 ruta_actualizado, senal, senal_nota, senal_nota_en, combustible, combustible_en)
SELECT 5, 'De abril a septiembre, cuando hace menos calor.', 'April to September, when it is less hot.',
    'Por Ruta Nacional 81, unos 115 km al noroeste de la capital. Es una parada cómoda en el camino hacia Las Lomitas y el Bañado La Estrella.',
    'Via National Route 81, about 115 km northwest of the capital. A convenient stop on the way to Las Lomitas and Bañado La Estrella.',
    'bueno', 'Ruta asfaltada.', 'Paved road.', '2026-09-25',
    'buena', NULL, NULL,
    'Estaciones de servicio en la ciudad, sobre la ruta.', 'Gas stations in town, along the highway.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM info_localidad WHERE localidad_id = 5);
