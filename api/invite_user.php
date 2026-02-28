session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) exit;

$admin_id = $_SESSION['user_id'];
$chat_id = intval($_POST['chat_id']);
$user_id = intval($_POST['user_id']);

$query = new Database();

/* Проверяем, админ ли он в этом чате */
$isAdmin = $query->select(
    'chat_users',
    '*',
    'chat_id = ? AND user_id = ? AND role = "admin"',
    [$chat_id, $admin_id],
    'ii'
);

if (empty($isAdmin)) {
    echo json_encode(["status"=>"error","message"=>"Нет прав"]);
    exit;
}

/* Добавляем участника */
$query->insert('chat_users', [
    'chat_id' => $chat_id,
    'user_id' => $user_id,
    'role' => 'member'
]);

echo json_encode(["status"=>"success"]);
