USE turismo_fermosa;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS eventos;
DROP TABLE IF EXISTS info_localidad;
DROP TABLE IF EXISTS intentos_login;
DROP TABLE IF EXISTS reportes;
DROP TABLE IF EXISTS resenas;
DROP TABLE IF EXISTS consultas;
DROP TABLE IF EXISTS fotos;
DROP TABLE IF EXISTS actividades;
DROP TABLE IF EXISTS alojamientos;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS lugares;
DROP TABLE IF EXISTS localidades;
DROP TABLE IF EXISTS parametros;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE localidades (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    slug         VARCHAR(100) NOT NULL UNIQUE,
    descripcion  TEXT,
    descripcion_en TEXT NULL,
    lat          DECIMAL(9,6) NOT NULL,
    lng          DECIMAL(9,6) NOT NULL,
    imagen       VARCHAR(255) NULL,
    es_destino   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lugares (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    localidad_id    INT UNSIGNED NOT NULL,
    nombre          VARCHAR(150) NOT NULL,
    nombre_en       VARCHAR(150) NULL,
    categoria       ENUM('naturaleza','playa','pesca','fauna','camping',
                         'gastronomia','cultura','aventura','deporte') NOT NULL,
    descripcion     TEXT,
    descripcion_en  TEXT NULL,
    lat             DECIMAL(9,6) NOT NULL,
    lng             DECIMAL(9,6) NOT NULL,
    costo_persona   DECIMAL(10,2) NULL DEFAULT 0,
    imagen          VARCHAR(255) NULL,
    destacado       TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_lugares_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id),
    INDEX idx_lugares_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre               VARCHAR(80)  NOT NULL,
    apellido             VARCHAR(80)  NOT NULL,
    email                VARCHAR(150) NOT NULL UNIQUE,
    password_hash        VARCHAR(255) NOT NULL,
    telefono             VARCHAR(30)  NULL,
    dni                  VARCHAR(10)  NULL UNIQUE,
    cuit                 VARCHAR(11)  NULL UNIQUE,
    email_verificado     TINYINT(1)   NOT NULL DEFAULT 0,
    codigo_verificacion  CHAR(6)      NULL,
    codigo_expira        DATETIME     NULL,
    estado               ENUM('activo','suspendido') NOT NULL DEFAULT 'activo',
    creado_en            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alojamientos (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id         INT UNSIGNED NOT NULL,
    localidad_id       INT UNSIGNED NOT NULL,
    tipo               ENUM('cabana','hotel','camping','casa','departamento') NOT NULL,
    nombre             VARCHAR(150) NOT NULL,
    descripcion        TEXT,
    precio             DECIMAL(10,2) NOT NULL,
    modalidad_precio   ENUM('por_noche','por_persona') NOT NULL DEFAULT 'por_noche',
    capacidad          TINYINT UNSIGNED NOT NULL,
    habitaciones       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    pileta             TINYINT(1) NOT NULL DEFAULT 0,
    parrilla           TINYINT(1) NOT NULL DEFAULT 0,
    wifi               TINYINT(1) NOT NULL DEFAULT 0,
    aire               TINYINT(1) NOT NULL DEFAULT 0,
    mascotas           TINYINT(1) NOT NULL DEFAULT 0,
    estacionamiento    TINYINT(1) NOT NULL DEFAULT 0,
    direccion          VARCHAR(200) NULL,
    lat                DECIMAL(9,6) NOT NULL,
    lng                DECIMAL(9,6) NOT NULL,
    telefono_contacto  VARCHAR(30) NULL,
    estado             ENUM('activo','oculto','revision') NOT NULL DEFAULT 'activo',
    motivo_revision    VARCHAR(255) NULL,
    creado_en          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_aloj_usuario   FOREIGN KEY (usuario_id)   REFERENCES usuarios(id),
    CONSTRAINT fk_aloj_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id),
    INDEX idx_aloj_busqueda (estado, localidad_id, capacidad, precio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE actividades (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id       INT UNSIGNED NULL,
    localidad_id     INT UNSIGNED NOT NULL,
    nombre           VARCHAR(150) NOT NULL,
    nombre_en        VARCHAR(150) NULL,
    categoria        ENUM('naturaleza','playa','pesca','fauna','camping',
                          'gastronomia','cultura','aventura','deporte') NOT NULL,
    descripcion      TEXT,
    descripcion_en   TEXT NULL,
    duracion_horas   DECIMAL(4,1) NOT NULL,
    precio_persona   DECIMAL(10,2) NULL DEFAULT 0,
    edad_minima      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    lat              DECIMAL(9,6) NOT NULL,
    lng              DECIMAL(9,6) NOT NULL,
    estado           ENUM('activo','oculto','revision') NOT NULL DEFAULT 'activo',
    motivo_revision  VARCHAR(255) NULL,
    creado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_act_usuario   FOREIGN KEY (usuario_id)   REFERENCES usuarios(id),
    CONSTRAINT fk_act_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id),
    INDEX idx_act_categoria (estado, categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fotos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_entidad  ENUM('alojamiento','actividad','lugar') NOT NULL,
    entidad_id    INT UNSIGNED NOT NULL,
    ruta          VARCHAR(255) NOT NULL,
    hash_md5      CHAR(32) NOT NULL,
    orden         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fotos_entidad (tipo_entidad, entidad_id),
    INDEX idx_fotos_hash (hash_md5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE consultas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alojamiento_id  INT UNSIGNED NOT NULL,
    nombre          VARCHAR(120) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    telefono        VARCHAR(30)  NULL,
    fecha_desde     DATE NOT NULL,
    fecha_hasta     DATE NOT NULL,
    personas        TINYINT UNSIGNED NOT NULL,
    mensaje         TEXT NULL,
    estado          ENUM('nueva','respondida','cerrada') NOT NULL DEFAULT 'nueva',
    token_resena    CHAR(32) NOT NULL UNIQUE,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consulta_aloj FOREIGN KEY (alojamiento_id) REFERENCES alojamientos(id),
    INDEX idx_consulta_estado (alojamiento_id, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resenas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consulta_id     INT UNSIGNED NOT NULL UNIQUE,
    alojamiento_id  INT UNSIGNED NOT NULL,
    puntuacion      TINYINT UNSIGNED NOT NULL,
    comentario      TEXT NULL,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resena_consulta FOREIGN KEY (consulta_id)    REFERENCES consultas(id),
    CONSTRAINT fk_resena_aloj     FOREIGN KEY (alojamiento_id) REFERENCES alojamientos(id),
    CONSTRAINT chk_puntuacion CHECK (puntuacion BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reportes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_entidad  ENUM('alojamiento','actividad') NOT NULL,
    entidad_id    INT UNSIGNED NOT NULL,
    motivo        ENUM('fraude','datos_falsos','fotos_falsas','no_existe',
                       'precio_enganoso','otro') NOT NULL,
    detalle       TEXT NULL,
    ip_hash       CHAR(64) NOT NULL,
    creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reporte_ip (tipo_entidad, entidad_id, ip_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE intentos_login (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150) NOT NULL,
    ip_hash    CHAR(64) NOT NULL,
    exitoso    TINYINT(1) NOT NULL DEFAULT 0,
    creado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_intentos (email, creado_en),
    INDEX idx_intentos_ip (ip_hash, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE info_localidad (
    localidad_id       INT UNSIGNED PRIMARY KEY,
    mejor_epoca        VARCHAR(255) NOT NULL,
    mejor_epoca_en     VARCHAR(255) NULL,
    acceso             TEXT NOT NULL,
    acceso_en          TEXT NULL,
    estado_ruta        ENUM('bueno','regular','malo') NOT NULL DEFAULT 'bueno',
    estado_ruta_nota   VARCHAR(255) NULL,
    estado_ruta_nota_en VARCHAR(255) NULL,
    ruta_actualizado   DATE NOT NULL,
    senal              ENUM('buena','parcial','nula') NOT NULL,
    senal_nota         VARCHAR(255) NULL,
    senal_nota_en      VARCHAR(255) NULL,
    combustible        VARCHAR(255) NOT NULL,
    combustible_en     VARCHAR(255) NULL,
    CONSTRAINT fk_info_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE eventos (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    localidad_id      INT UNSIGNED NOT NULL,
    nombre            VARCHAR(150) NOT NULL,
    nombre_en         VARCHAR(150) NULL,
    tipo              ENUM('fiesta','carnaval','religioso','cultural') NOT NULL,
    descripcion       TEXT NOT NULL,
    descripcion_en    TEXT NULL,
    lugar             VARCHAR(150) NULL,
    fecha_desde       DATE NOT NULL,
    fecha_hasta       DATE NOT NULL,
    fecha_confirmada  TINYINT(1) NOT NULL DEFAULT 0,
    gratis            TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_evento_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id),
    INDEX idx_eventos_fecha (fecha_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE parametros (
    clave        VARCHAR(60) PRIMARY KEY,
    valor        DECIMAL(12,2) NOT NULL,
    descripcion  VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
