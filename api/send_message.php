<?php
session_start();
header('Content-Type: application/json');

include '../config.php';
$query = new Database();

$response = [
    'status' => 'error',
    'message' => '',
    'data' => []
];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$chat_id     = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;

$content = isset($_POST['content']) ? trim($_POST['content']) : '';

/*
|--------------------------------------------------------------------------
| ОБРАБОТКА ФАЙЛА
|--------------------------------------------------------------------------
*/
if (!empty($_FILES['file']['name'])) {

    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['file']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        $content = 'uploads/' . $fileName;
    } else {
        $response['message'] = 'File upload failed';
        echo json_encode($response);
        exit;
    }
}

if ($content === '') {
    $response['message'] = 'Empty message';
    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| ГРУППОВОЙ ЧАТ
|--------------------------------------------------------------------------
*/
if ($chat_id > 0) {

    $check = $query->select(
        'chat_users',
        '*',
        'chat_id = ? AND user_id = ?',
        [$chat_id, $sender_id],
        'ii'
    );

    if (empty($check)) {
        $response['message'] = 'Not a member';
        echo json_encode($response);
        exit;
    }

    $data = [
        'sender_id' => $sender_id,
        'chat_id' => $chat_id,
        'receiver_id' => null,
        'content' => $content,
        'status' => 'unread'
    ];
}

/*
|--------------------------------------------------------------------------
| ЛИЧНЫЙ ЧАТ
|--------------------------------------------------------------------------
*/
elseif ($receiver_id > 0) {

    $data = [
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'chat_id' => null,
        'content' => $content,
        'status' => 'unread'
    ];
}
else {
    $response['message'] = 'No receiver';
    echo json_encode($response);
    exit;
}

$insertId = $query->insert('messages', $data);

if (is_numeric($insertId)) {
    $response['status'] = 'success';
    $response['data'] = [
        'id' => $insertId,
        'content' => $content,
        'created_at' => date('Y-m-d H:i:s')
    ];
} else {
    $response['message'] = $insertId;
}

echo json_encode($response);