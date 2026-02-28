<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$chat_id = intval($_POST['chat_id'] ?? 0);
$target_user = intval($_POST['user_id'] ?? 0);

$query = new Database();

/* Проверяем админ ли текущий пользователь */
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

/* Нельзя удалить самого себя */
if ($current_user_id == $target_user) {
    echo json_encode(["status" => "error", "message" => "Нельзя удалить себя"]);
    exit;
}

/* Проверяем не последний ли это админ */
$isTargetAdmin = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ? AND role = "admin"',
    [$chat_id, $target_user],
    'ii'
);

if (!empty($isTargetAdmin)) {

    $adminCount = $query->select(
        'chat_users',
        'COUNT(*) as total',
        'chat_id = ? AND role = "admin"',
        [$chat_id],
        'i'
    );

    if ($adminCount[0]['total'] <= 1) {
        echo json_encode(["status" => "error", "message" => "Нельзя удалить последнего администратора"]);
        exit;
    }
}

/* Получаем имя удаляемого пользователя */
$userData = $query->select(
    'users',
    'full_name',
    'id = ?',
    [$target_user],
    'i'
);

$userName = $userData[0]['full_name'] ?? 'Участник';

/* Удаляем участника */
$deleted = $query->delete(
    'chat_users',
    'chat_id = ? AND user_id = ?',
    [$chat_id, $target_user],
    'ii'
);

if ($deleted > 0) {

    /*
    |--------------------------------------------------------------------------
    | 🔔 Системное сообщение
    |--------------------------------------------------------------------------
    */
    $query->insert('messages', [
        'chat_id' => $chat_id,
        'sender_id' => $current_user_id,
        'content' => $userName . ' удален из группы',
        'type' => 'system',
        'status' => 'read'
    ]);

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Пользователь не найден"]);
}