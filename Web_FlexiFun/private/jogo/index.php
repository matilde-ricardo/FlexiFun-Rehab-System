<?php
// Se precisares, podes ligar a BD aqui com require 'db.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>FlexiFun - Jogo do Saco</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(180deg, #74b9ff 0%, #a29bfe 40%, #ffeaa7 100%);
    }

    /* CENA DO JOGO EM ECRÃ INTEIRO */
    .game-scene {
      position: relative;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
    }

    /* Céu / nuvens */
    .cloud {
      position: absolute;
      background: #fff;
      border-radius: 50px;
      box-shadow:
        20px 10px 0 #fff,
        40px 15px 0 #fff;
      width: 60px;
      height: 30px;
      opacity: 0.9;
      animation: cloud-move 45s linear infinite;
    }

    .cloud.cloud1 { top: 8%; left: -10%; transform: scale(1.4); animation-delay: 0s; }
    .cloud.cloud2 { top: 20%; left: -20%; transform: scale(1.1); animation-delay: 10s; }
    .cloud.cloud3 { top: 32%; left: -15%; transform: scale(1.6); animation-delay: 20s; }

    @keyframes cloud-move {
      0%   { transform: translateX(0) scale(1); }
      100% { transform: translateX(140vw) scale(1); }
    }

    /* Chão */
    .ground {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 150%;
      height: 22%;
      background: linear-gradient(180deg, #55efc4, #00b894);
      border-radius: 50% 50% 0 0;
      transform: translateX(-10%);
      box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.25);
    }

    .ground-detail {
      position: absolute;
      bottom: 7%;
      width: 100%;
      text-align: center;
      font-size: 14px;
      color: #145a32;
      text-shadow: 0 1px 3px rgba(255, 255, 255, 0.4);
    }

    /* HUD */
    .hud {
      position: absolute;
      top: 12px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 16px;
      align-items: center;
      padding: 10px 18px;
      background: rgba(255, 255, 255, 0.85);
      border-radius: 999px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .hud-item {
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .hud-label {
      font-weight: bold;
      color: #2d3436;
    }

    .badge-stars {
      background: #ffe066;
      border-radius: 999px;
      padding: 4px 10px;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .estrela-icon {
      font-size: 18px;
    }

    /* Caixa de missão (no meio de cima) */
    .mission-box {
      position: absolute;
      top: 70px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(255, 255, 255, 0.9);
      border-radius: 16px;
      padding: 10px 14px;
      max-width: 460px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      font-size: 14px;
    }

    .mission-title {
      font-size: 15px;
      font-weight: bold;
      color: #6c5ce7;
      margin-bottom: 4px;
    }

    .mission-text {
      margin-bottom: 4px;
    }

    .mission-limit {
      font-size: 12px;
      color: #636e72;
    }

    .mission-limit strong {
      color: #0984e3;
    }

    /* ÁREA PRINCIPAL (saco, barra, etc) */
    .center-area {
      position: absolute;
      top: 55%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: min(600px, 95vw);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
    }

    .game-title {
      font-size: 28px;
      font-weight: bold;
      color: #ffeaa7;
      text-shadow: 0 3px 8px rgba(0, 0, 0, 0.35);
      margin-bottom: 4px;
    }

    .game-subtitle {
      font-size: 13px;
      color: #fefefe;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.45);
      margin-bottom: 4px;
    }

    .saco-container {
      position: relative;
      width: 240px;
      height: 260px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .saco-shadow {
      position: absolute;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%);
      width: 130px;
      height: 26px;
      background: rgba(0, 0, 0, 0.25);
      filter: blur(8px);
      border-radius: 50%;
    }

    .saco {
      width: 150px;
      height: 190px;
      background: #ffbd73;
      border-radius: 60% 60% 70% 70%;
      position: relative;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
      transition: transform 0.1s ease, background 0.1s ease;
    }

    .saco::before {
      content: "";
      position: absolute;
      top: -20px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 30px;
      background: #ff9f43;
      border-radius: 999px;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    }

    .saco::after {
      content: "";
      position: absolute;
      top: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 40px;
      height: 9px;
      background: #e17055;
      border-radius: 999px;
    }

    .saco.explodiu {
      background: #ff7675;
      transform: scale(1.18) rotate(4deg);
    }

    /* Barra de força */
    .barra {
      width: 100%;
      max-width: 420px;
      height: 22px;
      background: rgba(236, 240, 241, 0.9);
      border-radius: 999px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .barra-preenchida {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #55efc4, #00cec9, #0984e3);
      transition: width 0.12s linear;
    }

    .forca-texto {
      font-size: 14px;
      color: #ffffff;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    }

    .forca-texto span {
      font-weight: bold;
      color: #ffeaa7;
    }

    .mensagem {
      font-size: 15px;
      margin-top: 4px;
      min-height: 24px;
      color: #fefefe;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
      text-align: center;
    }

    .mensagem strong {
      color: #ffd32a;
    }

    .fim-jogo {
      font-size: 18px;
      margin-top: 6px;
      color: #00ffb3;
      font-weight: bold;
      text-align: center;
      text-shadow: 0 2px 6px rgba(0, 0, 0, 0.65);
    }

    .rodape {
      position: absolute;
      bottom: 4px;
      width: 100%;
      text-align: center;
      font-size: 11px;
      color: #f3f3f3;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    }

    /* Estrelas a saltar */
    .estrela-salto {
      position: absolute;
      font-size: 22px;
      animation: saltar 0.9s ease-out forwards;
      pointer-events: none;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.4));
    }

    @keyframes saltar {
      0% {
        transform: translateY(0) scale(0.7) rotate(0deg);
        opacity: 1;
      }
      40% {
        transform: translateY(-50px) scale(1.1) rotate(10deg);
        opacity: 1;
      }
      100% {
        transform: translateY(-100px) scale(0.3) rotate(-20deg);
        opacity: 0;
      }
    }
  </style>
</head>
<body>

  <div class="game-scene" id="area-jogo">

    <!-- NUVENS -->
    <div class="cloud cloud1"></div>
    <div class="cloud cloud2"></div>
    <div class="cloud cloud3"></div>

    <!-- CHÃO -->
    <div class="ground"></div>
    <div class="ground-detail">
      FlexiFun: aperta o saco para ajudar o herói a ganhar estrelas! 🌟
    </div>

    <!-- HUD -->
    <div class="hud">
      <div class="hud-item">
        <span class="hud-label">Nível:</span>
        <span id="nivel-num">1</span>
      </div>
      <div class="hud-item">
        <span class="hud-label">Estrelas:</span>
        <span class="badge-stars">
          <span class="estrela-icon">⭐</span>
          <span id="estrelas-num">0</span>
        </span>
      </div>
    </div>

    <!-- MISSÃO -->
    <div class="mission-box">
      <div class="mission-title">Missão deste nível</div>
      <div class="mission-text" id="texto-nivel">
        Aperta devagar até o saco explodir! 💥
      </div>
      <div class="mission-limit">
        Limite de força: <strong id="limite-nivel">300</strong>
      </div>
    </div>

    <!-- CENTRO DO JOGO -->
    <div class="center-area">
      <div class="game-title">FlexiFun</div>
      <div class="game-subtitle">Aperta o saco com a luva para o fazer explodir e ganhar estrelas!</div>

      <div class="saco-container">
        <div class="saco-shadow"></div>
        <div class="saco" id="saco"></div>
      </div>

      <div class="barra">
        <div class="barra-preenchida" id="barra-preenchida"></div>
      </div>

      <div class="forca-texto">
        Força atual: <span id="forca-valor">0</span>
      </div>

      <div class="mensagem" id="mensagem">
        Aperta o saco para começar! 💪
      </div>

      <div class="fim-jogo" id="fim-jogo" style="display:none;">
        🎉 Parabéns! Completaste todos os níveis! 🎉
      </div>
    </div>

    <div class="rodape">
      Segura o saco com a luva e aperta. O terapeuta pode ajustar os níveis conforme a criança evolui.
    </div>
  </div>

  <script>
    // ---------- CONFIGURAÇÃO DE NÍVEIS (UM VALOR POR NÍVEL) ----------
    const niveis = [
      { forca: 300, estrelas: 1, texto: "Aperta devagarinho até o saco explodir! 💥" },
      { forca: 400, estrelas: 2, texto: "Agora um pouco mais forte... quase um super-herói! 🦸" },
      { forca: 500, estrelas: 3, texto: "Força média! Mostra os teus músculos mágicos! 💪✨" },
      { forca: 600, estrelas: 3, texto: "Uau! Nível avançado, aperta com confiança! 🚀" },
      { forca: 700, estrelas: 4, texto: "Nível super herói! Dá o teu melhor aperto! 🌟" }
    ];

    let indiceNivel = 0;
    let estrelasTotais = 0;
    let podeExplodir = true;
    let jogoTerminado = false;

    const spanNivel = document.getElementById('nivel-num');
    const spanEstrelas = document.getElementById('estrelas-num');
    const spanForca = document.getElementById('forca-valor');
    const barraPreenchida = document.getElementById('barra-preenchida');
    const textoNivel = document.getElementById('texto-nivel');
    const textoLimite = document.getElementById('limite-nivel');
    const divMensagem = document.getElementById('mensagem');
    const divFimJogo = document.getElementById('fim-jogo');
    const saco = document.getElementById('saco');
    const areaJogo = document.getElementById('area-jogo');

    function atualizarTextoNivel() {
      const nivelAtual = niveis[indiceNivel];
      spanNivel.textContent = indiceNivel + 1;
      textoNivel.textContent = nivelAtual.texto;
      textoLimite.textContent = nivelAtual.forca;
    }

    atualizarTextoNivel();

    async function buscarSensor() {
      if (jogoTerminado) return;

      try {
        const resp = await fetch('sensor.json?rand=' + Date.now());
        if (!resp.ok) {
          console.log('Erro ao carregar sensor.json', resp.status);
          return;
        }
        const dados = await resp.json();
        atualizarJogo(dados);
      } catch (e) {
        console.error('Erro no fetch:', e);
      }
    }

    function atualizarJogo(dados) {
      if (jogoTerminado) return;

      const valor = dados.valor || 0;
      const atingiu = dados.atingiu == 1;

      spanForca.textContent = valor;

      const nivelAtual = niveis[indiceNivel];
      const limite = nivelAtual.forca;
      const perc = Math.min(100, (valor / limite) * 100);
      barraPreenchida.style.width = perc + '%';

      if (!atingiu && perc < 30) {
        divMensagem.textContent = "Aperta o saco com cuidado! 💪";
      } else if (!atingiu && perc >= 30 && perc < 80) {
        divMensagem.textContent = "Continua! Quase lá! 😄";
      } else if (!atingiu && perc >= 80) {
        divMensagem.textContent = "Só mais um bocadinho... ⏱️";
      }

      if (atingiu && podeExplodir) {
        // Explosão deste nível
        podeExplodir = false;
        saco.classList.add('explodiu');
        divMensagem.innerHTML = "<strong>BOOM!</strong> Saco explodiu! 🌟";

        // Estrelas a saltar pela cena
        criarEstrelasASaltar(10);

        // Atualizar estrelas
        estrelasTotais += nivelAtual.estrelas;
        spanEstrelas.textContent = estrelasTotais;

        // Se tiveres PHP/BD pronto, podes guardar:
        /*
        guardarProgresso(indiceNivel + 1, nivelAtual.estrelas);
        */

        // Próximo nível
        setTimeout(() => {
          saco.classList.remove('explodiu');
          indiceNivel++;

          if (indiceNivel >= niveis.length) {
            terminarJogo();
          } else {
            podeExplodir = true;
            atualizarTextoNivel();
            divMensagem.textContent = "Novo nível! Aperta outra vez o saco! 🎯";
            barraPreenchida.style.width = '0%';
          }
        }, 1000);
      }
    }

    function criarEstrelasASaltar(qtd) {
      const rect = areaJogo.getBoundingClientRect();
      for (let i = 0; i < qtd; i++) {
        const estrela = document.createElement('div');
        estrela.classList.add('estrela-salto');
        estrela.textContent = '⭐';

        const x = Math.random() * (rect.width - 80) + 40;
        const y = rect.height * 0.55 + Math.random() * 40;

        estrela.style.left = x + 'px';
        estrela.style.top = y + 'px';

        areaJogo.appendChild(estrela);

        setTimeout(() => {
          estrela.remove();
        }, 900);
      }
    }

    function terminarJogo() {
      jogoTerminado = true;
      barraPreenchida.style.width = '100%';
      divMensagem.textContent = "Fantástico! Já não há mais sacos para explodir! 😄";
      divFimJogo.style.display = 'block';
    }

    // Se já tiveres guardar_progresso.php a funcionar:
    /*
    function guardarProgresso(nivel, estrelasGanhas) {
      fetch('guardar_progresso.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'nivel=' + encodeURIComponent(nivel) +
              '&estrelas_ganhas=' + encodeURIComponent(estrelasGanhas)
      })
      .then(r => r.text())
      .then(t => console.log('Resposta PHP:', t))
      .catch(e => console.error(e));
    }
    */

    // Lê o sensor ~10x por segundo
    setInterval(buscarSensor, 100);
  </script>

</body>
</html>
