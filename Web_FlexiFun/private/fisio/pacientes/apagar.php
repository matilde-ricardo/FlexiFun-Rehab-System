<?php
declare(strict_types=1);
session_start();

// 1) Verificar login
if (!isset($_SESSION['user_id'])) {
    header('Location: /FlexiFun/public/login.php');
    exit;
}

$idTerapeutaUser = (int) $_SESSION['user_id'];

// 2) Verificar se veio o id do paciente
if (!isset($_GET['id'])) {
    header('Location: utentes.php');
    exit;
}

$idPacienteUser = (int) $_GET['id'];

require_once __DIR__ . '/../../db.php';

try {
    // 3) Confirmar que o paciente pertence a este terapeuta
    $stmt = $pdo->prepare("
        SELECT user_id
        FROM paciente
        WHERE user_id = :idPac
          AND fisioterapeuta_id = :idTerapeuta
        LIMIT 1
    ");
    $stmt->execute([
        'idPac'       => $idPacienteUser,
        'idTerapeuta' => $idTerapeutaUser
    ]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        // Não existe ou não é deste fisio
        header('Location: utentes.php?erro=nao_encontrado');
        exit;
    }

    // 4) Apagar em transação: primeiro paciente, depois user
    $pdo->beginTransaction();

    // apagar da tabela paciente
    $delPac = $pdo->prepare("
        DELETE FROM paciente
        WHERE user_id = :idPac
          AND fisioterapeuta_id = :idTerapeuta
    ");
    $delPac->execute([
        'idPac'       => $idPacienteUser,
        'idTerapeuta' => $idTerapeutaUser
    ]);

    // apagar da tabela users
    $delUser = $pdo->prepare("
        DELETE FROM users
        WHERE id = :idUser
          AND role = 'paciente'
    ");
    $delUser->execute([
        'idUser' => $idPacienteUser
    ]);

    $pdo->commit();

    // 5) Voltar à lista com flag de sucesso
    header('Location: utentes.php?apagado=1');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Podes logar o erro se quiseres
    // error_log($e->getMessage());
    header('Location: utentes.php?erro=apagacao');
    exit;
}
