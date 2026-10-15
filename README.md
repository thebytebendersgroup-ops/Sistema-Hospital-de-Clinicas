# SIGSM — Sistema Informático de Gestión de Servicios Médicos

Módulo **Documentación** del Hospital de Clínicas: el personal administrativo carga documentos médicos,
los organiza por categoría y cada paciente puede abrir los suyos desde el celular con un código QR.

Hecho con **PHP + MySQL + HTML/CSS/JavaScript (Bootstrap 5)**, para correr en **XAMPP**.

---

## 1. Qué hace el sistema

| Quién | Qué puede hacer |
|---|---|
| **Administrador** | Entrar al panel, ver la lista de documentos, buscar y filtrar por categoría, crear, editar y eliminar documentos (con archivo PDF/JPG/PNG), ver el QR de cada documento y ver los resultados de la encuesta de satisfacción. |
| **Paciente** | Entrar a su portal, ver **solo sus** documentos, abrirlos y ver el QR de cada uno. |
| **Cualquier persona** | Abrir un documento escaneando su QR (vista pensada para celular) y responder la encuesta de satisfacción, que es **anónima**. |

## 2. Requisitos

- XAMPP con **Apache 2.4**, **PHP 8.0 o superior** y **MySQL/MariaDB**
- Un navegador actualizado
- Conexión a internet (Bootstrap y el generador de QR se cargan desde internet)
- Recomendado para programar: Visual Studio Code con las extensiones *PHP Intelephense* y *MySQL*

## 3. Instalación paso a paso

1. **Copiar la carpeta.** Copiar esta carpeta completa a `C:\xampp\htdocs\` y dejarle el nombre `sigsm`
   (debe quedar `C:\xampp\htdocs\sigsm\index.php`).
2. **Encender XAMPP.** Abrir el *XAMPP Control Panel* y hacer *Start* en **Apache** y en **MySQL**.
3. **Crear la base de datos.** Abrir <http://localhost/phpmyadmin>, ir a la pestaña **SQL** y ejecutar, en este orden:
   1. el contenido de `sql/01_ddl.sql` (crea la base `sigsm_prueba` y las tablas; no toca otras bases que tengas)
   2. el contenido de `sql/02_dml.sql` (carga datos de prueba)
4. **Revisar la conexión.** En `datos/conexion.php` están el usuario y la clave de MySQL.
   Vienen los valores por defecto de XAMPP (usuario `root`, sin clave). Si en tu PC son otros, cambialos ahí.
5. **Abrir el sistema:** <http://localhost/sigsm/>

## 4. Usuarios de prueba

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | `admin@hc.edu.uy` | `123456` |
| Paciente | `ana.perez@correo.uy` | `paciente123` |
| Paciente | `luis.gomez@correo.uy` | `paciente123` |
| Paciente | `maria.silva@correo.uy` | `paciente123` |
| Paciente | `jorge.rios@correo.uy` | `paciente123` |

Todos los datos son ficticios. Las contraseñas están guardadas con Bcrypt (`password_hash`), nunca en texto plano.

## 5. Probar el QR con el celular

El QR lleva la dirección del sistema, y el celular **no puede abrir `localhost`** (para el celular, "localhost" es el propio celular).

1. Conectar la PC y el celular a la **misma red wifi**.
2. En la PC, abrir una consola y ejecutar `ipconfig`. Anotar la *Dirección IPv4* (por ejemplo `192.168.1.20`).
3. Abrir `config/config.php` y cambiar `URL_BASE` por `http://192.168.1.20/sigsm` (con tu IP).
4. Si no abre, revisar que el firewall de Windows permita Apache en redes privadas.

## 6. Estructura del proyecto (tres capas)

