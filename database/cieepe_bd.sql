-- Estructura de la Base de Datos CIEEPE
CREATE DATABASE IF NOT EXISTS cieepe_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cieepe_bd;

-- 1. Tabla: lineas_investigacion
CREATE TABLE IF NOT EXISTS lineas_investigacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(100) DEFAULT 'book', -- Clase de icono Lucide
    url_saber_mas VARCHAR(255) DEFAULT '#',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla: proyectos
CREATE TABLE IF NOT EXISTS proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    estado VARCHAR(50) DEFAULT 'En Puerta', -- 'En Puerta', 'En Curso', 'Terminados'
    descripcion_corta TEXT,
    imagen_portada VARCHAR(255),
    responsable VARCHAR(255),
    internos TEXT,
    externos TEXT,
    anio_inicio VARCHAR(10),
    url_ficha VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla: investigadores
CREATE TABLE IF NOT EXISTS investigadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    especialidad VARCHAR(255),
    cargo_o_grado VARCHAR(255),
    etiqueta_badge VARCHAR(100) DEFAULT 'Investigador',
    email VARCHAR(255),
    telefono VARCHAR(50),
    ubicacion VARCHAR(255) DEFAULT 'ENEES',
    linkedin_url VARCHAR(255) DEFAULT '#',
    orcid_url VARCHAR(255) DEFAULT '#',
    scholar_url VARCHAR(255) DEFAULT '#',
    cv_url VARCHAR(255) DEFAULT '#',
    semblanza TEXT,
    imagen_perfil VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabla: investigador_lineas
CREATE TABLE IF NOT EXISTS investigador_lineas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investigador_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    orden INT DEFAULT 0,
    FOREIGN KEY (investigador_id) REFERENCES investigadores(id) ON DELETE CASCADE
);

-- Datos iniciales de prueba (Opcional)
-- INSERT INTO lineas_investigacion (titulo, descripcion, icono) VALUES 
-- ('Educación Inclusiva', 'Estrategias y prácticas para la inclusión educativa', 'users');
