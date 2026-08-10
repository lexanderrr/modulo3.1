<?php

// Reemplaza estos datos con los que te da Neon Console (pestaña Connection Details)
$DB_HOST = 'ep-summer-queen-ayv1o5f6-pooler.c-5.us-east-2.aws.neon.tech'; 
$DB_NAME = 'neondb'; 
$DB_USER = 'neondb_owner';   
$DB_PASS = 'npg_5OSsYXBn2zjV';        
$DB_PORT = '5432'; // Puerto estándar de PostgreSQL

try {
    // Cambiamos "mysql:" por "pgsql:" y añadimos el puerto y el sslmode obligatorio de Neon
    $pdo = new PDO(
        "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};sslmode=require",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos de Neon: ' . $e->getMessage());
}