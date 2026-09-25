USE turismo_fermosa;
SET NAMES utf8mb4;

ALTER TABLE lugares MODIFY categoria
    ENUM('naturaleza','playa','pesca','fauna','camping','gastronomia','cultura','aventura','deporte') NOT NULL;

ALTER TABLE actividades MODIFY categoria
    ENUM('naturaleza','playa','pesca','fauna','camping','gastronomia','cultura','aventura','deporte') NOT NULL;

ALTER TABLE actividades MODIFY precio_persona DECIMAL(10,2) NULL DEFAULT 0;

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
ALTER TABLE lugares MODIFY costo_persona DECIMAL(10,2) NULL DEFAULT 0;

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

INSERT IGNORE INTO parametros (clave, valor, descripcion) VALUES
('pasaje_colectivo_km',        70.00, 'Pasaje de colectivo estimado: pesos por km, por persona y por tramo'),
('pasaje_colectivo_minimo',  5000.00, 'Pasaje de colectivo mínimo por persona y por tramo (ARS)');
