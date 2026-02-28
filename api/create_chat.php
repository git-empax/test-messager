<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

/*
|--------------------------------------------------------------------------
| Разрешаем только POST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Проверка сессии
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No session"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['chat_name'] ?? '');

if (!$name) {
    echo json_encode([
        "status" => "error",
        "message" => "Введите название"
    ]);
    exit;
}

$query = new Database();

$avatar = 'default.png';

/*
|--------------------------------------------------------------------------
| Если выбран стандартный аватар
|--------------------------------------------------------------------------
*/
if (!empty($_POST['default_avatar'])) {
    $avatar = $_POST['default_avatar'];
}

/*
|--------------------------------------------------------------------------
| Если загружен файл
|--------------------------------------------------------------------------
*/
if (!empty($_FILES['avatar']['name'])) {

    $uploadDir = '../src/images/chat-avatar/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['avatar']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
        $avatar = $fileName;
    }
}

/*
|--------------------------------------------------------------------------
| Создаём чат
|--------------------------------------------------------------------------
*/
$chat_id = $query->insert('chats', [
    'name' => $name,
    'avatar' => $avatar,
    'created_by' => $user_id
]);

if (!is_numeric($chat_id)) {
    echo json_encode([
        "status" => "error",
        "message" => $chat_id
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Добавляем создателя как admin
|--------------------------------------------------------------------------
*/
$query->insert('chat_users', [
    'chat_id' => $chat_id,
    'user_id' => $user_id,
    'role' => 'admin'
]);

echo json_encode([
    "status" => "success",
    "chat_id" => $chat_id
]);
exit;