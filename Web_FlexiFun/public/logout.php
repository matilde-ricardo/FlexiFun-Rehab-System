<?php
declare(strict_types=1);

session_start();
$_SESSION = [];

// remover cookie da sessão (boa prática)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
?>
<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <title>Sessão terminada – FlexiFun</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Fonte global -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- CSS do login / páginas públicas -->
  <link rel="stylesheet" href="/FlexiFun/public/assets/css/login.css">

  <!-- Redirect automático para a página inicial -->
  <meta http-equiv="refresh" content="2;url=/FlexiFun/public/index.php">
</head>

<body class="login-body">
  <div class="login-wrapper">
    <div class="login-card" style="text-align:center;">
      <h1>Sessão terminada</h1>
      <p>A redirecionar para a página inicial…</p>
    </div>
  </div>
</body>
</html>
