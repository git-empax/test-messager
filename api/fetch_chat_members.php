<?php
session_start();
header('Content-Type: application/json');
include '../config.php';
$query = new Database();

$response = [
    'status' => 'error',
    'data' => []
];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;

if (!$chat_id) {
    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| Проверяем что пользователь состоит в чате
|--------------------------------------------------------------------------
*/
$isMember = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ?',
    [$chat_id, $user_id],
    'ii'
);

if (empty($isMember)) {
    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| Получаем участников
|--------------------------------------------------------------------------
*/
$members = $query->select(
    'chat_users cu
     JOIN users u ON cu.user_id = u.id',
    'u.id as user_id, u.full_name, u.profile_picture, cu.role',
    'cu.chat_id = ?',
    [$chat_id],
    'i'
);

$response['status'] = 'success';
$response['data'] = $members;

echo json_encode($response);
