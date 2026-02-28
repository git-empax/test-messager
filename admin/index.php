<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT is_admin FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

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
