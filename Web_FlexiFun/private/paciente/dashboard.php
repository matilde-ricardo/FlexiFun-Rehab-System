<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'paciente') {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

require_once __DIR__ . '/../db.php';

$userId = (int)$_SESSION['user_id'];

// Dados do paciente
$stmt = $pdo->prepare("
    SELECT p.nome, u.email
    FROM paciente p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = :id
    LIMIT 1
");
$stmt->execute([':id' => $userId]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    $paciente = ['nome' => 'Paciente', 'email' => '—'];
}

$_SESSION['nome_paciente'] = (string)$paciente['nome'];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FlexiFun – Paciente</title>

    <!-- Fonte -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- CSS privado -->
    <link rel="stylesheet" href="/FlexiFun/private/assets/css/private.css">
</head>

<body>
<div class="layout">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main">
        <?php include __DIR__ . '/includes/navbar.php'; ?>

        <div class="conteudo">

            <div class="patient-wrap">

                <div class="page-header patient-center">
                    <h1 class="page-title">Olá, <?= htmlspecialchars((string)$paciente['nome'], ENT_QUOTES, 'UTF-8') ?> 👋</h1>

                    <!-- ID por baixo do nome -->
                    <p class="page-subtitle" style="margin-top:6px;">
                        <strong>ID:</strong> <?= (int)$userId ?>
                    </p>

                    <p class="page-subtitle" style="margin-top:6px;">
                        Clica nas imagens para veres as explicações do jogo e dos controlos 🧤🎮
                    </p>
                </div>

                <section class="card">
                    <div class="card-header-row patient-center">
                        <h2 class="card-title">Como jogar</h2>
                    </div>

                    <div class="guide3-grid">

                        <div class="guide3-item">
                            <button type="button" class="guide3-card" data-panel="p1" aria-expanded="false">
                                <img class="guide3-img" src="/FlexiFun/private/assets/img/menu.jpeg" alt="Menu do jogo">
                                <div class="guide3-title">
                                    <i class="bi bi-list-ul"></i> Menu do jogo
                                </div>
                            </button>

                            <div class="guide3-panel" id="p1" hidden>
                                <p>O FlexiFun tem <strong>3 níveis</strong> e a dificuldade aumenta em cada um:</p>
                                <ul>
                                    <li><strong>Nível 1:</strong> começar e aprender.</li>
                                    <li><strong>Nível 2:</strong> mais desafios.</li>
                                    <li><strong>Nível 3:</strong> desafio máximo.</li>
                                </ul>
                                <p class="guide3-note">Segue o plano do teu terapeuta.</p>
                            </div>
                        </div>

                        <div class="guide3-item">
                            <button type="button" class="guide3-card" data-panel="p2" aria-expanded="false">
                                <img class="guide3-img" src="/FlexiFun/private/assets/img/jogo.jpeg" alt="Personagem no mapa">
                                <div class="guide3-title">
                                    <i class="bi bi-map"></i> Personagem no mapa
                                </div>
                            </button>

                            <div class="guide3-panel" id="p2" hidden>
                                <p>No mapa vais precisar de:</p>
                                <ul>
                                    <li><strong>Força</strong> para agarrar/ativar ações.</li>
                                    <li><strong>Saltar</strong> para passar obstáculos.</li>
                                    <li><strong>Virar</strong> para apanhar itens e evitar barreiras.</li>
                                </ul>
                                <p class="guide3-note">Joga com calma e com atenção.</p>
                            </div>
                        </div>

                        <div class="guide3-item">
                            <button type="button" class="guide3-card" data-panel="p3" aria-expanded="false">
                                <img class="guide3-img" src="/FlexiFun/private/assets/img/controlos.jpeg" alt="Controlos da luva">
                                <div class="guide3-title">
                                    <i class="bi bi-hand-index-thumb"></i> Controlos
                                </div>
                            </button>

                            <div class="guide3-panel" id="p3" hidden>
                                <p>Os controlos da luva:</p>
                                <ul>
                                    <li><strong>Rodar</strong> a mão para direção.</li>
                                    <li><strong>Fazer força</strong> para agarrar/ativar.</li>
                                    <li><strong>Esticar dedos</strong> para ações do jogo.</li>
                                </ul>
                                <p class="guide3-note">Se algo falhar, confirma a luva.</p>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- REMOVIDO: stats-grid (Sessões jogadas / Níveis completos / Última sessão) -->

                <section class="card patient-center-block">
                    <div class="card-header-row patient-center">
                        <h2 class="card-title">Dicas importantes</h2>
                    </div>

                    <div class="tips-grid">
                        <div class="tip">
                            <strong>Joga com calma</strong>
                            <div>Faz os movimentos devagar e com atenção.</div>
                        </div>
                        <div class="tip">
                            <strong>Faz pausas</strong>
                            <div>Se cansares, descansa um pouco e volta depois.</div>
                        </div>
                        <div class="tip">
                            <strong>Se doer, pára</strong>
                            <div>Se sentires dor, termina e avisa o terapeuta.</div>
                        </div>
                    </div>
                </section>

            </div><!-- /patient-wrap -->
        </div>
    </main>
</div>

<script>
(function () {
  const cards = document.querySelectorAll('.guide3-card');

  cards.forEach(card => {
    card.addEventListener('click', () => {
      const panelId = card.getAttribute('data-panel');
      const panel = document.getElementById(panelId);
      if (!panel) return;

      const isOpen = !panel.hidden;
      panel.hidden = isOpen;
      card.setAttribute('aria-expanded', String(!isOpen));
    });
  });
})();
</script>

</body>
</html>