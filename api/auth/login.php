<?php
header('Content-Type: application/json');
session_start();

include '../../config.php';
$query = new Database();

$response = [
    'status' => '',
    'message' => '',
    'data' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['email']) && isset($_POST['password'])) {

        $email = strtolower(trim($_POST['email']));
        $password = trim($_POST['password']);

        $result = $query->select('users', '*', "email = ?", [$email], 's');

        if (!empty($result)) {

            $user = $result[0];

            if ($user['password'] == $query->hashPassword($password)) {

                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_picture'] = $user['profile_picture'];

                setcookie('email', $email, time() + (86400 * 30), "/", "", true, true);
                setcookie('session_token', session_id(), time() + (86400 * 30), "/", "", true, true);

                $response['status'] = 'success';
                $response['message'] = 'Успешный вход в систему';
                $response['data'] = [
                    'loggedin' => true,
                    'user_id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'profile_picture' => $user['profile_picture']
                ];

            } else {
                $response['status'] = 'error';
                $response['message'] = 'Incorrect email or password';
            }

        } else {
            $response['status'] = 'error';
            $response['message'] = 'No user found with that email';
        }

    } else {
        $response['status'] = 'error';
        $response['message'] = 'Please provide both email and password';
    }

} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);