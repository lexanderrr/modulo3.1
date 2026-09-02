<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/config/conexion.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$tipo   = $_POST['tipo'] ?? '';
$correo = trim($_POST['correo'] ?? '');

if (!in_array($tipo, ['padre', 'admin'], true) || $correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Completa el tipo de usuario y un correo válido.']);
    exit;
}

// Se busca la cuenta por correo. Por seguridad, la respuesta al usuario es la
// misma exista o no la cuenta (para no revelar qué correos están registrados);
// la solicitud solo se guarda en la base de datos si el correo coincide.
if ($tipo === 'padre') {
    $stmt = $pdo->prepare('SELECT id FROM padres WHERE correo = ?');
} else {
    $stmt = $pdo->prepare('SELECT id FROM administradores WHERE correo = ?');
}
$stmt->execute([$correo]);
$fila = $stmt->fetch();

if ($fila) {
    $stmt = $pdo->prepare(
        'INSERT INTO solicitudes_password (tipo_usuario, id_usuario, correo, estado) VALUES (?,?,?,\'pendiente\')'
    );
    $stmt->execute([$tipo, $fila['id'], $correo]);
}

echo json_encode(['ok' => true, 'mensaje' => 'Si el correo está registrado, la secretaría te contactará para restablecer tu contraseña.']);
