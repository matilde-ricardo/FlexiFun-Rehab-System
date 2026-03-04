<?php
declare(strict_types=1);
session_start();

// só fisioterapeuta
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'fisioterapeuta') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

require_once __DIR__ . '/../../db.php';

$userId = (int)$_SESSION['user_id'];

// buscar dados do fisio + user
$stmt = $pdo->prepare("
    SELECT 
        u.email, u.created_at, u.last_login_at,
        f.nome, f.especialidade, f.telefone
    FROM users u
    LEFT JOIN fisioterapeuta f ON f.user_id = u.id
    WHERE u.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $userId]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

// fallback seguro
if (!$perfil) {
    $perfil = [
        'email' => '—',
        'created_at' => null,
        'last_login_at' => null,
        'nome' => 'Terapeuta',
        'especialidade' => '—',
        'telefone' => '—',
    ];
}

// garante nome na sessão (para navbar/sidebar)
if (!empty($perfil['nome'])) {
    $_SESSION['nome_terapeuta'] = (string)$perfil['nome'];
}

function fmt_dt(?string $dt): string {
    if (!$dt) return '—';
    return date('d/m/Y H:i', strtotime($dt));
}
?>


<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfil & Definições – FlexiFun</title>

    <!-- Fonte (igual em todo o site) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- CSS PRIVADO (CAMINHO ABSOLUTO) -->
    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">
</head>
<body>
<div class="layout">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="conteudo">
            <div class="page-header">
                <h1 class="page-title">Perfil &amp; Definições</h1>
                <p class="page-subtitle">Informação da conta</p>
            </div>

            <section class="card">
                <div class="card-header-row">
                    <h2 class="card-title">Dados do Terapeuta</h2>
                </div>

                <div class="profile-grid">
                    <div class="profile-item">
                        <div class="profile-label">Nome</div>
                        <div class="profile-value"><?= htmlspecialchars((string)$perfil['nome'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Especialidade</div>
                        <div class="profile-value"><?= htmlspecialchars((string)($perfil['especialidade'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Telefone</div>
                        <div class="profile-value"><?= htmlspecialchars((string)($perfil['telefone'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Email</div>
                        <div class="profile-value"><?= htmlspecialchars((string)$perfil['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
            </section>

            <section class="card" style="margin-top:14px;">
                <div class="card-header-row">
                    <h2 class="card-title">Conta</h2>
                </div>

                <div class="profile-grid">
                    <div class="profile-item">
                        <div class="profile-label">Conta criada em</div>
                        <div class="profile-value"><?= htmlspecialchars(fmt_dt($perfil['created_at']), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Último login</div>
                        <div class="profile-value"><?= htmlspecialchars(fmt_dt($perfil['last_login_at']), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Permissão</div>
                        <div class="profile-value">Fisioterapeuta</div>
                    </div>
                </div>

              
            </section>
        </div>
    </main>
</div>
</body>
</html>