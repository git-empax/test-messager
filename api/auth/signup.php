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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = $query->validate($_POST['full_name']);
    $email     = strtolower($query->validate($_POST['email']));
    $password  = $query->hashPassword($_POST['password']);

    // Проверяем существует ли email
    $checkEmail = $query->select('users', 'id', 'email = ?', [$email], 's');

    if (!empty($checkEmail)) {
        $response['status'] = 'error';
        $response['message'] = 'Email already exists';
        echo json_encode($response);
        exit;
    }

    $data = [
        'full_name'       => $full_name,
        'email'           => $email,
        'password'        => $password,
        'profile_picture' => 'default.png'
    ];

    $result = $query->insert('users', $data);

    if (!empty($result)) {

        $user_id = $query->select('users', 'id', 'email = ?', [$email], 's')[0]['id'];

        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $_SESSION['profile_picture'] = 'default.png';

        setcookie('session_token', session_id(), time() + (86400 * 30), "/", "", true, true);

        $response['status'] = 'success';
        $response['message'] = 'Registration successful';
        $response['data'] = [
            'loggedin' => true,
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $email,
            'profile_picture' => 'default.png'
        ];
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Registration failed.';
    }

} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);