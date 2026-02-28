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

$current_user_id = $_SESSION['user_id'];

$receiver_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$chat_id     = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$last_id     = isset($_POST['last_id']) ? intval($_POST['last_id']) : 0;
$older_than  = isset($_POST['older_than']) ? intval($_POST['older_than']) : 0;

$limit = 20;

/*
|--------------------------------------------------------------------------
| ГРУППОВОЙ ЧАТ
|--------------------------------------------------------------------------
*/
if ($chat_id > 0) {

    // Проверяем участие
    $check = $query->select(
        'chat_users',
        '*',
        'chat_id = ? AND user_id = ?',
        [$chat_id, $current_user_id],
        'ii'
    );

    if (empty($check)) {
        echo json_encode($response);
        exit;
    }

    // Помечаем как прочитанные
    $query->update(
        'messages',
        ['status' => 'read'],
        'chat_id = ? AND sender_id != ?',
        [$chat_id, $current_user_id],
        'ii'
    );

    // ===== ЗАГРУЗКА СТАРЫХ =====
    if ($older_than > 0) {

        $messages = $query->select(
            'messages m JOIN users u ON u.id = m.sender_id',
            'm.*, u.profile_picture, u.full_name',
            'm.chat_id = ? AND m.id < ? ORDER BY m.id DESC LIMIT ?',
            [$chat_id, $older_than, $limit],
            'iii'
        );

        $messages = array_reverse($messages);
    }

    // ===== НОВЫЕ СООБЩЕНИЯ =====
    elseif ($last_id > 0) {

        $messages = $query->select(
            'messages m JOIN users u ON u.id = m.sender_id',
            'm.*, u.profile_picture, u.full_name',
            'm.chat_id = ? AND m.id > ? ORDER BY m.id ASC',
            [$chat_id, $last_id],
            'ii'
        );
    }

    // ===== ПЕРВАЯ ЗАГРУЗКА =====
    else {

        $messages = $query->select(
            'messages m JOIN users u ON u.id = m.sender_id',
            'm.*, u.profile_picture, u.full_name',
            'm.chat_id = ? ORDER BY m.id DESC LIMIT ?',
            [$chat_id, $limit],
            'ii'
        );

        $messages = array_reverse($messages);
    }

    $response['status'] = 'success';
    $response['data'] = $messages;

    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| ЛИЧНЫЙ ЧАТ
|--------------------------------------------------------------------------
*/
if ($receiver_id > 0) {

    // Помечаем как прочитанные
    $query->update(
        'messages',
        ['status' => 'read'],
        'receiver_id = ? AND sender_id = ?',
        [$current_user_id, $receiver_id],
        'ii'
    );

    $baseCondition = '
        (
            (m.sender_id = ? AND m.receiver_id = ?) 
            OR 
            (m.sender_id = ? AND m.receiver_id = ?)
        )
    ';

    // ===== СТАРЫЕ =====
    if ($older_than > 0) {

        $messages = $query->select(
            'messages m JOIN users u ON u.id = m.sender_id',
            'm.*, u.profile_picture, u.full_name',
            $baseCondition . ' AND m.id < ? ORDER BY m.id DESC LIMIT ?',
            [
                $current_user_id,
                $receiver_id,
                $receiver_id,
                $current_user_id,
                $older_than,
                $limit
            ],
            'iiiiii'
        );

        $messages = array_reverse($messages);
    }

    // ===== НОВЫЕ =====
    elseif ($last_id > 0) {

        $messages = $query->select(
            'messages m JOIN users u ON u.id = m.sender_id',
            'm.*, u.profile_picture, u.full_name',
            $baseCondition . ' AND m.id > ? ORDER BY m.id ASC',
            [
                $current_user_id,
                $receiver_id,
                $receiver_id,
                $current_user_id,
                $last_id
            ],
            'iiiii'
        );
    }

    // ===== ПЕРВАЯ ЗАГРУЗКА =====
    else {

        $messages = $query->select(
            'messages m JOIN users u ON u.id = m.sender_id',
            'm.*, u.profile_picture, u.full_name',
            $baseCondition . ' ORDER BY m.id DESC LIMIT ?',
            [
                $current_user_id,
                $receiver_id,
                $receiver_id,
                $current_user_id,
                $limit
            ],
            'iiiii'
        );

        $messages = array_reverse($messages);
    }

    $response['status'] = 'success';
    $response['data'] = $messages;

    echo json_encode($response);
    exit;
}

echo json_encode($response);