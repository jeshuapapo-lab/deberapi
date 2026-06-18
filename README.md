# GeoClima Multi-API

Proyecto con PHP, MySQL, HTML, CSS y JavaScript. Incluye registro, inicio de sesión, cierre de sesión y un panel protegido con tres APIs.

## APIs

1. Open-Meteo: clima.
2. REST Countries: datos del país.
3. Sunrise-Sunset: amanecer y atardecer.

## Instalación

1. Copia el proyecto en `C:/xampp/htdocs/deberapi`.
2. Inicia Apache y MySQL en XAMPP.
3. Entra a phpMyAdmin e importa `database.sql`.
4. Abre `http://localhost/deberapi/`.
5. Registra un usuario e inicia sesión.

## Archivos

- `index.php`: acceso.
- `registro.php`: registro.
- `login.php`: valida credenciales.
- `logout.php`: cierra la sesión.
- `dashboard.php`: panel y APIs.
- `conexion.php`: conexión MySQL.
- `database.sql`: base de datos.
- `js/app.js`: consumo de APIs.

Las contraseñas se guardan con `password_hash` y se verifican con `password_verify`.

## Autor

Jeshua Sánchez
