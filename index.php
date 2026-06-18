<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$mensaje = '';
if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $mensaje = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
}
if (isset($_GET['error'])) {
    $mensaje = 'Correo o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - GeoClima</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <section class="auth-screen">
        <div class="auth-card">
            <h1>⛅ GeoClima</h1>
            <p>Inicia sesión para consultar información de ciudades.</p>

            <?php if ($mensaje): ?>
                <div class="auth-message"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <label for="correo">Correo electrónico</label>
                <input id="correo" name="correo" type="email" required>

                <label for="contrasena">Contraseña</label>
                <input id="contrasena" name="contrasena" type="password" required>

                <button type="submit">Iniciar sesión</button>
            </form>

            <p class="auth-link">¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
    </section>
</body>
</html>