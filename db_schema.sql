-- Esquema de la base de datos de la agenda.
-- Si el hosting ya creó la base, se puede omitir la línea CREATE DATABASE
-- y ejecutar solo la tabla.

CREATE DATABASE IF NOT EXISTS agenda_contactos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE agenda_contactos;

CREATE TABLE IF NOT EXISTS contactos (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre    VARCHAR(80)  NOT NULL,
    apellido  VARCHAR(80)  NOT NULL,
    telefono  VARCHAR(20)  NOT NULL,
    foto      VARCHAR(255) NOT NULL,
    email     VARCHAR(120) NULL DEFAULT NULL,
    direccion VARCHAR(255) NULL DEFAULT NULL,
    notas     TEXT         NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_nombre_apellido (nombre, apellido),
    INDEX idx_telefono (telefono)
);