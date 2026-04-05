<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$email    = $data['email']    ?? '';
$password = $data['password'] ?? '';

if ($email === 'test@test.de' && $password === 'geheim') {
    http_response_code(200);
    echo json_encode(['ok' => true]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'E-Mail oder Passwort ist falsch']);
}