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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require __DIR__ . '/conexion.php';

        $correo = trim($_POST['correo'] ?? '');
        $clave = $_POST['contrasena'] ?? '';

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $clave === '') {
            $mensaje = 'Completa correctamente el correo y la contraseña.';
        } else {
            $stmt = $conexion->prepare('SELECT id, nombre, correo, contrasena FROM usuarios WHERE correo = ? LIMIT 1');

            if (!$stmt) {
                throw new Exception('No se pudo preparar la consulta.');
            }

            $stmt->bind_param('s', $correo);
            $stmt->execute();
            $stmt->bind_result($id, $nombre, $correoGuardado, $hash);

            if ($stmt->fetch() && password_verify($clave, $hash)) {
                $stmt->close();
                $_SESSION['usuario_id'] = (int)$id;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_correo'] = $correoGuardado;
                header('Location: dashboard.php');
                exit;
            }

            $stmt->close();
            $mensaje = 'Correo o contraseña incorrectos.';
        }
    } catch (Throwable $error) {
        error_log('Error en index.php: ' . $error->getMessage());
        $mensaje = 'No se pudo iniciar sesión por un error del servidor.';
    }
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

            <form action="index.php" method="POST" class="auth-form">
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