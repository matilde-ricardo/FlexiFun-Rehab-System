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
if ($pacienteId <= 0) { header('Location: utentes.php'); exit; }

$nivel = (int)($_GET['nivel'] ?? 1);
if (!in_array($nivel, [1,2,3], true)) $nivel = 1;

// Confirmar paciente pertence ao fisio + nome/email
$stmtP = $pdo->prepare("
    SELECT p.user_id, p.nome, u.email
    FROM paciente p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = :pid AND p.fisioterapeuta_id = :fid
    LIMIT 1
");
$stmtP->execute([':pid' => $pacienteId, ':fid' => $idTerapeutaUser]);
$pac = $stmtP->fetch(PDO::FETCH_ASSOC);

if (!$pac) { header('Location: utentes.php'); exit; }

// Buscar resultados do nível selecionado
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
    WHERE s.paciente_id = :pid AND s.fisioterapeuta_id = :fid AND rn.nivel_id = :nid
    ORDER BY s.iniciou_em ASC
");
$stmt->execute([':pid' => $pacienteId, ':fid' => $idTerapeutaUser, ':nid' => $nivel]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sessions = [];
foreach ($rows as $r) {
    $raw = [];
    if (!empty($r['raw_data'])) $raw = json_decode((string)$r['raw_data'], true) ?: [];

    $durationMs = 0;
    if (isset($raw['durationMs'])) $durationMs = (int)$raw['durationMs'];
    else if (!empty($r['tempo_reacao_ms'])) $durationMs = (int)$r['tempo_reacao_ms'];

    $coinForces = $raw['coinForces'] ?? array_fill(0, 10, 0);
    if (!is_array($coinForces) || count($coinForces) !== 10) $coinForces = array_fill(0, 10, 0);

    $threshold = isset($raw['threshold']) ? (int)$raw['threshold'] : 300;

    // nível 3
    $sr = isset($raw['sampleRateHz']) ? (float)$raw['sampleRateHz'] : 50.0;
    $mdRawSamples = $raw['mdRawSamples'] ?? [];
    $mdDeltaSamples = $raw['mdDeltaSamples'] ?? [];
    $jumpIdx = $raw['jumpSampleIndices'] ?? [];

    // delta flexão por sessão (para o gráfico de cima)
    $flexDelta = 0;
    if (!empty($r['flexao_max'])) $flexDelta = (int)$r['flexao_max'];
    else if (isset($raw['flexDeltaMax'])) $flexDelta = (int)$raw['flexDeltaMax'];
    else if (isset($raw['maxDelta'])) $flexDelta = (int)$raw['maxDelta'];

    $sessions[] = [
        'resultadoId' => (int)$r['resultado_id'],
        'startedAt'   => (string)$r['iniciou_em'],
        'durationMs'  => $durationMs,
        'forcaMax'    => (float)$r['forca_max'],
        'threshold'   => $threshold,
        'coinForces'  => array_map('intval', $coinForces),

        // nível 3
        'flexDelta'      => $flexDelta,
        'sampleRateHz'   => $sr,
        'mdRawSamples'   => $mdRawSamples,
        'mdDeltaSamples' => $mdDeltaSamples,   // mantém para compatibilidade, mas não usamos no último gráfico
        'jumpIdx'        => $jumpIdx,

        'raw' => $raw,
    ];
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Evolução - <?= h((string)$pac['nome']) ?></title>

    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
      .muted{ color:#6b7280; }
      .chart-wrap{ height: 320px; }

      .level-tabs{
        display:flex; gap:10px; flex-wrap:wrap; margin-top: 6px;
      }
      .level-tab{
        display:inline-flex; align-items:center; justify-content:center;
        padding: 8px 14px; border-radius: 999px; text-decoration:none;
        font-weight: 700; font-size: .9rem;
        border: 1px solid rgba(0,0,0,.08);
        background: rgba(255,255,255,.7);
        color: #111827; transition: .15s ease;
      }
      .level-tab:hover{ transform: translateY(-1px); box-shadow: 0 10px 24px rgba(0,0,0,.06); }
      .level-tab.active{
        background: rgba(115,187,123,0.14);
        border-color: rgba(115,187,123,0.45);
        color: #166534;
      }

      .report-bar{
        display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap;
      }
      .report-bar .filter-group{ min-width: 220px; }

      .form-control{
        width: 100%; padding: 8px 10px; border-radius: 10px;
        border: 1px solid #d1d5db; font-family: inherit; font-size: 0.9rem;
        color: #111827; background: #f9fafb;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
      }
      .form-control:focus{
        outline:none; border-color: var(--verde); background:#fff;
        box-shadow: 0 0 0 2px rgba(115, 187, 123, 0.25);
      }

      .topbar{
        display:flex; align-items:flex-start; justify-content:space-between;
        gap:12px; flex-wrap:wrap;
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
                <h1 class="page-title">Evolução do utente</h1>
                <p class="page-subtitle">
                    <?= h((string)$pac['nome']) ?>
                    <span class="muted">(<?= h((string)$pac['email']) ?>)</span>
                </p>

                <div class="level-tabs">
                  <a class="level-tab <?= $nivel===1?'active':'' ?>" href="evolucao.php?id=<?= (int)$pacienteId ?>&nivel=1">Nível 1</a>
                  <a class="level-tab <?= $nivel===2?'active':'' ?>" href="evolucao.php?id=<?= (int)$pacienteId ?>&nivel=2">Nível 2</a>
                  <a class="level-tab <?= $nivel===3?'active':'' ?>" href="evolucao.php?id=<?= (int)$pacienteId ?>&nivel=3">Nível 3</a>
                </div>
            </div>

            <section class="card">
              <div class="report-bar">
                <div class="filter-group">
                  <label for="reportDate">Dia do relatório</label>
                  <input type="date" id="reportDate" class="form-control">
                </div>

                <div class="filter-actions">
                  <button id="btnOpenReport" class="btn-primary" type="button">
                    Relatório (PDF) — Nível <?= (int)$nivel ?>
                  </button>
                </div>

                <div class="results-count" id="reportInfo"></div>
              </div>
              <p class="empty-state" style="margin-top:10px;">
                Dica: o PDF é feito pelo navegador (abre o relatório e escolhe “Guardar como PDF”).
              </p>
            </section>

            <?php if ($nivel === 1): ?>

              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 1 — Tempo por sessão</h2>
                          <p class="muted">Cada ponto é uma sessão (cada vez que o jogo é jogado).</p>
                      </div>
                  </div>
                  <div class="chart-wrap"><canvas id="chartTempo"></canvas></div>
              </section>

              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 1 — Força nas 10 moedas</h2>
                          <p class="muted">Linha de referência: 300N. Escolhe uma sessão para ver os 10 pontos.</p>
                      </div>

                      <div style="min-width: 320px;">
                          <label for="sessionSelect" class="muted">Sessão</label><br>
                          <select id="sessionSelect" class="form-control"></select>
                      </div>
                  </div>

                  <div class="chart-wrap"><canvas id="chartForca"></canvas></div>
              </section>

              <?php if (empty($sessions)): ?>
                  <p class="empty-state">Ainda não há resultados guardados para este utente no Nível 1.</p>
              <?php endif; ?>

            <?php elseif ($nivel === 2): ?>

              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 2 — Tempo por sessão</h2>
                          <p class="muted">Cada ponto é uma sessão (cada vez que o jogo é jogado).</p>
                      </div>
                  </div>
                  <div class="chart-wrap"><canvas id="chartTempo2"></canvas></div>
              </section>

              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 2 — Força nas 10 moedas</h2>
                          <p class="muted">Mostra a força registada em cada moeda (10 pontos).</p>
                      </div>

                      <div style="min-width: 320px;">
                          <label for="sessionSelect2" class="muted">Sessão</label><br>
                          <select id="sessionSelect2" class="form-control"></select>
                      </div>
                  </div>

                  <div class="chart-wrap"><canvas id="chartForca2"></canvas></div>
              </section>

              <?php if (empty($sessions)): ?>
                  <p class="empty-state">Ainda não há resultados guardados para este utente no Nível 2.</p>
              <?php endif; ?>

            <?php else: ?>

              <!-- NÍVEL 3 -->
              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 3 — Tempo por sessão</h2>
                          <p class="muted">Cada ponto é uma sessão (cada vez que o jogo é jogado).</p>
                      </div>
                  </div>
                  <div class="chart-wrap"><canvas id="chartTempo3"></canvas></div>
              </section>

              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 3 — Δ máximo de flexão por sessão</h2>
                          <p class="muted">Evolução entre sessões (máximo Δ registado durante o nível).</p>
                      </div>
                  </div>
                  <div class="chart-wrap"><canvas id="chartFlexMax3"></canvas></div>
              </section>

              <section class="card">
                  <div class="topbar">
                      <div>
                          <h2 class="card-title">Nível 3 — Flexão ao longo do nível </h2>
                          <p class="muted">Seleciona uma sessão para ver flexão + saltos (pontos grandes).</p>
                      </div>

                      <div style="min-width: 320px;">
                          <label for="sessionSelect3" class="muted">Sessão</label><br>
                          <select id="sessionSelect3" class="form-control"></select>
                      </div>
                  </div>
                  <div class="chart-wrap"><canvas id="chartFlex3"></canvas></div>
              </section>

              <?php if (empty($sessions)): ?>
                  <p class="empty-state">Ainda não há resultados guardados para este utente no Nível 3.</p>
              <?php endif; ?>

            <?php endif; ?>

        </main>
    </div>
</div>

<script>
const nivelAtual = <?= (int)$nivel ?>;
const pacienteId = <?= (int)$pacienteId ?>;
const sessions = <?= json_encode($sessions, JSON_UNESCAPED_UNICODE) ?>;

// helpers
function toDateOnly(isoStr){ return (isoStr || '').slice(0,10); }
function sessionsOfDay(dayStr){ return sessions.filter(s => toDateOnly(s.startedAt) === dayStr); }
function todayStr(){
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth()+1).padStart(2,'0');
  const dd = String(d.getDate()).padStart(2,'0');
  return `${y}-${m}-${dd}`;
}
function msToMinSec(ms) {
  const totalSec = Math.round((ms || 0) / 1000);
  const m = Math.floor(totalSec / 60);
  const s = totalSec % 60;
  return `${m}:${String(s).padStart(2,'0')}`;
}

// report
const reportDate = document.getElementById('reportDate');
const btnOpenReport = document.getElementById('btnOpenReport');
const reportInfo = document.getElementById('reportInfo');

function updateReportInfo(dayStr){
  const list = sessionsOfDay(dayStr);
  if (reportInfo) reportInfo.textContent = `Sessões em ${dayStr}: ${list.length}`;
}
function openReport(dayStr){
  const url = `evolucao_relatorio.php?id=${pacienteId}&day=${encodeURIComponent(dayStr)}&nivel=${nivelAtual}`;
  window.open(url, '_blank');
}
if (reportDate){
  reportDate.value = todayStr();
  updateReportInfo(reportDate.value);
  reportDate.addEventListener('change', () => updateReportInfo(reportDate.value));
}
if (btnOpenReport){
  btnOpenReport.addEventListener('click', () => {
    const day = reportDate?.value || todayStr();
    openReport(day);
  });
}

// ---------------------
// NÍVEL 1
// ---------------------
if (nivelAtual === 1) {
  const tempoLabels = sessions.map(s => s.startedAt);
  const tempoData = sessions.map(s => Math.round((s.durationMs || 0) / 1000));
  const ctxTempo = document.getElementById('chartTempo');

  if (ctxTempo) {
    new Chart(ctxTempo, {
      type: 'line',
      data: { labels: tempoLabels, datasets: [{ label:'Tempo (segundos)', data: tempoData, tension:0.25, pointRadius:4 }] },
      options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  const sessionSelect = document.getElementById('sessionSelect');
  const ctxForca = document.getElementById('chartForca');
  let chartForca = null;

  function populateSelect() {
    if (!sessionSelect) return;
    sessionSelect.innerHTML = '';
    sessions.forEach((s, idx) => {
      const opt = document.createElement('option');
      opt.value = idx;
      opt.textContent = `${s.startedAt} (max ${Math.round(s.forcaMax)}N, tempo ${msToMinSec(s.durationMs)})`;
      sessionSelect.appendChild(opt);
    });
  }

  function renderForceChart(idx) {
    const s = sessions[idx];
    if (!s || !ctxForca) return;

    const labels = ['1','2','3','4','5','6','7','8','9','10'];
    const thresholdLine = new Array(10).fill(s.threshold || 300);
    const forces = (s.coinForces || []).map(v => parseInt(v || 0, 10));

    if (chartForca) chartForca.destroy();

    chartForca = new Chart(ctxForca, {
      type: 'line',
      data: { labels, datasets: [
        { label:`Linha referência (${s.threshold || 300}N)`, data: thresholdLine, tension:0, pointRadius:0 },
        { label:'Força por moeda (N)', data: forces, tension:0.25, pointRadius:4 }
      ]},
      options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  if (sessions.length > 0) {
    populateSelect();
    renderForceChart(0);
    sessionSelect?.addEventListener('change', (e) => renderForceChart(parseInt(e.target.value,10)));
  }
}

// ---------------------
// NÍVEL 2
// ---------------------
if (nivelAtual === 2) {
  const tempoLabels2 = sessions.map(s => s.startedAt);
  const tempoData2 = sessions.map(s => Math.round((s.durationMs || 0) / 1000));
  const ctxTempo2 = document.getElementById('chartTempo2');

  if (ctxTempo2) {
    new Chart(ctxTempo2, {
      type: 'line',
      data: { labels: tempoLabels2, datasets: [{ label:'Tempo (segundos)', data: tempoData2, tension:0.25, pointRadius:4 }] },
      options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  const sessionSelect2 = document.getElementById('sessionSelect2');
  const ctxForca2 = document.getElementById('chartForca2');
  let chartForca2 = null;

  function populateSelect2() {
    if (!sessionSelect2) return;
    sessionSelect2.innerHTML = '';
    sessions.forEach((s, idx) => {
      const opt = document.createElement('option');
      opt.value = idx;
      opt.textContent = `${s.startedAt} (max ${Math.round(s.forcaMax)}N, tempo ${msToMinSec(s.durationMs)})`;
      sessionSelect2.appendChild(opt);
    });
  }

  function renderForceChart2(idx) {
    const s = sessions[idx];
    if (!s || !ctxForca2) return;

    const labels = ['1','2','3','4','5','6','7','8','9','10'];
    const forces = (s.coinForces || []).map(v => parseInt(v || 0, 10));

    if (chartForca2) chartForca2.destroy();

    chartForca2 = new Chart(ctxForca2, {
      type: 'line',
      data: { labels, datasets: [{ label:'Força por moeda (N)', data: forces, tension:0.25, pointRadius:4 }] },
      options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  if (sessions.length > 0) {
    populateSelect2();
    renderForceChart2(0);
    sessionSelect2?.addEventListener('change', (e) => renderForceChart2(parseInt(e.target.value,10)));
  }
}

// ---------------------
// NÍVEL 3
// ---------------------
if (nivelAtual === 3) {

  // tempo por sessão
  const tempoLabels3 = sessions.map(s => s.startedAt);
  const tempoData3 = sessions.map(s => Math.round((s.durationMs || 0) / 1000));
  const ctxTempo3 = document.getElementById('chartTempo3');

  if (ctxTempo3) {
    new Chart(ctxTempo3, {
      type: 'line',
      data: { labels: tempoLabels3, datasets: [{ label:'Tempo (segundos)', data: tempoData3, tension:0.25, pointRadius:4 }] },
      options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  // Δ máximo por sessão (já mostra variação máxima)
  const flexLabels = sessions.map(s => s.startedAt);
  const flexData = sessions.map(s => parseInt(s.flexDelta || 0, 10));
  const ctxFlexMax = document.getElementById('chartFlexMax3');

  if (ctxFlexMax) {
    new Chart(ctxFlexMax, {
      type: 'line',
      data: { labels: flexLabels, datasets: [{ label:'Δ flexão (máximo)', data: flexData, tension:0.25, pointRadius:4 }] },
      options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
    });
  }

  // gráfico dentro da sessão: SÓ flexão + saltos grandes
  const sessionSelect3 = document.getElementById('sessionSelect3');
  const ctxFlex3 = document.getElementById('chartFlex3');
  let chartFlex3 = null;

  function populateSelect3() {
    if (!sessionSelect3) return;
    sessionSelect3.innerHTML = '';
    sessions.forEach((s, idx) => {
      const n = (s.mdRawSamples?.length || 0);
      const opt = document.createElement('option');
      opt.value = idx;
      opt.textContent = `${s.startedAt} (${n} amostras)`;
      sessionSelect3.appendChild(opt);
    });
  }

  function downsample(arr, maxPoints) {
    if (!Array.isArray(arr)) return [];
    if (arr.length <= maxPoints) return arr;
    const step = Math.ceil(arr.length / maxPoints);
    const out = [];
    for (let i = 0; i < arr.length; i += step) out.push(arr[i]);
    return out;
  }

  function renderFlexSession(idx) {
    const s = sessions[idx];
    if (!s || !ctxFlex3) return;

    const sr = s.sampleRateHz || 50;
    const raw = Array.isArray(s.mdRawSamples) ? s.mdRawSamples.map(v => parseInt(v||0,10)) : [];
    const jumps = Array.isArray(s.jumpIdx) ? s.jumpIdx.map(v => parseInt(v||0,10)) : [];

    const n = raw.length;
    const labels = Array.from({length:n}, (_,i) => (i / sr).toFixed(2));

    // mantém leve
    const MAX_POINTS = 2000;
    const labelsDs = downsample(labels, MAX_POINTS);
    const rawDs = downsample(raw, MAX_POINTS);

    // pontos de salto na altura da flexão
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

    if (chartFlex3) chartFlex3.destroy();

    chartFlex3 = new Chart(ctxFlex3, {
      type: 'line',
      data: {
        labels: labelsDs,
        datasets: [
          {
            label:'Flexão',
            data: rawDs,
            tension:0.15,
            pointRadius:0
          },
          {
            label:'Saltos',
            data: jumpPoints,
            showLine:false,
            pointRadius:10,
            pointHoverRadius:12,
            pointHitRadius:14
          }
        ]
      },
      options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins: {
          tooltip: {
            callbacks: {
              label: (ctx) => {
                if (ctx.dataset.label === 'Saltos') return ` Salto — flexão: ${ctx.parsed.y}`;
                return ` Flexão: ${ctx.parsed.y}`;
              }
            }
          }
        },
        scales: {
          x: { title: { display:true, text:'Tempo (s)' } },
          y: { beginAtZero:true }
        }
      }
    });
  }

  if (sessions.length > 0) {
    populateSelect3();
    renderFlexSession(0);
    sessionSelect3?.addEventListener('change', (e) => renderFlexSession(parseInt(e.target.value,10)));
  }
}
</script>
</body>
</html>