<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_id = intval($_GET['chat_id'] ?? 0);

$query = new Database();

/* Проверяем что пользователь состоит в чате */
$isMember = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ?',
    [$chat_id, $user_id],
    'ii'
);

if (empty($isMember)) {
    echo json_encode(["status" => "error"]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Получаем пользователей, которых НЕТ в группе
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id, full_name
    FROM users
    WHERE id NOT IN (
        SELECT user_id FROM chat_users WHERE chat_id = ?
    )
";

$result = $query->executeQuery($sql, [$chat_id], 'i');

if (is_string($result)) {
    echo json_encode(["status" => "error"]);
    exit;
}

$users = $result->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "status" => "success",
    "data" => $users
]);