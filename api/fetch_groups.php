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

$groups = $query->select(
    "chat_users cu 
     JOIN chats c ON cu.chat_id = c.id",
    "c.id, c.name, c.avatar",
    "cu.user_id = ?",
    [$user_id],
    "i"
);

if (!$groups) {
    $response['status'] = 'success';
    $response['data'] = [];
    echo json_encode($response);
    exit;
}

foreach ($groups as &$group) {

    $unread = $query->select(
        "messages",
        "COUNT(*) as total",
        "chat_id = ? AND sender_id != ? AND status = 'unread'",
        [$group['id'], $user_id],
        "ii"
    );

    $group['unread_count'] = $unread[0]['total'] ?? 0;

    if (empty($group['avatar'])) {
        $group['avatar'] = 'default.png';
    }
}

$response['status'] = 'success';
$response['data'] = $groups;

echo json_encode($response);