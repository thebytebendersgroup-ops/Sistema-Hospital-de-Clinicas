-- ============================================================
-- SIGSM - Script DML (datos de prueba, todos ficticios)
-- Ejecutar DESPUÉS de 01_ddl.sql.
--
-- Credenciales de prueba:
--   Administrador: admin@hc.edu.uy         / 123456
--   Pacientes:     ana.perez@correo.uy      / paciente123
--                  luis.gomez@correo.uy     / paciente123
--                  maria.silva@correo.uy    / paciente123
--                  jorge.rios@correo.uy     / paciente123
-- ============================================================

USE sigsm_prueba;

INSERT INTO usuario (nombre, email, contrasenia, rol) VALUES
('Administrador SIGSM', 'admin@hc.edu.uy',       '$2y$10$sL.gQ5kz2cezW5ZStiFSSe9twG/JTyj/1tCnFUVuRvenF45hnjAEy', 'admin'),
('Ana Pérez',           'ana.perez@correo.uy',   '$2y$10$xqWcyRoB5fXb26Q0gxjUd.8tGiZFDPfm3LDgEVcfar9FzMbJcH3Je', 'paciente'),
('Luis Gómez',          'luis.gomez@correo.uy',  '$2y$10$5yV8BxwQ/8UTvenl/DuKmuLsUqYZW/5LtyUekK7gWmrRHFSYZUTei', 'paciente'),
('María Silva',         'maria.silva@correo.uy', '$2y$10$KSSCk4I3rvmLGL4ic4dSr.n4FzyA6cgjj3AtJizOdnNQlyOSnPgcy', 'paciente'),
('Jorge Ríos',          'jorge.rios@correo.uy',  '$2y$10$HcWzVUXQu/XQgaCHIJ3DmOp6VuSlmz.Cj0vQq2mH3YVsiQD5CGaYO', 'paciente');

-- Pacientes (usuarios 2 a 5)
INSERT INTO paciente (ci, telefono, id_usuario) VALUES
('45678901', '099111222', 2),
('51234567', '098333444', 3),
('38765432', '091555666', 4),
('49876543', NULL,        5);

INSERT INTO categoria (nombre) VALUES
('Cardiología'),
('Gastroenterología'),
('Nefrología'),
('Medicina Nuclear'),
('Imagenología'),
('Ginecología'),
('Urología'),
('Enfermería');

-- Documentos (los archivos adjuntos se suben desde el sistema)
INSERT INTO documento (titulo, descripcion, token, fecha_emision, id_categoria, id_paciente) VALUES
('Ecocardiograma con dobutamina',   'Indicaciones previas al estudio.',            'efc1b560dd63cb96609017371b4f4df3', '2026-09-01', 1, 1),
('Tratamiento con warfarina',       'Pauta de alimentación y controles.',          'de110a9bf18b090df138180d5f487ad0', '2026-09-03', 1, 1),
('Pauta para ostomizados',          'Cuidados diarios de la ostomía.',             'd07eaf6398e06b7d9f0b5a875d102019', '2026-09-05', 2, 2),
('Preparación para estudios',       'Ayuno y medicación antes del estudio.',       'ab37e8883a977e6b3e0f8829947a7e40', '2026-09-08', 5, 2),
('Centellograma miocárdico',        'Información sobre el estudio.',               '7fbfa9981d1fb406650ef9102b7a16f6', '2026-09-10', 4, 3),
('Ingreso a nefrología',            'Documentación para el ingreso.',              '0e50fe6554dda04f974912291bbf07ef', '2026-09-12', 3, 3),
('Alta de enfermería - nefrología', 'Recomendaciones al alta.',                    'aeb73b869130c37fba6a11f2462a3d3a', '2026-09-15', 8, 3),
('Control ginecológico',            'Resultados e indicaciones.',                  '7479148db0b8c3cbdfd770cfc82396c0', '2026-09-18', 6, 4),
('Prostatectomía radical',          'Cuidados antes y después de la cirugía.',     'b4c40af058b7e2f0c53c0c61fa892151', '2026-09-20', 7, 4),
('Prevención de infecciones',       'Recomendaciones generales de higiene.',       '16b244e8ce7995b1d0cbbebb2b60a3f9', '2026-09-22', 8, 1);

INSERT INTO encuestas_satisfaccion (calificacion, comentarios) VALUES
(5, 'Muy buena atención.'),
(4, 'Todo bien, la espera fue larga.'),
(5, NULL),
(3, 'Podría mejorar la señalización.');
