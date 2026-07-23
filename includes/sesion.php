<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Exige sesión de administrador; si no existe, redirige al login. */
function exigirAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: /index.php?err=sesion');
        exit;
    }
}

/** Exige sesión de padre/madre; si no existe, redirige al login. */
function exigirPadre(): void {
    if (empty($_SESSION['padre_id'])) {
        header('Location: /index.php?err=sesion');
        exit;
    }
}

/** Escapa texto para salida segura en HTML. */
function h($texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
