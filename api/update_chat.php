<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$chat_id = intval($_POST['chat_id']);
$name = trim($_POST['chat_name']);

$query = new Database();

/* Проверка админа */
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

$data = [];

if ($name) {
    $data['name'] = $name;
}

/* Обработка файла */
if (!empty($_FILES['avatar']['name'])) {

    $uploadDir = '../src/images/chat-avatar/';
    $fileName = time() . '_' . basename($_FILES['avatar']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
        $data['avatar'] = $fileName;
    }
}

/* Стандартный аватар */
if (!empty($_POST['default_avatar'])) {
    $data['avatar'] = $_POST['default_avatar'];
}

if (!empty($data)) {
    $query->update(
        'chats',
        $data,
        'id = ?',
        [$chat_id],
        'i'
    );
}

echo json_encode(["status" => "success"]);