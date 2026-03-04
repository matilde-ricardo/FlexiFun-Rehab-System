<?php
// Landing page pública (sem BD)
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>FlexiFun</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <link rel="stylesheet" href="assets/css/public.css">
</head>

<body>

  <!-- NAV -->
  <header class="nav" id="topo">
    <div class="nav-inner">
      <a class="brand" href="#topo">
        <img src="assets/img/logo_flexifun.png" alt="FlexiFun">
        <div class="brand-txt">
          <strong>FlexiFun</strong>
          <span>Reabilitar a Jogar</span>
        </div>
      </a>

      <nav class="nav-links">
        <a href="#para-quem">Para quem é</a>
        <a href="#como">Como funciona</a>
        <a href="#arquitetura">Arquitetura</a>
        <a href="#tecnologias">Tecnologias</a>
        <a href="#equipa">Equipa</a>
      </nav>

      <div class="nav-cta">
        <a class="btn btn-primary" href="login.php">
          <i class="bi bi-box-arrow-in-right"></i> Entrar
        </a>
      </div>
    </div>
  </header>

  <!-- HERO -->
  <main class="hero">
    <div class="hero-content">
      <div class="hero-card">
        <div class="hero-badge">
          <i class="bi bi-hand-index-thumb"></i>
          <span>Luva sensorizada</span>
        </div>

        <h1>Treino de reabilitação <span class="accent">em formato de jogo</span>.</h1>

        <p>
          Uma luva capta os movimentos da mão e controla um personagem em Unity.
          A plataforma web regista sessões e progresso para acompanhamento.
        </p>
      </div>
    </div>
  </main>

  <section id="para-quem" class="section section-soft">
  <div class="wrap narrow">
    <div class="section-head">
      <h2>Para quem é o FlexiFun?</h2>
      <p>
        O FlexiFun surge como uma solução que transforma exercícios
        em interações de jogo, promovendo maior envolvimento e tornando
        o processo de reabilitação mais apelativo e motivador.
      </p>
    </div>

    <div class="who-grid">
      <div class="who-item">
        <h3>Pacintes</h3>
        <p>
          Os exercícios de reabilitação são integrados num jogo interativo, onde os movimentos da mão correspondem a ações no ambiente virtual.
          Esta abordagem transforma tarefas repetitivas em desafios com objetivos claros, 
          promovendo maior motivação, envolvimento contínuo e adesão ao plano de reabilitação ao longo do tempo.

        </p>
      </div>


        <h3>Profissionais</h3>
        <p>
          A aplicação web permite acompanhar de forma objetiva o desempenho do paciente em cada sessão de treino.
          Os dados recolhidos facilitam a análise da evolução, apoiam a tomada de decisão clínica e permitem ajustar o nível de dificuldade 
          e os exercícios de acordo com as necessidades individuais.
        </p>
      </div>
    </div>
  </div>
</section>




  <!-- COMO FUNCIONA -->
  <section id="como" class="section">
    <div class="wrap">
      <div class="section-head">
        <h2>Como funciona</h2>
        <p>Três componentes integrados para treino, medição e monitorização.</p>
      </div>

      <div class="grid3">
        <article class="card">
          <div class="icon">🧤</div>
          <h3>Luva sensorizada</h3>
          <p>Capta sinais da mão em tempo real.</p>
        </article>

        <article class="card">
          <div class="icon">🎮</div>
          <h3>Jogo interativo</h3>
          <p>Movimentos → ações no jogo com níveis e objetivos.</p>
        </article>

        <article class="card">
          <div class="icon">📈</div>
          <h3>Plataforma</h3>
          <p>Registo de sessões e evolução ao longo do tempo.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ARQUITETURA -->
  <section id="arquitetura" class="section section-soft">
    <div class="wrap">
      <div class="section-head">
        <h2>Arquitetura</h2>
        <p>Fluxo simples e robusto (modo atual: USB para estabilidade e debug).</p>
      </div>

      <div class="architecture">
        <div class="arch-node">
          <h3>ESP + Luva</h3>
          <p>Aquisição e envio (USB).</p>
        </div>
        <div class="arch-arrow"><i class="bi bi-arrow-right"></i></div>
        <div class="arch-node">
          <h3>Unity (PC)</h3>
          <p>Interpretação → jogo.</p>
        </div>
        <div class="arch-arrow"><i class="bi bi-arrow-right"></i></div>
        <div class="arch-node">
          <h3>Web</h3>
          <p>Contas, sessões e evolução.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TECNOLOGIAS -->
  <section id="tecnologias" class="section">
    <div class="wrap">
      <div class="section-head">
        <h2>Tecnologias</h2>
        <p>Componentes principais do protótipo.</p>
      </div>

      <div class="grid3">
        <article class="card">
          <h3>Hardware</h3>
          <ul class="list">
            <li>Microcontrolador (ESP)</li>
            <li>PCB + sensores</li>
          </ul>
        </article>

        <article class="card">
          <h3>Software</h3>
          <ul class="list">
            <li>Unity (jogo)</li>
            <li>Lógica de níveis</li>
          </ul>
        </article>

        <article class="card">
          <h3>Plataforma</h3>
          <ul class="list">
            <li>Web (PHP/HTML/CSS)</li>
            <li>Base de dados (sessões)</li>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <!-- EQUIPA -->
  <section id="equipa" class="section section-soft">
    <div class="wrap">
      <div class="section-head">
        <h2>Equipa</h2>
        <p>Projeto desenvolvido no âmbito da UC Laboratório de Sistemas Biomédicos.</p>
      </div>

      <div class="team">
        <div class="person">
          <img src="assets/img/ines.jpg" alt="Inês Vieira">
          <h3>Inês Vieira</h3>
        </div>
        <div class="person">
          <img src="assets/img/matilde.jpg" alt="Matilde Ricardo">
          <h3>Matilde Ricardo</h3>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="wrap footer-grid">
      <div>
        <strong>FlexiFun</strong>
        <p>Sistema de reabilitação gamificada da mão.</p>
      </div>
      <div>
        <strong>Contato</strong>
        <p>flexifun@app.com</p>
      </div>
      <div>
        <strong>Privacidade</strong>
        <p>Dados apenas acessíveis a utilizadores autorizados.</p>
      </div>
    </div>
    <div class="wrap footer-copy">© 2025 FlexiFun</div>
  </footer>

</body>
</html>
