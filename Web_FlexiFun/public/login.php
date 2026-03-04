<?php
declare(strict_types=1);

ob_start();
session_start();

require_once "../private/db.php";

ini_set('display_errors', '1');
error_reporting(E_ALL);

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['user'] ?? ''));
    $pass  = (string)($_POST['pass'] ?? '');
    $roleSelecionado = (string)($_POST['role'] ?? '');

    if (
        $email === '' ||
        $pass === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($roleSelecionado, ['paciente', 'fisioterapeuta'], true)
    ) {
        $erro = 'Credenciais inválidas.';
    } else {

        $stmt = $pdo->prepare("
            SELECT id, email, password_hash, role, is_active
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $loginOk = $user
            && (int)$user['is_active'] === 1
            && hash_equals(trim((string)$user['role']), $roleSelecionado)
            && password_verify($pass, (string)$user['password_hash']);

        if (!$loginOk) {
            $erro = 'Credenciais inválidas.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role']    = trim((string)$user['role']);

            $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")
                ->execute([$_SESSION['user_id']]);

            if ($_SESSION['role'] === 'paciente') {
                header('Location: ../private/paciente/dashboard.php');
                exit;
            }

            if ($_SESSION['role'] === 'fisioterapeuta') {
                header('Location: ../private/fisio/index.php');
                exit;
            }

            header('Location: ../private/admin/dashboard.php');
            exit;
        }
    }
}

ob_end_flush();
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Flexifun – Acesso</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body class="login-body">
<div class="login-wrapper">

    <!-- IMPORTANTE: action absoluto para não haver confusões -->
    <form class="login-card" id="loginForm" method="post" action="/FlexiFun/public/login.php" novalidate>
        <!-- Hidden para “forçar” alguns browsers a tratar como submit real -->
        <input type="hidden" name="do_login" value="1">

        <h1>Flexifun – Acesso</h1>

        <?php if ($erro !== ''): ?>
            <div class="login-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="login-group">
            <label for="role">Entrar como</label>
            <select id="role" name="role" class="login-field" required>
                <option value="">Selecionar...</option>
                <option value="paciente" <?= ($_POST['role'] ?? '') === 'paciente' ? 'selected' : '' ?>>Paciente</option>
                <option value="fisioterapeuta" <?= ($_POST['role'] ?? '') === 'fisioterapeuta' ? 'selected' : '' ?>>Terapeuta</option>
            </select>
        </div>

        <div class="login-group">
            <label for="user">Email</label>
            <input
                type="email"
                id="user"
                name="user"
                class="login-field"
                required
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['user'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <div class="login-group">
            <label for="pass">Palavra-passe</label>
            <input
                type="password"
                id="pass"
                name="pass"
                class="login-field"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="submit" class="login-btn">Entrar</button>
    </form>

</div>

<!-- Fallback: se houver JS a bloquear submit, força submit “a sério” -->
<script>
  const form = document.getElementById('loginForm');
  form.addEventListener('submit', function () {
    // se algum script fizer preventDefault, isto ajuda a garantir envio
    if (typeof form.submit === 'function') {
      // não faz nada aqui; só garante que o botão é submit e o form tem action/method fixos
    }
  });
</script>

</body>
</html>
