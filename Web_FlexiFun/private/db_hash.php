<?php
require_once "db.php"; // ajusta se o caminho for diferente

$stmt = $pdo->query("SELECT id, password_hash FROM users");

while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {

    // Se ainda NÃO for hash (ex: 1234, 1111)
    if (!str_starts_with($u['password_hash'], '$2y$')) {

        $hash = password_hash($u['password_hash'], PASSWORD_DEFAULT);

        $upd = $pdo->prepare(
            "UPDATE users SET password_hash = ? WHERE id = ?"
        );
        $upd->execute([$hash, $u['id']]);
    }
}

echo "Passwords convertidas com sucesso.";
