<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) exit;

$current_user_id = $_SESSION['user_id'];
$chat_id = intval($_POST['chat_id']);
$new_user = intval($_POST['user_id']);

$query = new Database();

/* Проверяем админ */
$isAdmin = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ? AND role = "admin"',
    [$chat_id, $current_user_id],
    'ii'
);

if (empty($isAdmin)) {
    echo json_encode(["status" => "error", "message" => "Нет прав"]);
    exit;
}

/* Проверяем не состоит ли уже */
$exists = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ?',
    [$chat_id, $new_user],
    'ii'
);

if (!empty($exists)) {
    echo json_encode(["status" => "error", "message" => "Уже в группе"]);
    exit;
}

/* Добавляем участника */
$query->insert('chat_users', [
    'chat_id' => $chat_id,
    'user_id' => $new_user,
    'role' => 'member'
]);

/*
|--------------------------------------------------------------------------
| 🔔 СИСТЕМНОЕ СООБЩЕНИЕ
|--------------------------------------------------------------------------
*/

/* Получаем имя нового участника */
$userData = $query->select(
    'users',
    'full_name',
    'id = ?',
    [$new_user],
    'i'
);

$newUserName = $userData[0]['full_name'] ?? 'Новый участник';

/* Создаем системное сообщение */
$query->insert('messages', [
    'chat_id' => $chat_id,
    'sender_id' => $current_user_id,
    'content' => $newUserName . ' добавлен в группу',
    'type' => 'system',
    'status' => 'read'
]);

echo json_encode(["status" => "success"]);