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
    <title>РЕГИСТРАЦИЯ | MY.MERAL.PRO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../src/css/login_signup.css">
</head>

<body>
    <div class="form-container">
		<div class="login-logo">
    <img src="../logo.png" alt="MY.MERAL.PRO">
</div>
        <h1>Регистрация</h1>
        <form id="signupForm">
            <div class="form-group">
                <label for="full_name">ФИО</label>
                <input type="text" id="full_name" name="full_name" required maxlength="30">
            </div>
            <div class="form-group">
                <label for="email">E-mail (почта)</label>
                <input type="email" id="email" name="email" required maxlength="150">
                <p id="email-message"></p>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required maxlength="255">
                    <button type="button" id="toggle-password" class="password-toggle"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Пароль</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" required maxlength="255">
                    <button type="button" id="toggle-confirm-password" class="password-toggle"><i class="fas fa-eye"></i></button>
                </div>
                <small id="confirm-password-error" style="color: #e43c5a; font-weight:600"></small>
            </div>
            <div class="form-group">
                <button type="submit" id="submit" disabled>Зарегистрироваться</button>
            </div>
        </form>
        <div class="text-center">
            <p>У вас уже есть аккаунт? <a href="../login/">Войти</a></p>
        </div>
    </div>
    <script src="../src/js/sweetalert2.js"></script>
    <script>
	const submitButton = document.getElementById('submit');
        let isEmailAvailable = false;

        document.getElementById('email').addEventListener('input', function() {
            let email = this.value;
            if (email.length > 0) {
                fetch('../api/auth/check_availability.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `email=${encodeURIComponent(email)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        const messageElement = document.getElementById('email-message');
                        if (data.exists) {
                            messageElement.textContent = 'This email exists!';
                            isEmailAvailable = false;
							validateForm();
                        } else {
                            messageElement.textContent = '';
                            isEmailAvailable = true;
							validateForm();
						
                        }
                    });
            }
        });


        function validateEmailFormat(email) {
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            return emailPattern.test(email);
        }



        // Confirm password validation
        function validatePasswordsMatch() {
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            const errorElem = document.getElementById('confirm-password-error');
            if (confirmPassword.length > 0 && password !== confirmPassword) {
                errorElem.textContent = "Пароли не совпадают!";
                submitButton.disabled = true;
                return false;
            } else {
                errorElem.textContent = "";
                // Don't enable submit if other validation fails!
                validateForm();
                return true;
            }
        }
        document.getElementById('confirm_password').addEventListener('input', validatePasswordsMatch);
        document.getElementById('password').addEventListener('input', validatePasswordsMatch);
document.getElementById('full_name').addEventListener('input', validateForm);
document.getElementById('email').addEventListener('input', validateForm);
document.getElementById('password').addEventListener('input', validateForm);
document.getElementById('confirm_password').addEventListener('input', validateForm);
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            event.preventDefault();

            let email = document.getElementById('email').value;
            const messageElement = document.getElementById('email-message');
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            const confirmPasswordError = document.getElementById('confirm-password-error');

            if (!validateEmailFormat(email)) {
                messageElement.textContent = 'Формат электронного письма неверен!';
                return;
            }

            if (isEmailAvailable === false) {
                messageElement.textContent = 'Этот адрес электронной почты существует.!';
                return;
            }


            if (password !== confirmPassword) {
                confirmPasswordError.textContent = "Пароли не совпадают!";
                return;
            } else {
                confirmPasswordError.textContent = "";
            }

            const formData = new FormData(this);

            fetch('../api/auth/signup.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Регистрация прошла успешно!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '../';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ой...',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Произошла ошибка, пожалуйста, попробуйте еще раз.',
                    });
                });
        });

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

        document.getElementById('toggle-confirm-password').addEventListener('click', function() {
            const confirmPasswordField = document.getElementById('confirm_password');
            const toggleIcon = this.querySelector('i');
            if (confirmPasswordField.type === 'password') {
                confirmPasswordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
		function validateForm() {

    const fullName = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();

    const emailValid = validateEmailFormat(email);
    const passwordsMatch = password === confirmPassword && password.length > 0;

    if (
        fullName.length > 0 &&
        emailValid &&
        passwordsMatch &&
        isEmailAvailable === true
    ) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
}
    </script>
</body>

</html>
