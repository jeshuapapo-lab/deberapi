<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoClima Multi-API</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth.css">
    <script src="js/app.js" defer></script>
</head>
<body>
<header class="header">
    <div class="header-container dashboard-header">
        <span class="header-logo">⛅ GeoClima Multi-API</span>
        <div class="user-area">
            <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            <a href="logout.php" class="logout-button">Cerrar sesión</a>
        </div>
    </div>
</header>

<main class="main-content">
    <section class="search-section">
        <h1 class="main-title">Clima e información de ciudades</h1>
        <p class="subtitle">Consulta clima, datos del país y horarios solares mediante tres APIs públicas.</p>
        <form id="formulario-clima" class="search-form">
            <div class="input-group">
                <label for="buscar-ciudad" class="sr-only">Ciudad</label>
                <input id="buscar-ciudad" placeholder="Ej. Guayaquil, Madrid, Tokio" autocomplete="off" required>
            </div>
            <button id="btn-buscar">Buscar</button>
        </form>
    </section>

    <div id="indicador-carga" class="loader-container hidden">
        <div class="spinner"></div>
        <p>Consultando las tres APIs...</p>
    </div>

    <div id="mensaje-error" class="error-container hidden" role="alert"></div>

    <article id="contenedor-clima" class="weather-container hidden">
        <section class="main-card">
            <div class="main-card-header">
                <div>
                    <h2 id="clima-ciudad" class="city-name"></h2>
                    <p id="clima-pais" class="country-name"></p>
                </div>
                <span id="clima-icono" class="weather-icon"></span>
            </div>
            <div class="main-card-body">
                <div class="temp-principal"><span id="clima-temp"></span><span class="u-temp">°C</span></div>
                <p id="clima-descripcion" class="weather-description"></p>
            </div>
            <div class="main-card-footer">Consulta: <span id="clima-fecha"></span></div>
        </section>

        <section class="details-grid">
            <div class="detail-card"><h3>Sensación</h3><p class="detail-value" id="detalle-termica"></p></div>
            <div class="detail-card"><h3>Máxima</h3><p class="detail-value" id="detalle-max"></p></div>
            <div class="detail-card"><h3>Mínima</h3><p class="detail-value" id="detalle-min"></p></div>
            <div class="detail-card"><h3>Humedad</h3><p class="detail-value" id="detalle-humedad"></p></div>
            <div class="detail-card"><h3>Viento</h3><p class="detail-value" id="detalle-viento"></p></div>
            <div class="detail-card"><h3>Capital</h3><p class="detail-value" id="pais-capital"></p></div>
            <div class="detail-card"><h3>Moneda</h3><p class="detail-value" id="pais-moneda"></p></div>
            <div class="detail-card"><h3>Población</h3><p class="detail-value" id="pais-poblacion"></p></div>
            <div class="detail-card"><h3>Amanecer</h3><p class="detail-value" id="sol-amanecer"></p></div>
            <div class="detail-card"><h3>Atardecer</h3><p class="detail-value" id="sol-atardecer"></p></div>
        </section>

        <section class="main-card">
            <h3>Bandera del país</h3>
            <img id="pais-bandera" alt="Bandera del país" class="country-flag">
        </section>
    </article>
</main>

<footer class="footer">Open-Meteo · REST Countries · Sunrise-Sunset</footer>
</body>
</html>