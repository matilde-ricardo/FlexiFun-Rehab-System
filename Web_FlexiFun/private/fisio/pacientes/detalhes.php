<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'fisioterapeuta') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header('Location: utentes.php');
    exit;
}

$idPacienteUser = (int) $_GET['id'];

require_once __DIR__ . '/../../db.php';

// Nome do fisio para navbar
$stmtTer = $pdo->prepare("
    SELECT f.nome
    FROM fisioterapeuta f
    WHERE f.user_id = :uid
    LIMIT 1
");
$stmtTer->execute([':uid' => $idTerapeutaUser]);
$terapeuta = $stmtTer->fetch(PDO::FETCH_ASSOC);
if ($terapeuta && !empty($terapeuta['nome'])) {
    $_SESSION['nome_terapeuta'] = $terapeuta['nome'];
}

// Buscar paciente + email garantindo que é deste fisio
$sql = "
    SELECT
        p.*,
        p.user_id AS id_utente,
        u.email
    FROM paciente p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = :idPac
      AND p.fisioterapeuta_id = :idTerapeuta
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':idPac'       => $idPacienteUser,
    ':idTerapeuta' => $idTerapeutaUser
]);
$utente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    die('Utente não encontrado ou não pertence a este terapeuta.');
}

function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmtDate(?string $d): string {
    if (!$d) return '-';
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : '-';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Utente – FlexiFun</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">

    <style>
      .muted{ color:#6b7280; }

      .details-header{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
      }

      .pill-id{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(115,187,123,0.14);
        color: #166534;
        font-weight: 700;
        border: 1px solid rgba(115,187,123,0.35);
        white-space: nowrap;
      }

      .details-grid{
        margin-top: 14px;
        display:grid;
        grid-template-columns: repeat(2, minmax(260px, 1fr));
        gap: 14px;
        align-items:start;
      }

      .info-box{
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 16px;
        padding: 14px;
        background: rgba(255,255,255,.75);
      }

      .info-title{
        margin: 0 0 10px;
        display:flex;
        align-items:center;
        gap:10px;
        font-size: 1rem;
        color: #111827;
      }

      .kv{
        display:grid;
        grid-template-columns: 160px 1fr;
        gap: 10px 12px;
        font-size: .92rem;
      }

      .k{
        color:#6b7280;
        font-weight: 700;
      }

      .v{
        color:#111827;
        font-weight: 600;
      }

      .text-box{
        margin-top: 12px;
        border-top: 1px dashed rgba(0,0,0,.12);
        padding-top: 12px;
      }

      .text-label{
        margin: 0 0 6px;
        font-weight: 800;
        color:#374151;
        font-size: .92rem;
      }

      .text-value{
        margin: 0;
        color:#111827;
        line-height: 1.45;
        white-space: pre-wrap;
      }

      .actions-row{
        margin-top: 16px;
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        align-items:center;
      }

      @media (max-width: 900px){
        .details-grid{ grid-template-columns: 1fr; }
        .kv{ grid-template-columns: 140px 1fr; }
      }
    </style>
</head>
<body>
<div class="layout">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-area">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <main class="conteudo">
            <div class="page-header">
                <h1 class="page-title">Detalhes do Utente</h1>
                <p class="page-subtitle">
                    Informação de identificação, contacto e dados clínicos.
                </p>
            </div>

            <section class="card">
                <div class="details-header">
                    <div>
                        <h2 class="card-title" style="margin-bottom:6px;"><?= h($utente['nome'] ?? '') ?></h2>
                        <div class="muted">Ficha do utente</div>
                    </div>

                    <div class="pill-id">
                        <i class="bi bi-hash"></i>
                        ID do utente: <?= (int)($utente['id_utente'] ?? 0) ?>
                    </div>
                </div>

                <div class="details-grid">
                    <!-- Identificação -->
                    <div class="info-box">
                        <h3 class="info-title"><i class="bi bi-person-badge"></i> Identificação</h3>
                        <div class="kv">
                            <div class="k">Data nascimento</div>
                            <div class="v"><?= h(fmtDate($utente['data_nascimento'] ?? null)) ?></div>

                            <div class="k">Sexo</div>
                            <div class="v"><?= h($utente['sexo'] ?? '-') ?></div>

                            <div class="k">NIF</div>
                            <div class="v"><?= h($utente['nif'] ?? '-') ?></div>

                            <div class="k">Nº Utente</div>
                            <div class="v"><?= h($utente['numero_utente'] ?? '-') ?></div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="info-box">
                        <h3 class="info-title"><i class="bi bi-telephone"></i> Contacto</h3>
                        <div class="kv">
                            <div class="k">Email</div>
                            <div class="v"><?= h($utente['email'] ?? '-') ?></div>

                            <div class="k">Telefone</div>
                            <div class="v"><?= h($utente['telefone'] ?? '-') ?></div>

                            <div class="k">Morada</div>
                            <div class="v"><?= h($utente['morada'] ?? '-') ?></div>
                        </div>
                    </div>

                    <!-- Clínico (ocupa a largura toda quando der) -->
                    <div class="info-box" style="grid-column: 1 / -1;">
                        <h3 class="info-title"><i class="bi bi-clipboard2-pulse"></i> Informação clínica</h3>

                        <div class="text-box" style="margin-top:0; border-top:none; padding-top:0;">
                            <p class="text-label">Diagnóstico</p>
                            <p class="text-value"><?= h($utente['diagnostico'] ?? '-') ?></p>
                        </div>

                        <div class="text-box">
                            <p class="text-label">Notas / Observações</p>
                            <p class="text-value"><?= h($utente['notas'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>

                <div class="actions-row">
                    <a class="btn-acao editar" href="editar.php?id=<?= (int)($utente['user_id'] ?? 0) ?>">
                        <i class="bi bi-pencil-square"></i> Editar
                    </a>

                    <a class="btn-acao detalhe" href="evolucao.php?id=<?= (int)($utente['user_id'] ?? 0) ?>">
                        <i class="bi bi-graph-up"></i> Evolução
                    </a>

                    <a class="btn-acao detalhe" href="utentes.php">
                        <i class="bi bi-arrow-left"></i> Voltar à lista
                    </a>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>