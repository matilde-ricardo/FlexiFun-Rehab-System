<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'fisioterapeuta') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int)$_SESSION['user_id'];


require_once dirname(__DIR__) . '/db.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function fmt_dt(?string $dt): string {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date('d/m/Y H:i', $ts) : '—';
}

// --------------------
// Nome do terapeuta (para navbar / saudação)
// --------------------
try {
    $stmtTer = $pdo->prepare("
        SELECT f.nome
        FROM fisioterapeuta f
        WHERE f.user_id = :uid
        LIMIT 1
    ");
    $stmtTer->execute([':uid' => $idTerapeutaUser]);
    $ter = $stmtTer->fetch(PDO::FETCH_ASSOC);

    if ($ter && !empty($ter['nome'])) {
        $_SESSION['nome_terapeuta'] = (string)$ter['nome'];
    }
} catch (Throwable $e) {
    // se falhar, não bloqueia
}

$terapeutaNome = (string)($_SESSION['nome_terapeuta'] ?? '');

// --------------------
// Datas para filtros (hoje e semana)
// --------------------
$today = date('Y-m-d');
$todayStart = $today . ' 00:00:00';
$todayEnd   = $today . ' 23:59:59';

// semana começa na segunda
$dayN = (int)date('N'); // 1..7
$mondayTs = strtotime('-' . ($dayN - 1) . ' days');
$weekStart = date('Y-m-d 00:00:00', $mondayTs);
$weekEnd   = date('Y-m-d 23:59:59'); // agora

// --------------------
// STATS reais
// --------------------
$stats = [
    'utentes_total'  => 0,
    'sessoes_hoje'   => 0,
    'sessoes_semana' => 0,
    'resultados'     => 0,
    'ultima_sessao'  => null,
];

try {
    // utentes associados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM paciente WHERE fisioterapeuta_id = :fid");
    $stmt->execute([':fid' => $idTerapeutaUser]);
    $stats['utentes_total'] = (int)$stmt->fetchColumn();

    // sessões hoje
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM sessao
        WHERE fisioterapeuta_id = :fid
          AND iniciou_em BETWEEN :ini AND :fim
    ");
    $stmt->execute([':fid' => $idTerapeutaUser, ':ini' => $todayStart, ':fim' => $todayEnd]);
    $stats['sessoes_hoje'] = (int)$stmt->fetchColumn();

    // sessões semana
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM sessao
        WHERE fisioterapeuta_id = :fid
          AND iniciou_em BETWEEN :ini AND :fim
    ");
    $stmt->execute([':fid' => $idTerapeutaUser, ':ini' => $weekStart, ':fim' => $weekEnd]);
    $stats['sessoes_semana'] = (int)$stmt->fetchColumn();

    // total de resultados registados (qualquer nível)
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM resultado_nivel rn
        JOIN sessao s ON s.id = rn.sessao_id
        WHERE s.fisioterapeuta_id = :fid
    ");
    $stmt->execute([':fid' => $idTerapeutaUser]);
    $stats['resultados'] = (int)$stmt->fetchColumn();

    // última sessão (data)
    $stmt = $pdo->prepare("
        SELECT MAX(iniciou_em)
        FROM sessao
        WHERE fisioterapeuta_id = :fid
    ");
    $stmt->execute([':fid' => $idTerapeutaUser]);
    $stats['ultima_sessao'] = $stmt->fetchColumn() ?: null;

} catch (Throwable $e) {
    // se algo falhar, fica a 0/—
}

// --------------------
// Sessões recentes (últimas 6)
// Mostra nome do utente + data + níveis que tiveram resultados nessa sessão
// --------------------
$recentSessions = [];
try {
    $stmt = $pdo->prepare("
        SELECT
            s.id AS sessao_id,
            s.iniciou_em,
            p.user_id AS paciente_id,
            p.nome AS paciente_nome,
            GROUP_CONCAT(DISTINCT rn.nivel_id ORDER BY rn.nivel_id SEPARATOR ', ') AS niveis
        FROM sessao s
        JOIN paciente p ON p.user_id = s.paciente_id
        LEFT JOIN resultado_nivel rn ON rn.sessao_id = s.id
        WHERE s.fisioterapeuta_id = :fid
        GROUP BY s.id, s.iniciou_em, p.user_id, p.nome
        ORDER BY s.iniciou_em DESC
        LIMIT 6
    ");
    $stmt->execute([':fid' => $idTerapeutaUser]);
    $recentSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $recentSessions = [];
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>FlexiFun – Área do Terapeuta</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">
</head>
<body>

<div class="layout">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-area">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="conteudo">

      <div class="page-header">
        <h1 class="page-title">Olá, <?= h($terapeutaNome !== '' ? $terapeutaNome : 'Terapeuta') ?></h1>
        <p class="page-subtitle">Aqui podes acompanhar os utentes e consultar as sessões registadas.</p>
      </div>

      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Utentes associados</div>
          <div class="stat-value"><?= (int)$stats['utentes_total'] ?></div>
          <span class="stat-tag">—</span>
        </div>

        <div class="stat-card">
          <div class="stat-label">Sessões registadas hoje</div>
          <div class="stat-value"><?= (int)$stats['sessoes_hoje'] ?></div>
          <span class="stat-tag">—</span>
        </div>

        <div class="stat-card">
          <div class="stat-label">Sessões esta semana</div>
          <div class="stat-value"><?= (int)$stats['sessoes_semana'] ?></div>
          <span class="stat-tag">—</span>
        </div>

        <div class="stat-card">
          <div class="stat-label">Resultados registados</div>
          <div class="stat-value"><?= (int)$stats['resultados'] ?></div>
          <span class="stat-tag">—</span>
        </div>
      </section>

      <section class="card">
        <div class="card-header-row">
          <h2 class="card-title">Última sessão registada</h2>
        </div>
        <p style="margin:0; color:#555;">
          <?= h(fmt_dt(is_string($stats['ultima_sessao']) ? $stats['ultima_sessao'] : null)) ?>
        </p>
      </section>

      <section class="card">
        <div class="card-header-row">
          <h2 class="card-title">Sessões recentes</h2>
        </div>

        <?php if (empty($recentSessions)): ?>
          <p class="empty-state">Ainda não existem sessões registadas.</p>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="tabela-utentes">
              <thead>
                <tr>
                  <th>Utente</th>
                  <th>Data</th>
                  <th>Níveis registados</th>
                  <th class="col-acoes">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentSessions as $s): ?>
                  <tr>
                    <td><?= h((string)$s['paciente_nome']) ?></td>
                    <td><?= h(fmt_dt((string)$s['iniciou_em'])) ?></td>
                    <td><?= h((string)($s['niveis'] ?: '—')) ?></td>
                    <td class="col-acoes">
                      <a class="btn-acao detalhe" href="/FlexiFun/private/fisio/pacientes/evolucao.php?id=<?= (int)$s['paciente_id'] ?>&nivel=1">Evolução</a>
                      <a class="btn-acao detalhe" href="/FlexiFun/private/fisio/pacientes/detalhes.php?id=<?= (int)$s['paciente_id'] ?>">Detalhes</a>
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