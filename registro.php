<?php
session_start();
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['contrasena'] ?? '';

    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($clave) < 4) {
        $mensaje = 'Completa correctamente todos los campos.';
    } else {
        $verificar = $conexion->prepare('SELECT id FROM usuarios WHERE correo=?');
        $verificar->bind_param('s', $correo);
        $verificar->execute();

        if ($verificar->get_result()->num_rows > 0) {
            $mensaje = 'Ese correo ya está registrado.';
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare('INSERT INTO usuarios(nombre,correo,contrasena) VALUES(?,?,?)');
            $stmt->bind_param('sss', $nombre, $correo, $hash);

            if ($stmt->execute()) {
                header('Location: index.php?registro=ok');
                exit;
            }

            $mensaje = 'No se pudo crear la cuenta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - GeoClima</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<section class="auth-screen">
    <div class="auth-card">
        <h1>Crear cuenta</h1>
        <p>Regístrate para acceder a GeoClima.</p>
        <?php if ($mensaje): ?><div class="auth-message"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
        <form method="POST" class="auth-form">
            <label for="nombre">Nombre completo</label>
            <input id="nombre" name="nombre" type="text" required>
            <label for="correo">Correo electrónico</label>
            <input id="correo" name="correo" type="email" required>
            <label for="contrasena">Contraseña</label>
            <input id="contrasena" name="contrasena" type="password" minlength="4" required>
            <button type="submit">Crear cuenta</button>
        </form>
        <p class="auth-link">¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a></p>
    </div>
</section>
</body>
</html>