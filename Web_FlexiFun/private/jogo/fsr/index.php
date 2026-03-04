<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Jogo do Saco – Luva Robótica</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin-top: 40px;
    }
    #info {
      margin-bottom: 20px;
      font-size: 18px;
    }
    .saco {
      width: 150px;
      height: 200px;
      margin: 0 auto 20px;
      border-radius: 20px;
      background: #ffcc66;
      position: relative;
      transition: transform 0.1s ease, background 0.1s ease;
    }
    .saco.explodiu {
      background: #ff6666;
      transform: scale(1.2) rotate(5deg);
    }
    .barra {
      width: 60%;
      height: 25px;
      background: #ddd;
      margin: 10px auto;
      border-radius: 15px;
      overflow: hidden;
    }
    .barra-preenchida {
      height: 100%;
      width: 0%;
      background: #4caf50;
      transition: width 0.1s linear;
    }
    #mensagem {
      font-size: 20px;
      margin-top: 15px;
      min-height: 24px;
    }
  </style>
</head>
<body>

  <div id="info">
    Nível: <span id="nivel-num">1</span> |
    Estrelas: <span id="estrelas-num">0</span>
  </div>

  <div class="saco" id="saco"></div>

  <div class="barra">
    <div class="barra-preenchida" id="barra-preenchida"></div>
  </div>

  Força atual: <span id="forca-valor">0</span>

  <div id="mensagem"></div>

  <script>
    // --- LÓGICA DO JOGO NO JS ---
    let nivel = 1;
    let limites = [300, 400, 500, 600, 700]; // limite aumenta por nível
    let estrelas = 0;
    let podeExplodir = true; // para não explodir 50x seguidas no mesmo aperto

    async function buscarSensor() {
      try {
        const resp = await fetch('sensor.json?rand=' + Math.random());
        const dados = await resp.json();
        atualizarJogo(dados);
      } catch (e) {
        console.error(e);
      }
    }

    function atualizarJogo(dados) {
      const valor = dados.valor;
      const atingiu = dados.atingiu == 1;

      document.getElementById('forca-valor').textContent = valor;

      const limiteAtual = limites[nivel - 1] || limites[limites.length - 1];
      const perc = Math.min(100, (valor / limiteAtual) * 100);
      document.getElementById('barra-preenchida').style.width = perc + "%";

      const saco = document.getElementById('saco');
      const msg = document.getElementById('mensagem');

      if (atingiu && podeExplodir) {
        // Explodiu o saco neste nível
        podeExplodir = false;

        saco.classList.add('explodiu');
        msg.textContent = "Boom! Saco explodiu! 🌟";

        const estrelasGanhas = 3; // por exemplo, 3 estrelas por nível
        estrelas += estrelasGanhas;
        document.getElementById('estrelas-num').textContent = estrelas;

        // Enviar para PHP guardar na BD
        guardarProgresso(nivel, estrelasGanhas);

        // Passa para o próximo nível depois de 1 segundo
        setTimeout(() => {
          saco.classList.remove('explodiu');
          nivel++;
          document.getElementById('nivel-num').textContent = nivel;
          msg.textContent = "Novo nível! Aperta mais forte!";
          podeExplodir = true;
        }, 1000);
      }

      if (!atingiu && perc < 50) {
        msg.textContent = "Aperta o saco! 💪";
      }
    }

    function guardarProgresso(nivel, estrelasGanhas) {
      fetch('guardar_progresso.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'nivel=' + encodeURIComponent(nivel) +
              '&estrelas_ganhas=' + encodeURIComponent(estrelasGanhas)
      })
      .then(r => r.text())
      .then(t => console.log('Resposta PHP:', t))
      .catch(e => console.error(e));
    }

    // Atualiza o sensor várias vezes por segundo
    setInterval(buscarSensor, 100); // 100 ms
  </script>

</body>
</html>
