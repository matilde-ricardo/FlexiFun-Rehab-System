<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'fisioterapeuta') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../../db.php';

// Buscar nome do fisio (navbar)
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

$erro = '';

function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome        = trim($_POST['nome'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $data        = $_POST['data_nascimento'] ?? null;
    $sexo        = $_POST['sexo'] ?? null;
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $nif         = trim($_POST['nif'] ?? '');
    $numUtente   = trim($_POST['numero_utente'] ?? '');
    $telefone    = trim($_POST['telefone'] ?? '');
    $morada      = trim($_POST['morada'] ?? '');
    $notas       = trim($_POST['notas'] ?? '');

    if ($nome === '' || $email === '') {
        $erro = 'Nome e email são obrigatórios.';
    } else {

        // Verificar se email já existe
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
        $check->execute([':e' => $email]);
        $existe = $check->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $erro = 'Este email já está registado.';
        } else {
            // Criar utilizador com password default
            $password = '1234';
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare("
                INSERT INTO users (email, password_hash, role, is_active)
                VALUES (:email, :ph, 'paciente', 1)
            ");

            $stmtUser->execute([
                ':email' => $email,
                ':ph'    => $hash
            ]);

            $newUserId = (int) $pdo->lastInsertId();

            // Criar paciente
            $stmtPac = $pdo->prepare("
                INSERT INTO paciente (
                    user_id,
                    nome,
                    data_nascimento,
                    sexo,
                    diagnostico,
                    nif,
                    numero_utente,
                    telefone,
                    morada,
                    notas,
                    fisioterapeuta_id
                ) VALUES (
                    :uid,
                    :nome,
                    :data,
                    :sexo,
                    :diag,
                    :nif,
                    :numUtente,
                    :telefone,
                    :morada,
                    :notas,
                    :fisio
                )
            ");

            $stmtPac->execute([
                ':uid'       => $newUserId,
                ':nome'      => $nome,
                ':data'      => ($data !== '' ? $data : null),
                ':sexo'      => ($sexo !== '' ? $sexo : null),
                ':diag'      => $diagnostico,
                ':nif'       => $nif,
                ':numUtente' => $numUtente,
                ':telefone'  => $telefone,
                ':morada'    => $morada,
                ':notas'     => $notas,
                ':fisio'     => $idTerapeutaUser
            ]);

            header('Location: utentes.php?ok=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Utente – FlexiFun</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- usa o teu private.css (com o /FlexiFun) -->
    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">

    <style>
      /* Não mexe no .formulario global do teu CSS; usamos classes novas */
      .required{ color:#b91c1c; font-weight:800; }
      .muted{ color:#6b7280; }

      /* wrapper ocupa a largura do conteúdo (fica "normal" como as outras páginas) */
      .add-wrap{
        width: 100%;
        max-width: 1200px;
      }

      .add-grid{
        margin-top: 12px;
        display:grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        align-items: start;
      }

      .add-section.card{
        padding: 16px;
      }

      .add-title{
        margin: 0 0 12px;
        display:flex;
        align-items:center;
        gap:10px;
        font-size: 1rem;
        font-weight: 800;
        color:#111827;
      }

      .add-row{
        display:flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
      }
      .add-row:last-child{ margin-bottom: 0; }

      /* usa os estilos dos inputs do teu private.css, mas força full width */
      .add-row input,
      .add-row select,
      .add-row textarea{
        width: 100%;
      }

      .add-row textarea{
        min-height: 110px;
        resize: vertical;
      }

      .add-actions{
        margin-top: 16px;
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        align-items:center;
      }

      .btn-ghost{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(0,0,0,.10);
        background: rgba(255,255,255,.75);
        text-decoration:none;
        font-weight: 700;
        color:#111827;
      }
      .btn-ghost:hover{ filter: brightness(0.98); }

      @media (max-width: 980px){
        .add-grid{ grid-template-columns: 1fr; }
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
                <h1 class="page-title">Adicionar Utente</h1>
                <p class="page-subtitle">Regista um novo paciente associado a ti.</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="erro-msg"><?= h($erro) ?></div>
            <?php endif; ?>

            <div class="add-wrap">
                <form method="post" class="formulario" style="max-width:none;">

                    <div class="add-grid">

                        <!-- IDENTIFICAÇÃO -->
                        <section class="card add-section">
                            <h3 class="add-title"><i class="bi bi-person-plus"></i> Identificação</h3>

                            <div class="add-row">
                                <label for="nome">Nome completo <span class="required">*</span></label>
                                <input id="nome" type="text" name="nome" required
                                       placeholder="ex.: Filipe Coelho"
                                       value="<?= h($_POST['nome'] ?? '') ?>">
                            </div>

                            <div class="add-row">
                                <label for="data_nascimento">Data de nascimento</label>
                                <input id="data_nascimento" type="date" name="data_nascimento"
                                       value="<?= h($_POST['data_nascimento'] ?? '') ?>">
                            </div>

                            <div class="add-row">
                                <label for="sexo">Sexo</label>
                                <select id="sexo" name="sexo">
                                    <option value="">—</option>
                                    <option value="M" <?= (($_POST['sexo'] ?? '') === 'M') ? 'selected' : '' ?>>Masculino</option>
                                    <option value="F" <?= (($_POST['sexo'] ?? '') === 'F') ? 'selected' : '' ?>>Feminino</option>
                                    <option value="O" <?= (($_POST['sexo'] ?? '') === 'O') ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </section>

                        <!-- CONTACTO -->
                        <section class="card add-section">
                            <h3 class="add-title"><i class="bi bi-envelope"></i> Contacto</h3>

                            <div class="add-row">
                                <label for="email">Email do utente <span class="required">*</span></label>
                                <input id="email" type="email" name="email" required
                                       placeholder="ex.: utente@gmail.com"
                                       value="<?= h($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="add-row">
                                <label for="telefone">Telefone</label>
                                <input id="telefone" type="text" name="telefone"
                                       placeholder="ex.: 912345678"
                                       value="<?= h($_POST['telefone'] ?? '') ?>">
                            </div>

                            <div class="add-row">
                                <label for="morada">Morada</label>
                                <textarea id="morada" name="morada"
                                          placeholder="Rua, nº, localidade..."><?= h($_POST['morada'] ?? '') ?></textarea>
                            </div>
                        </section>

                        <!-- DOCUMENTOS -->
                        <section class="card add-section">
                            <h3 class="add-title"><i class="bi bi-card-text"></i> Documentos</h3>

                            <div class="add-row">
                                <label for="nif">NIF</label>
                                <input id="nif" type="text" name="nif"
                                       placeholder="ex.: 123456789"
                                       value="<?= h($_POST['nif'] ?? '') ?>">
                            </div>

                            <div class="add-row">
                                <label for="numero_utente">Nº Utente</label>
                                <input id="numero_utente" type="text" name="numero_utente"
                                       placeholder="ex.: 1617999"
                                       value="<?= h($_POST['numero_utente'] ?? '') ?>">
                            </div>
                        </section>

                        <!-- CLÍNICO -->
                        <section class="card add-section">
                            <h3 class="add-title"><i class="bi bi-clipboard2-pulse"></i> Informação clínica</h3>

                            <div class="add-row">
                                <label for="diagnostico">Diagnóstico clínico</label>
                                <textarea id="diagnostico" name="diagnostico"
                                          placeholder="ex.: Lesão neuromuscular..."><?= h($_POST['diagnostico'] ?? '') ?></textarea>
                            </div>

                            <div class="add-row">
                                <label for="notas">Notas / Observações</label>
                                <textarea id="notas" name="notas"
                                          placeholder="Notas adicionais..."><?= h($_POST['notas'] ?? '') ?></textarea>
                            </div>
                        </section>

                    </div>

                    <div class="add-actions">
                        <button class="btn-primary" type="submit">
                            <i class="bi bi-check2-circle"></i> Adicionar Utente
                        </button>

                        <a href="utentes.php" class="btn-ghost">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>

                        <span class="muted" style="margin-left:auto;">
                            Campos com <span class="required">*</span> são obrigatórios
                        </span>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>