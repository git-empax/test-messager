<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);

if (!isset($_FILES['file'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$uploadDir = "../uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$originalName = $_FILES['file']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','txt','zip'];

if (!in_array($extension, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Тип файла запрещён']);
    exit;
}

if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'Файл больше 10MB']);
    exit;
}

// очищаем имя файла
$cleanName = preg_replace('/[^A-Za-zА-Яа-яЁё0-9_\-\.]/u', '_', pathinfo($originalName, PATHINFO_FILENAME));


// сокращаем имя (чтобы не ломало верстку)
$shortName = substr($cleanName, 0, 30);

// создаём уникальное имя для сервера
$newName = time() . "_" . $shortName . "." . $extension;

$targetFile = $uploadDir . $newName;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
    echo json_encode(['status' => 'error']);
    exit;
}

// путь для БД
$filePathForDB = "uploads/" . $newName;

// сохраняем сообщение
$query = new Database();
$query->insert('messages', [
    'sender_id' => $sender_id,
    'receiver_id' => $receiver_id,
    'content' => $filePathForDB,
    'created_at' => date("Y-m-d H:i:s")
]);

// размер файла
$fileSize = round(filesize($targetFile) / 1024, 2); // KB

// проверка изображение или нет
$isImage = in_array($extension, ['jpg','jpeg','png','gif','webp']);

echo json_encode([
    "status" => "success",
    "file_url" => $filePathForDB,
    "file_name" => $shortName . "." . $extension,
    "file_size" => $fileSize . " KB",
    "is_image" => $isImage,
    "created_at" => date("H:i")
]);
exit;
