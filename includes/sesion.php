<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Exige sesión de administrador (rol='admin'); si no, redirige al login. */
function exigirAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ../index.php?err=sesion');
        exit;
    }
}

/** Exige sesión de padre/madre; si no existe, redirige al login. */
function exigirPadre(): void {
    if (empty($_SESSION['padre_id'])) {
        header('Location: ../index.php?err=sesion');
        exit;
    }
}

/** Exige sesión de profesor (fila de administradores con rol='profesor'). */
function exigirProfesor(): void {
    if (empty($_SESSION['admin_id']) || ($_SESSION['admin_rol'] ?? '') !== 'profesor') {
        header('Location: ../index.php?err=sesion');
        exit;
    }
}

/** Exige sesión de administrador o cajero (los únicos roles autorizados para el módulo de pagos). */
function exigirCajero(): void {
    $rolesPermitidos = ['admin', 'cajero'];
    if (empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_rol'] ?? 'admin', $rolesPermitidos, true)) {
        header('Location: ../index.php?err=sesion');
        exit;
    }
}

/** Escapa texto para salida segura en HTML. */
function h($texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
