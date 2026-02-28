<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_id = intval($_POST['chat_id'] ?? 0);

$query = new Database();

/* Проверяем админ ли */
$isAdmin = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ? AND role = "admin"',
    [$chat_id, $user_id],
    'ii'
);

if (empty($isAdmin)) {
    echo json_encode(["status" => "error", "message" => "Нет прав"]);
    exit;
}

/* Удаляем сообщения */
$query->delete('messages', 'chat_id = ?', [$chat_id], 'i');

/* Удаляем участников */
$query->delete('chat_users', 'chat_id = ?', [$chat_id], 'i');

/* Удаляем сам чат */
$query->delete('chats', 'id = ?', [$chat_id], 'i');

echo json_encode(["status" => "success"]);
exit;