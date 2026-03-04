<?php
declare(strict_types=1);

$nomePaciente = $_SESSION['nome_paciente'] ?? 'Paciente';

// inicial (primeira letra do nome)
$iniciais = 'P';
if (!empty($nomePaciente)) {
    $nomePaciente = trim((string)$nomePaciente);
    $partes = preg_split('/\s+/', $nomePaciente);
    if (!empty($partes[0])) {
        $iniciais = mb_strtoupper(mb_substr($partes[0], 0, 1));
    }
}

// ✅ para marcar link ativo
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
function isActive(string $file, string $currentPage): string {
    return $currentPage === $file ? ' active' : '';
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
        <a class="sidebar-link<?= isActive('dashboard.php', $currentPage) ?>" href="/FlexiFun/private/paciente/dashboard.php">
            Início
        </a>

        <a class="sidebar-link<?= isActive('jogar.php', $currentPage) ?>" href="/FlexiFun/private/paciente/jogar.php">
            Jogar
        </a>

        <a class="sidebar-link logout" href="/FlexiFun/public/logout.php">
            Terminar sessão
        </a>
    </nav>
</aside>