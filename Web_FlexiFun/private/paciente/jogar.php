<?php
declare(strict_types=1);
session_start();

// ✅ Proteção: só pacientes autenticados
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'paciente') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

/**
 * ✅ CONFIGURAÇÃO DO FICHEIRO DO JOGO
 * Guarda o ZIP em: FlexiFun/private/downloads/FlexiFun_Windows.zip
 */
$zipFileName = 'FlexiFun_Windows.zip';
$zipFilePath = __DIR__ . '/../downloads/' . $zipFileName;

// ✅ Se clicarem no botão -> faz download (mesmo ficheiro, via ?download=1)
if (isset($_GET['download']) && $_GET['download'] === '1') {

    // segurança extra: só permite este nome (evita path traversal)
    if (!file_exists($zipFilePath)) {
        http_response_code(404);
        echo "Ficheiro do jogo não encontrado. Contacta o suporte.";
        exit;
    }

    // headers para forçar download
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipFilePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');

    readfile($zipFilePath);
    exit;
}

$nomePaciente = $_SESSION['nome_paciente'] ?? 'Paciente';
$fileExists = file_exists($zipFilePath);
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FlexiFun – Jogar</title>

    <!-- Fonte -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- CSS privado -->
    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">

    <style>
        /* Pequenos extras locais para esta página (mantém o estilo do site) */
        .play-wrap { max-width: 980px; margin: 0 auto; }
        .play-hero { text-align: center; margin-bottom: 14px; }
        .play-actions { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top: 10px; }
        .note {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: .95rem;
        }
        .warn {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            font-size: .95rem;
        }
        .steps { margin: 8px 0 0; padding-left: 18px; text-align:left; }
        .steps li { margin: 6px 0; }
    </style>
</head>

<body>
<div class="layout">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main">
        <?php include __DIR__ . '/includes/navbar.php'; ?>

        <div class="conteudo">
            <div class="play-wrap">

                <div class="page-header play-hero">
                    <h1 class="page-title"><i class="bi bi-play-circle"></i> Jogar</h1>
                    <p class="page-subtitle">
                        Olá, <?= htmlspecialchars((string)$nomePaciente, ENT_QUOTES, 'UTF-8') ?>!
                        Aqui podes descarregar o jogo FlexiFun para começares a sessão.
                    </p>
                </div>

                <section class="card">
                    <div class="card-header-row patient-center">
                        <h2 class="card-title">Download do jogo</h2>
                    </div>

                    <?php if ($fileExists): ?>
                        <p>
                            Clica no botão abaixo para descarregar o jogo (Windows). Depois é só extrair e abrir o <strong>.exe</strong>.
                        </p>

                        <div class="play-actions">
                            <a class="btn-primary" href="jogar.php?download=1">
                                <i class="bi bi-download"></i> Jogar (Download)
                            </a>
                        </div>

                        <div class="note">
                            <strong>Antes de jogar:</strong>
                            <ol class="steps">
                                <li>Liga a luva ao PC.</li>
                                <li>Confirma qual é a porta <strong>COM</strong> (Arduino IDE ou Gestor de Dispositivos).</li>
                                <li>Extrai o ZIP para uma pasta.</li>
                                <li>Abre o <strong>FlexiFun.exe</strong>.</li>
                                <li>Se o jogo pedir, seleciona a porta COM correta.</li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <div class="warn">
                            <strong>O ficheiro do jogo não está disponível neste momento.</strong><br>
                            Confirma que colocaste o ZIP em:
                            <code>/FlexiFun/private/downloads/<?= htmlspecialchars($zipFileName, ENT_QUOTES, 'UTF-8') ?></code>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="card">
                    <div class="card-header-row patient-center">
                        <h2 class="card-title">Dicas rápidas</h2>
                    </div>

                    <ul class="steps">
                        <li>Joga com calma e faz força progressiva, sem dor.</li>
                        <li>Se a personagem não responder, confirma a ligação da luva e a porta COM.</li>
                        <li>Segue sempre as indicações do teu fisioterapeuta.</li>
                    </ul>
                </section>

            </div>
        </div>
    </main>

</div>
</body>
</html>