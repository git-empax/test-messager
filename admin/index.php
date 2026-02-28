<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit;
}

$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    die("Доступ запрещен");
}
?>

<h1>Админ-панель</h1>

<ul>
    <li><a href="users.php">Пользователи</a></li>
    <li><a href="chats.php">Группы</a></li>
    <li><a href="cleanup.php">Очистка файлов</a></li>
</ul>
