<?php
declare(strict_types=1);

// Navbar reutilizável (NÃO colocar <head> aqui)
$nomeTerapeuta = $_SESSION['nome_terapeuta'] ?? 'Terapeuta';

// Se quiseres também mostrar especialidade e não a tens na sessão,
// fica um fallback simples:
$especialidade = $_SESSION['especialidade_terapeuta'] ?? '—';
?>
<nav class="top-navbar">

    <!-- Título / indicador da área -->
    <div class="top-navbar-title">
        <i class="bi bi-heart-pulse"></i>
        Painel de Reabilitação
    </div>

    <!-- MENU DO UTILIZADOR -->
    <div class="user-menu">

        <button class="user-btn" type="button">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($nomeTerapeuta, ENT_QUOTES, 'UTF-8') ?>
            <i class="bi bi-caret-down-fill"></i>
        </button>

        <div class="user-dropdown">
            <div class="user-dropdown-header">
                <?= htmlspecialchars($nomeTerapeuta, ENT_QUOTES, 'UTF-8') ?><br>
                <span><?= htmlspecialchars($especialidade, ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <!-- Perfil -->
            <a href="/FlexiFun/private/fisio/perfil/perfil.php">
                <i class="bi bi-gear"></i> Perfil &amp; Definições
            </a>

            <!-- Se não tiveres esta página implementada, deixa # ou remove -->
            <a href="#" onclick="return false;" style="opacity:.7; cursor: default;">
                <i class="bi bi-display"></i> Ecrã e Acessibilidade (em breve)
            </a>

            <!-- Logout -->
            <a href="/FlexiFun/public/logout.php" class="logout">
                <i class="bi bi-box-arrow-right"></i> Terminar sessão
            </a>
        </div>
    </div>

</nav>