```
sigsm/
├── index.php                 Entrada: manda al login
├── config/
│   └── config.php            URL del sistema (para el QR), carpeta y tamaño de archivos
├── presentacion/             CAPA DE PRESENTACIÓN: lo que ve el usuario (HTML, CSS, JS)
│   ├── login.php             Formulario de inicio de sesión
│   ├── index.php             Panel del administrador (documentos, encuestas)
│   ├── portal-paciente.php   Portal del paciente
│   ├── ver_documento_mobile.php   Vista del documento que se abre con el QR
│   ├── encuesta.php          Encuesta de satisfacción (anónima)
│   ├── js/script.js          JavaScript del portal del paciente
│   └── prototipo/            Prototipo visual del módulo Ambulancias (solo HTML, sin lógica)
├── negocio/                  CAPA DE NEGOCIO: lógica, validaciones y control de acceso
│   ├── sesion.php            Sesiones, control de acceso por rol y funciones de ayuda
│   ├── validaciones.php      Validaciones de los formularios (en PHP)
│   ├── procesarLogin.php     Inicio de sesión
│   ├── logout.php            Cierre de sesión
│   ├── guardar_documento.php Alta y edición de documentos
│   ├── eliminar_documento.php  Baja lógica de documentos
│   └── guardar_encuesta.php  Guarda una respuesta de la encuesta
├── datos/                    CAPA DE DATOS: conexión y consultas a la base
│   ├── conexion.php          Clase Conexion (PDO)
│   ├── Usuario.php           Clase Usuario (login)
│   ├── Documento.php         Clase Documento (alta, edición, baja, listados)
│   └── Encuesta.php          Clase Encuesta (guardar y resumen de resultados)
├── sql/
│   ├── 01_ddl.sql            Creación de la base y las tablas
│   └── 02_dml.sql            Datos de prueba
└── uploads/                  Archivos subidos (no se puede ejecutar PHP acá)
```

Regla de las capas: la **presentación** no hace consultas SQL, el **negocio** valida y decide, y solo la **capa de datos** habla con MySQL.

## 7. Seguridad implementada

- **Contraseñas** con `password_hash()` (Bcrypt) y `password_verify()`.
- **Consultas preparadas (PDO)** en todo el sistema: ningún dato del usuario se pega directo en el SQL.
- **Sesiones** con `session_start()`, `session_regenerate_id()` al iniciar sesión y cierre que destruye la sesión y la cookie.
- **Control de acceso por rol:** cada página protegida usa `exigirRol()`. Un paciente no entra al panel de administración y viceversa.
- **Validación doble:** en JavaScript (antes de enviar) y en PHP (siempre, del lado del servidor).
- **El QR no usa el número del documento** sino un código al azar de 32 caracteres, así nadie puede ver documentos ajenos probando números. La cédula se muestra parcialmente tapada.
- **Archivos subidos:** solo PDF, JPG o PNG de hasta 2 MB; se controla la extensión y el contenido real; se guardan con nombre al azar.
- **XSS:** todo texto que viene de la base se escapa con `e()` antes de mostrarse.
- **Baja lógica:** eliminar un documento lo marca como inactivo (`activo = 0`), no borra el registro.
- **Encuesta anónima:** la tabla no tiene ninguna relación con usuarios, pacientes ni documentos.

## 8. Estado de la entrega

Hecho:
- Login, logout, sesiones y dos roles (administrador y paciente)
- CRUD de documentos con categorías, filtros, archivo adjunto y confirmación al eliminar
- QR por documento y vista pensada para celular
- Encuesta anónima con resultados en el panel
- Código en tres capas, con clases PHP (`Conexion`, `Usuario`, `Documento`, `Encuesta`) y comentarios

Pendiente / fuera de esta entrega:
- Módulo **Ambulancias** (opcional): solo existe el prototipo visual en `presentacion/prototipo/`
- Usuarios de MySQL con permisos distintos y vistas SQL (los define la parte de Base de Datos)

## 9. Convención de commits

Mensajes cortos, en español, con un prefijo que dice qué tipo de cambio es:

| Prefijo | Para qué | Ejemplo |
|---|---|---|
| `feat:` | funcionalidad nueva | `feat: alta de documentos con archivo adjunto` |
| `fix:` | arreglo de un error | `fix: el login redirigía a una página que no existe` |
| `docs:` | documentación | `docs: README con pasos de instalación` |
| `refactor:` | ordenar código sin cambiar lo que hace | `refactor: separar el código en tres capas` |

## 10. Problemas comunes

- **"No se pudo conectar con la base de datos"** → MySQL no está encendido en XAMPP, o el usuario/clave de `datos/conexion.php` no coinciden con los de tu MySQL.
- **Pantalla en blanco o error 500** → mirar `C:\xampp\apache\logs\error.log`.
- **No se puede subir un archivo** → debe ser PDF, JPG o PNG de hasta 2 MB. Si el archivo es válido y falla, revisar que la carpeta `uploads/` permita escritura.
- **El QR no abre en el celular** → ver la sección 5.
