<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$chat_id = intval($_POST['chat_id']);
$target_user = intval($_POST['user_id']);

$query = new Database();

/* Проверяем админ ли текущий */
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

/* Обновляем роль */
$query->update(
    'chat_users',
    ['role' => 'admin'],
    'chat_id = ? AND user_id = ?',
    [$chat_id, $target_user],
    'ii'
);

echo json_encode(["status" => "success"]);