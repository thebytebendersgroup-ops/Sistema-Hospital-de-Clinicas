-- ============================================================
-- SIGSM - Script DDL (creación de la base y las tablas)
-- Ejecutar en phpMyAdmin > pestaña SQL.
-- Esta variante crea una base NUEVA llamada "sigsm_prueba" y NO toca tu base "sigsm".
-- Si "sigsm_prueba" ya existe, el DROP la borra y se crea de cero.
-- ============================================================

DROP DATABASE IF EXISTS sigsm_prueba;
CREATE DATABASE sigsm_prueba CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigsm_prueba;

-- ------------------------------------------------------------
-- USUARIO: todas las personas que entran al sistema.
-- rol = 'admin' (administrador) o 'paciente' (usuario básico)
-- La contraseña se guarda con password_hash() (Bcrypt), nunca en texto plano.
-- ------------------------------------------------------------
CREATE TABLE usuario (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80)  NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    contrasenia VARCHAR(255) NOT NULL,
    rol         ENUM('admin','paciente') NOT NULL DEFAULT 'paciente',
    activo      TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PACIENTE: datos propios del paciente. Cada paciente es un usuario
-- (relación 1 a 1), por eso el nombre se guarda solo en USUARIO.
-- ------------------------------------------------------------
CREATE TABLE paciente (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ci         VARCHAR(8)  NOT NULL UNIQUE,   -- cédula sin puntos ni guión
    telefono   VARCHAR(20) NULL,
    id_usuario INT NOT NULL UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CATEGORIA: sirve para organizar los documentos (por especialidad).
-- ------------------------------------------------------------
CREATE TABLE categoria (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DOCUMENTO: cada documento pertenece a UNA categoría y a UN paciente.
-- token: código al azar que va dentro del QR (así nadie adivina otros documentos).
-- activo: baja lógica (0 = eliminado, el registro queda en la base).
-- ------------------------------------------------------------
CREATE TABLE documento (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    titulo        VARCHAR(120) NOT NULL,
    descripcion   TEXT NULL,
    archivo       VARCHAR(100) NULL,          -- nombre del archivo subido (carpeta uploads/)
    token         CHAR(32) NOT NULL UNIQUE,
    fecha_emision DATE NOT NULL,
    activo        TINYINT(1) NOT NULL DEFAULT 1,
    id_categoria  INT NOT NULL,
    id_paciente   INT NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id),
    FOREIGN KEY (id_paciente)  REFERENCES paciente(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ENCUESTAS_SATISFACCION: encuesta de satisfacción ANÓNIMA.
-- No tiene ninguna clave foránea: no se sabe quién la respondió.
-- ------------------------------------------------------------
CREATE TABLE encuestas_satisfaccion (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    calificacion TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentarios  VARCHAR(300) NULL,
    fecha        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
