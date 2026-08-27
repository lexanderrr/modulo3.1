<?php
// =========================================================
// Conexión a la base de datos: portal_notas
// Motor: MySQL / MariaDB (XAMPP)
// =========================================================

$DB_HOST = 'localhost';
$DB_NAME = 'portal_notas';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos. Verifica que el servidor MySQL esté activo y que los datos de conexión sean correctos.');
}