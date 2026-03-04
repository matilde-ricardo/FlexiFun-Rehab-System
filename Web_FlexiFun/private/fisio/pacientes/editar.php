<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header('Location: utentes.php');
    exit;
}

// id do utilizador (users.id / paciente.user_id)
$idPacienteUser = (int) $_GET['id'];

require_once __DIR__ . '/../../db.php';

// Nome do fisio para navbar
$stmtTer = $pdo->prepare("
    SELECT f.nome
    FROM fisioterapeuta f
    WHERE f.user_id = :uid
    LIMIT 1
");
$stmtTer->execute(['uid' => $idTerapeutaUser]);
$terapeuta = $stmtTer->fetch(PDO::FETCH_ASSOC);
if ($terapeuta && !empty($terapeuta['nome'])) {
    $_SESSION['nome_terapeuta'] = $terapeuta['nome'];
}

// Buscar paciente + email, garantindo que pertence a este fisio
$sql = "
    SELECT
        p.*,
        u.email
    FROM paciente p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = :idPac
      AND p.fisioterapeuta_id = :idTerapeuta
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'idPac'       => $idPacienteUser,
    'idTerapeuta' => $idTerapeutaUser
]);
$utente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    die('Utente não encontrado ou não pertence a este terapeuta.');
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome        = trim($_POST['nome'] ?? '');
    $data_nasc   = $_POST['data_nascimento'] ?: null;
    $email       = trim($_POST['email'] ?? '');
    $telefone    = trim($_POST['telefone'] ?? '');
    $sexo        = $_POST['sexo'] ?? null;
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $nif         = trim($_POST['nif'] ?? '');
    $numUtente   = trim($_POST['numero_utente'] ?? '');
    $morada      = trim($_POST['morada'] ?? '');
    $notas       = trim($_POST['notas'] ?? '');

    if ($nome === '') {
        $erro = 'O nome é obrigatório.';
    } elseif ($email === '') {
        $erro = 'O email é obrigatório.';
    } else {

        // Verificar se email já existe noutro user
        $check = $pdo->prepare("
            SELECT id FROM users
            WHERE email = :email AND id <> :idUser
            LIMIT 1
        ");
        $check->execute([
            'email'  => $email,
            'idUser' => $idPacienteUser
        ]);
        $existe = $check->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $erro = 'Já existe outro utilizador com esse email.';
        } else {

            // Atualizar email em users
            $updUser = $pdo->prepare("
                UPDATE users
                SET email = :email
                WHERE id = :idUser
            ");
            $updUser->execute([
                'email'  => $email,
                'idUser' => $idPacienteUser
            ]);

            // Atualizar dados em paciente
            $updPac = $pdo->prepare("
                UPDATE paciente SET
                    nome            = :nome,
                    data_nascimento = :data_nasc,
                    sexo            = :sexo,
                    telefone        = :telefone,
                    diagnostico     = :diagnostico,
                    nif             = :nif,
                    numero_utente   = :numUtente,
                    morada          = :morada,
                    notas           = :notas
                WHERE user_id = :idPac
                  AND fisioterapeuta_id = :idTerapeuta
            ");
            $updPac->execute([
                'nome'        => $nome,
                'data_nasc'   => $data_nasc,
                'sexo'        => $sexo,
                'telefone'    => $telefone,
                'diagnostico' => $diagnostico,
                'nif'         => $nif,
                'numUtente'   => $numUtente,
                'morada'      => $morada,
                'notas'       => $notas,
                'idPac'       => $idPacienteUser,
                'idTerapeuta' => $idTerapeutaUser
            ]);

            $sucesso = 'Dados atualizados com sucesso!';

            // atualizar array local
            $utente = array_merge($utente, [
                'nome'            => $nome,
                'data_nascimento' => $data_nasc,
                'sexo'            => $sexo,
                'telefone'        => $telefone,
                'diagnostico'     => $diagnostico,
                'nif'             => $nif,
                'numero_utente'   => $numUtente,
                'morada'          => $morada,
                'notas'           => $notas,
                'email'           => $email
            ]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Utente – FlexiFun</title>
    <link rel="stylesheet" href="../../assets/css/private.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<div class="layout">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-area">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <main class="conteudo">
            <div class="page-header">
                <h1 class="page-title">Editar Utente</h1>
                <p class="page-subtitle">
                    Atualiza os dados clínicos e administrativos do paciente.
                </p>
            </div>

            <section class="card">
                <?php if ($erro): ?>
                    <p class="erro-msg"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <p class="sucesso-msg"><?= htmlspecialchars($sucesso) ?></p>
                <?php endif; ?>

                <form method="post" class="formulario">

                    <label>Nome*</label>
                    <input type="text" name="nome" required
                           value="<?= htmlspecialchars($utente['nome'] ?? '') ?>">

                    <label>Data de nascimento</label>
                    <input type="date" name="data_nascimento"
                           value="<?= htmlspecialchars($utente['data_nascimento'] ?? '') ?>">

                    <label>Email</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($utente['email'] ?? '') ?>">

                    <label>Telefone</label>
                    <input type="text" name="telefone"
                           value="<?= htmlspecialchars($utente['telefone'] ?? '') ?>">

                    <label>Sexo</label>
                    <select name="sexo">
                        <option value="">—</option>
                        <option value="M" <?= (($utente['sexo'] ?? '') === 'M') ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= (($utente['sexo'] ?? '') === 'F') ? 'selected' : '' ?>>Feminino</option>
                    </select>

                    <label>NIF</label>
                    <input type="text" name="nif"
                           value="<?= htmlspecialchars($utente['nif'] ?? '') ?>">

                    <label>Nº Utente</label>
                    <input type="text" name="numero_utente"
                           value="<?= htmlspecialchars($utente['numero_utente'] ?? '') ?>">

                    <label>Morada</label>
                    <input type="text" name="morada"
                           value="<?= htmlspecialchars($utente['morada'] ?? '') ?>">

                    <label>Diagnóstico</label>
                    <textarea name="diagnostico" rows="3"><?= htmlspecialchars($utente['diagnostico'] ?? '') ?></textarea>

                    <label>Notas / Observações</label>
                    <textarea name="notas" rows="4"><?= htmlspecialchars($utente['notas'] ?? '') ?></textarea>

                    <button type="submit" class="btn-primary">Guardar alterações</button>
                    <a href="utentes.php" class="btn-acao detalhe">Cancelar</a>
                </form>
            </section>
        </main>
    </div>
</div>
</body>
</html>
