<?php
declare(strict_types=1);

// Nome do terapeuta vindo da sessão
$nomeTerapeuta = $_SESSION['nome_terapeuta'] ?? 'Terapeuta';

// Valor por defeito
$iniciais = 'T';

if (!empty($nomeTerapeuta)) {
    $nomeTerapeuta = trim($nomeTerapeuta);

    // Divide o nome em palavras
    $partes = preg_split('/\s+/', $nomeTerapeuta);

    // Primeira letra do primeiro nome
    $iniciais = mb_strtoupper(mb_substr($partes[0], 0, 1));

    if (count($partes) > 1) {
        $iniciais .= mb_strtoupper(mb_substr(end($partes), 0, 1));
    }
 
}
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-circle">
            <?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="sidebar-logo-text">FlexiFun</div>
    </div>

    <div class="sidebar-section-title">Menu</div>

    <nav class="sidebar-menu">
        <a class="sidebar-link" href="/FlexiFun/private/fisio/index.php">
            Início
        </a>

        <a class="sidebar-link" href="/FlexiFun/private/fisio/pacientes/utentes.php">
            Utentes
        </a>

        <a class="sidebar-link logout" href="/FlexiFun/public/logout.php">
            Terminar sessão
        </a>
    </nav>
</aside>
