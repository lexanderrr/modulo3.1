<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/config/conexion.php';

$rol      = $_POST['rol'] ?? '';
$usuario  = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';

if ($usuario === '' || $password === '') {
    header('Location: index.php?err=credenciales');
    exit;
}

if ($rol === 'admin' || $rol === 'profesor') {
    $stmt = $pdo->prepare('SELECT id, nombre, password, rol FROM administradores WHERE usuario = ?');
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch();

    if ($fila && password_verify($password, $fila['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']     = $fila['id'];
        $_SESSION['admin_nombre'] = $fila['nombre'];
        $_SESSION['admin_rol']    = $fila['rol'];

        if ($fila['rol'] === 'profesor') {
            header('Location: profesor/dashboard.php');
        } else {
            header('Location: admin/dashboard.php');
        }
        exit;
    }
} elseif ($rol === 'padre') {
    $stmt = $pdo->prepare('SELECT id, nombre, apellido, password FROM padres WHERE usuario = ?');
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch();

    if ($fila && password_verify($password, $fila['password'])) {
        session_regenerate_id(true);
        $_SESSION['padre_id']     = $fila['id'];
        $_SESSION['padre_nombre'] = $fila['nombre'] . ' ' . $fila['apellido'];
        header('Location: padres/dashboard.php');
        exit;
    }
}

header('Location: index.php?err=credenciales');
exit;