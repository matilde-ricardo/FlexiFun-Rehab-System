<?php
// ligar à BD (ajusta user, password e nome BD)
$dsn = 'mysql:host=localhost;dbname=luva_jogo;charset=utf8';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo "Erro na ligação à BD";
    exit;
}

session_start();

// Em produção, o id da criança viria do login
// Para já, podemos fixar em 1 ou usar sessão
$id_crianca = $_SESSION['id_crianca'] ?? 1;

$nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 0;
$estrelas_ganhas = isset($_POST['estrelas_ganhas']) ? (int)$_POST['estrelas_ganhas'] : 0;

if ($nivel <= 0 || $estrelas_ganhas <= 0) {
    http_response_code(400);
    echo "Dados inválidos";
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO progresso (id_crianca, nivel, estrelas_ganhas, data_hora)
    VALUES (?, ?, ?, NOW())
");
$stmt->execute([$id_crianca, $nivel, $estrelas_ganhas]);

echo "ok";
