<?php
session_start();
require 'conexion.php';

$correo = trim($_POST['correo'] ?? '');
$clave = $_POST['contrasena'] ?? '';

$stmt = $conexion->prepare('SELECT id,nombre,correo,contrasena FROM usuarios WHERE correo=?');
$stmt->bind_param('s', $correo);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if ($usuario && password_verify($clave, $usuario['contrasena'])) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
    header('Location: dashboard.php');
    exit;
}

header('Location: index.php?error=1');
exit;
?>