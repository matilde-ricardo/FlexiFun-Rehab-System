<?php
declare(strict_types=1);
session_start();

// Autenticação + role
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'fisioterapeuta') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../../db.php';

// Nome do terapeuta (navbar)
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

// --------------------
// Filtros (GET)
// --------------------
$q = trim((string)($_GET['q'] ?? ''));
$nasc_ini = trim((string)($_GET['nasc_ini'] ?? ''));
$nasc_fim = trim((string)($_GET['nasc_fim'] ?? ''));

// validação simples YYYY-MM-DD
$validYmd = static function (string $d): bool {
    return $d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
};
if (!$validYmd($nasc_ini)) $nasc_ini = '';
if (!$validYmd($nasc_fim)) $nasc_fim = '';

// --------------------
// Query com filtros (CORRIGIDA: :q1 e :q2)
// --------------------
$sql = "
    SELECT 
        p.user_id AS id,
        p.nome,
        p.data_nascimento,
        u.email
    FROM paciente p
    JOIN users u ON u.id = p.user_id
    WHERE p.fisioterapeuta_id = :idTerapeuta
";
$params = [':idTerapeuta' => $idTerapeutaUser];

if ($q !== '') {
    $sql .= " AND (p.nome LIKE :q1 OR u.email LIKE :q2) ";
    $like = '%' . $q . '%';
    $params[':q1'] = $like;
    $params[':q2'] = $like;
}

if ($nasc_ini !== '') {
    $sql .= " AND p.data_nascimento >= :nasc_ini ";
    $params[':nasc_ini'] = $nasc_ini;
}

if ($nasc_fim !== '') {
    $sql .= " AND p.data_nascimento <= :nasc_fim ";
    $params[':nasc_fim'] = $nasc_fim;
}

$sql .= " ORDER BY p.nome ASC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$utentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">

</head>
<body>
<div class="layout">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-area">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <main class="conteudo">
            <div class="page-header">
                <h1 class="page-title">Meus Utentes</h1>
                <p class="page-subtitle">
                    Lista de crianças associadas a este terapeuta.
                </p>
            </div>

            <section class="card">
                <div class="card-header-row">
                    <h2 class="card-title">Utentes em acompanhamento</h2>
                    <a href="adicionar.php" class="btn-primary">+ Adicionar Utente</a>
                </div>

                <!-- FILTROS -->
                <form method="get" class="filter-bar">
                    <div class="filter-group grow">
                        <label for="q">Pesquisar (nome ou email)</label>
                        <input
                            type="text"
                            id="q"
                            name="q"
                            value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="ex.: Filipe ou filipe@email.com"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="nasc_ini">Nascimento (de)</label>
                        <input type="date" id="nasc_ini" name="nasc_ini"
                               value="<?= htmlspecialchars($nasc_ini, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="filter-group">
                        <label for="nasc_fim">Nascimento (até)</label>
                        <input type="date" id="nasc_fim" name="nasc_fim"
                               value="<?= htmlspecialchars($nasc_fim, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">Filtrar</button>
                        <a class="btn-link" href="utentes.php">Limpar</a>
                    </div>
                </form>

                <div class="results-count">
                    <?= count($utentes) ?> resultado(s)
                </div>

                <?php if (empty($utentes)): ?>
                    <p class="empty-state">
                        Não há utentes a mostrar com estes filtros.
                    </p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="tabela-utentes">
                            <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Data de nascimento</th>
                                <th>E-mail</th>
                                <th class="col-acoes">Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($utentes as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$u['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?= !empty($u['data_nascimento'])
                                            ? htmlspecialchars(date('d/m/Y', strtotime((string)$u['data_nascimento'])), ENT_QUOTES, 'UTF-8')
                                            : '-' ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)$u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="col-acoes">
                                        <a class="btn-acao detalhe" href="evolucao.php?id=<?= (int)$u['id'] ?>">Evolução</a>
                                        <a class="btn-acao detalhe" href="detalhes.php?id=<?= (int)$u['id'] ?>">Detalhes</a>
                                        <a class="btn-acao editar" href="editar.php?id=<?= (int)$u['id'] ?>">Editar</a>
                                        <a class="btn-acao apagar"
                                           href="apagar.php?id=<?= (int)$u['id'] ?>"
                                           onclick="return confirm('Tens a certeza que queres apagar este utente?');">
                                            Apagar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
</body>
</html>
