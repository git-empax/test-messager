<?php

session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: ../");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <title>ВХОД | MY.MERAL.PRO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../src/css/login_signup.css">
</head>

<body>
    <div class="form-container">
	<div class="login-logo">
    <img src="../logo.png" alt="MY.MERAL.PRO">
</div>
        <h1>Вход</h1>
        <form id="loginForm">
            <div class="form-group">
                <label for="email">E-mail</label>
<input type="email" id="email" name="email" required maxlength="100">
<small id="email-error" style="color: red;"></small>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required maxlength="255">
                    <button type="button" id="toggle-password" class="password-toggle"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" id="submit" disabled>Войти</button>
            </div>
        </form>
        <div class="text-center">
            <p>У вас нет аккаунта? <a href="../signup/">Регистрация</a></p>
        </div>
    </div>
    <script src="../src/js/sweetalert2.js"></script>
    <script>
const emailField = document.getElementById('email');
const emailError = document.getElementById('email-error');
const loginForm = document.getElementById('loginForm');
const submitButton = document.getElementById('submit');

function validateForm() {
    const email = emailField.value;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        emailError.textContent = "Введите корректный email!";
        submitButton.disabled = true;
    } else {
        emailError.textContent = "";
        submitButton.disabled = false;
    }
}

emailField.addEventListener('input', validateForm);

        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const toggleIcon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(loginForm);
            fetch('../api/auth/login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '../';
                        });
                    } else {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: true
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Произошла непредвиденная ошибка. Пожалуйста, попробуйте еще раз..',
                        showConfirmButton: true
                    });
                });
        });
    </script>
</body>

</html>
