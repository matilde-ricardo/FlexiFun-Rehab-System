<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'fisioterapeuta') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int)$_SESSION['user_id'];
require_once dirname(__DIR__, 2) . '/db.php';

$pacienteId = (int)($_GET['id'] ?? 0);
$day = (string)($_GET['day'] ?? '');
$nivelId = (int)($_GET['nivel'] ?? 1);
if (!in_array($nivelId, [1,2,3], true)) $nivelId = 1;

if ($pacienteId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
    header('Location: utentes.php');
    exit;
}

// confirmar paciente pertence ao fisio + dados
$stmtP = $pdo->prepare("
    SELECT p.user_id, p.nome, u.email
    FROM paciente p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = :pid AND p.fisioterapeuta_id = :fid
    LIMIT 1
");
$stmtP->execute([':pid' => $pacienteId, ':fid' => $idTerapeutaUser]);
$pac = $stmtP->fetch(PDO::FETCH_ASSOC);
if (!$pac) {
    header('Location: utentes.php');
    exit;
}

// buscar resultados do dia e nível escolhido
$stmt = $pdo->prepare("
    SELECT 
        rn.id AS resultado_id,
        s.iniciou_em,
        rn.forca_max,
        rn.flexao_max,
        rn.tempo_reacao_ms,
        rn.raw_data
    FROM resultado_nivel rn
    JOIN sessao s ON s.id = rn.sessao_id
    WHERE s.paciente_id = :pid
      AND s.fisioterapeuta_id = :fid
      AND rn.nivel_id = :nid
      AND DATE(s.iniciou_em) = :day
    ORDER BY s.iniciou_em ASC
");
$stmt->execute([':pid' => $pacienteId, ':fid' => $idTerapeutaUser, ':nid' => $nivelId, ':day' => $day]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sessions = [];
foreach ($rows as $r) {
    $raw = [];
    if (!empty($r['raw_data'])) {
        $raw = json_decode((string)$r['raw_data'], true) ?: [];
    }

    $durationMs = 0;
    if (isset($raw['durationMs'])) $durationMs = (int)$raw['durationMs'];
    else if (!empty($r['tempo_reacao_ms'])) $durationMs = (int)$r['tempo_reacao_ms'];

    // Nivel 1/2
    $coinForces = $raw['coinForces'] ?? array_fill(0, 10, 0);
    if (!is_array($coinForces) || count($coinForces) !== 10) $coinForces = array_fill(0, 10, 0);
    $threshold = isset($raw['threshold']) ? (int)$raw['threshold'] : 0;

    // Nivel 3
    $sr = isset($raw['sampleRateHz']) ? (float)$raw['sampleRateHz'] : 50.0;
    $mdRawSamples = $raw['mdRawSamples'] ?? [];
    $jumpIdx = $raw['jumpSampleIndices'] ?? [];

    // delta flexão por sessão (preferir coluna; fallback raw)
    $flexDelta = 0;
    if (!empty($r['flexao_max'])) $flexDelta = (int)$r['flexao_max'];
    else if (isset($raw['flexDeltaMax'])) $flexDelta = (int)$raw['flexDeltaMax'];
    else if (isset($raw['maxDelta'])) $flexDelta = (int)$raw['maxDelta'];

    $sessions[] = [
        'resultadoId' => (int)$r['resultado_id'],
        'startedAt'   => (string)$r['iniciou_em'],
        'durationMs'  => $durationMs,
        'forcaMax'    => (float)$r['forca_max'],
        'flexDelta'   => $flexDelta,

        'threshold'   => $threshold,
        'coinForces'  => array_map('intval', $coinForces),

        'sampleRateHz' => $sr,
        'mdRawSamples' => $mdRawSamples,
        'jumpIdx'      => $jumpIdx,

        'raw' => $raw,
    ];
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <title>Relatório — <?= h((string)$pac['nome']) ?> — <?= h($day) ?> — Nível <?= (int)$nivelId ?></title>

  <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    .muted{ color:#6b7280; }
    .report-wrap{ max-width: 900px; margin: 0 auto; }
    .report-actions{ display:flex; gap:10px; justify-content:flex-end; margin-bottom: 12px; }
    .chart-wrap{ height: 320px; }

    /* esconder sidebar/navbar e deixar formato limpo */
    .layout{ display:block; }
    .sidebar, .top-navbar{ display:none !important; }
    .main-area{ display:block; }
    .conteudo{ padding: 18px; }

    @media print{
      .report-actions{ display:none !important; }
      .card{ break-inside: avoid; page-break-inside: avoid; }
      body{ background:#fff; }
    }
  </style>
</head>

<body>
<div class="layout">
  <div class="main-area">
    <main class="conteudo report-wrap">

      <div class="report-actions">
        <button class="btn-primary" type="button" onclick="window.print()">Descarregar PDF</button>
        <button class="btn-primary" type="button" onclick="window.close()">Fechar</button>
      </div>

      <div class="page-header">
        <h1 class="page-title">Relatório diário — Nível <?= (int)$nivelId ?></h1>
        <p class="page-subtitle">
          <?= h((string)$pac['nome']) ?>
          <span class="muted">(<?= h((string)$pac['email']) ?>)</span><br>
          Dia: <strong><?= h($day) ?></strong> — Sessões: <strong><?= count($sessions) ?></strong>
        </p>
      </div>

      <?php if (empty($sessions)): ?>

        <section class="card">
          <p class="empty-state">Não há sessões nesse dia.</p>
        </section>

      <?php else: ?>

        <?php if ($nivelId === 1): ?>

          <section class="card">
            <h2 class="card-title">Tempo por sessão</h2>
            <p class="muted">Sessões apenas desse dia.</p>
            <div class="chart-wrap"><canvas id="chartTempo"></canvas></div>
          </section>

          <section class="card" style="margin-top:16px;">
            <h2 class="card-title">Força nas 10 moedas (última sessão do dia)</h2>
            <p class="muted">Linha de referência = threshold (por defeito 300N).</p>
            <div class="chart-wrap"><canvas id="chartForcaN1"></canvas></div>
          </section>

        <?php elseif ($nivelId === 2): ?>

          <section class="card">
            <h2 class="card-title">Tempo por sessão</h2>
            <p class="muted">Sessões apenas desse dia.</p>
            <div class="chart-wrap"><canvas id="chartTempo2"></canvas></div>
          </section>

          <section class="card" style="margin-top:16px;">
            <h2 class="card-title">Força nas 10 moedas (última sessão do dia)</h2>
            <p class="muted">Mostra apenas a linha com os valores guardados em <code>coinForces</code>.</p>
            <div class="chart-wrap"><canvas id="chartForcaN2"></canvas></div>
          </section>

        <?php else: ?>

          <section class="card">
            <h2 class="card-title">Nível 3 — Tempo por sessão</h2>
            <p class="muted">Sessões apenas desse dia.</p>
            <div class="chart-wrap"><canvas id="chartTempo3"></canvas></div>
          </section>

          <section class="card" style="margin-top:16px;">
            <h2 class="card-title">Nível 3 — Δ máximo de flexão por sessão</h2>
            <p class="muted">Máximo Δ registado durante o nível (por sessão).</p>
            <div class="chart-wrap"><canvas id="chartFlexMax3"></canvas></div>
          </section>

          <section class="card" style="margin-top:16px;">
            <h2 class="card-title">Nível 3 — Flexão ao longo do nível </h2>
            <p class="muted">Curva + pontos a marcar saltos.</p>
            <div class="chart-wrap"><canvas id="chartFlex3"></canvas></div>
          </section>

        <?php endif; ?>

        <section class="card" style="margin-top:16px;">
          <h2 class="card-title">Resumo</h2>

          <div class="table-wrapper">
            <table class="tabela-utentes">
              <thead>
                <tr>
                  <th>Sessão</th>
                  <th>Tempo (s)</th>

                  <?php if ($nivelId === 3): ?>
                    <th>Δ flexão máx.</th>
                  <?php else: ?>
                    <th>Força máx. (N)</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($sessions as $s): ?>
                <tr>
                  <td><?= h((string)$s['startedAt']) ?></td>
                  <td><?= (int)round(((int)$s['durationMs'])/1000) ?></td>

                  <?php if ($nivelId === 3): ?>
                    <td><?= (int)$s['flexDelta'] ?></td>
                  <?php else: ?>
                    <td><?= (int)round((float)$s['forcaMax']) ?></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

      <?php endif; ?>

    </main>
  </div>
</div>

<script>
const nivel = <?= (int)$nivelId ?>;
const sessions = <?= json_encode($sessions, JSON_UNESCAPED_UNICODE) ?>;

function msToSec(ms){ return Math.round((ms||0)/1000); }

function renderTempo(canvasId){
  const el = document.getElementById(canvasId);
  if (!el) return;

  const labels = sessions.map(s => s.startedAt);
  const data = sessions.map(s => msToSec(s.durationMs));

  new Chart(el, {
    type: 'line',
    data: { labels, datasets: [{ label:'Tempo (s)', data, tension:0.25, pointRadius:4 }] },
    options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
  });
}

function lastSession(){
  if (!sessions.length) return null;
  return sessions[sessions.length - 1];
}

function downsample(arr, maxPoints){
  if (!Array.isArray(arr)) return [];
  if (arr.length <= maxPoints) return arr;
  const step = Math.ceil(arr.length / maxPoints);
  const out = [];
  for (let i = 0; i < arr.length; i += step) out.push(arr[i]);
  return out;
}

// -------- NÍVEL 1 --------
if (nivel === 1) {
  renderTempo('chartTempo');

  const last = lastSession();
  if (last) {
    const labels = ['1','2','3','4','5','6','7','8','9','10'];
    const threshold = (last.threshold || 300);
    const thresholdLine = new Array(10).fill(threshold);
    const forces = (last.coinForces || []).map(v => parseInt(v||0,10));

    const el = document.getElementById('chartForcaN1');
    if (el) {
      new Chart(el, {
        type:'line',
        data:{
          labels,
          datasets:[
            { label:`Linha referência (${threshold}N)`, data: thresholdLine, tension:0, pointRadius:0 },
            { label:'Força por moeda (N)', data: forces, tension:0.25, pointRadius:4 }
          ]
        },
        options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
      });
    }
  }
}

// -------- NÍVEL 2 --------
if (nivel === 2) {
  renderTempo('chartTempo2');

  const last = lastSession();
  if (last) {
    const el = document.getElementById('chartForcaN2');
    if (el) {
      const labels = ['1','2','3','4','5','6','7','8','9','10'];
      const forces = (last.coinForces || []).map(v => parseInt(v||0,10));

      new Chart(el, {
        type:'line',
        data:{ labels, datasets:[ { label:'Força por moeda (N)', data: forces, tension:0.25, pointRadius:4 } ] },
        options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
      });
    }
  }
}

// -------- NÍVEL 3 --------
if (nivel === 3) {
  renderTempo('chartTempo3');

  // Δ máximo por sessão (do dia)
  const elFlexMax = document.getElementById('chartFlexMax3');
  if (elFlexMax) {
    const labels = sessions.map(s => s.startedAt);
    const data = sessions.map(s => parseInt(s.flexDelta || 0, 10));

    new Chart(elFlexMax, {
      type:'line',
      data:{ labels, datasets:[ { label:'Δ flexão (máximo)', data, tension:0.25, pointRadius:4 } ] },
      options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  // Flexão ao longo da última sessão do dia (MD raw) + saltos grandes
  const elFlex3 = document.getElementById('chartFlex3');
  const last = lastSession();

  if (elFlex3 && last) {
    const sr = last.sampleRateHz || 50;
    const raw = Array.isArray(last.mdRawSamples) ? last.mdRawSamples.map(v => parseInt(v||0,10)) : [];
    const jumps = Array.isArray(last.jumpIdx) ? last.jumpIdx.map(v => parseInt(v||0,10)) : [];
    const n = raw.length;

    const labels = Array.from({length:n}, (_,i) => (i / sr).toFixed(2));

    const MAX_POINTS = 2000;
    const labelsDs = downsample(labels, MAX_POINTS);
    const rawDs = downsample(raw, MAX_POINTS);

    const jumpPoints = new Array(labelsDs.length).fill(null);
    if (jumps.length > 0 && n > 0) {
      const step = Math.ceil(n / Math.max(1, labelsDs.length));
      jumps.forEach(j => {
        const dsIndex = Math.floor(j / step);
        if (dsIndex >= 0 && dsIndex < jumpPoints.length) {
          jumpPoints[dsIndex] = rawDs[dsIndex] ?? null;
        }
      });
    }

    new Chart(elFlex3, {
      type:'line',
      data:{
        labels: labelsDs,
        datasets:[
          { label:'Flexão', data: rawDs, tension:0.15, pointRadius:0 },
          { label:'Saltos', data: jumpPoints, showLine:false, pointRadius:10, pointHoverRadius:12, pointHitRadius:14 }
        ]
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
          tooltip:{
            callbacks:{
              label: (ctx) => {
                if (ctx.dataset.label === 'Saltos') return ` Salto — flexão: ${ctx.parsed.y}`;
                return ` Flexão: ${ctx.parsed.y}`;
              }
            }
          }
        },
        scales:{
          x:{ title:{ display:true, text:'Tempo (s)' } },
          y:{ beginAtZero:true }
        }
      }
    });
  }
}
</script>
</body>
</html>