<?php
declare(strict_types=1);

$nomePaciente = $_SESSION['nome_paciente'] ?? 'Paciente';
?>
<nav class="top-navbar">

    <div class="top-navbar-title">
        <i class="bi bi-controller"></i>
        Área do Paciente
    </div>

    <div class="user-menu">
        <button class="user-btn" type="button">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($nomePaciente, ENT_QUOTES, 'UTF-8') ?>
            <i class="bi bi-caret-down-fill"></i>
        </button>

        <div class="user-dropdown">
            <div class="user-dropdown-header">
                <?= htmlspecialchars($nomePaciente, ENT_QUOTES, 'UTF-8') ?><br>
                <span>Paciente</span>
            </div>

            <a href="#" onclick="return false;" style="opacity:.7; cursor: default;">
                <i class="bi bi-gear"></i> Perfil &amp; Definições (em breve)
            </a>

            <a href="/FlexiFun/public/logout.php" class="logout">
                <i class="bi bi-box-arrow-right"></i> Terminar sessão
            </a>
        </div>
    </div>

</nav>
