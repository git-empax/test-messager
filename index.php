<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ./login/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЧАТ | MY.MERAL.PRO</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
    <link rel="stylesheet" href="./src/css/profile-modal.css">
</head>

<body><?php if($sender_user['role'] === 'admin'): ?>
<button onclick="openCreateChatModal()">Создать чат</button>
<?php endif; ?>
    <!-- Profile Modal: Only 1 Image (Preview), Responsive, and Clean -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="profileModalLabel">Редактировать профиль</h5>
                    <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" style="font-size:2rem;">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="profile-img-preview">
                        <img src="./src/images/profile-picture/default.png" id="modalProfilePicture" alt="Profile Preview">
                    </div>
                    <form id="profile-form" method="POST" enctype="multipart/form-data" autocomplete="off">
                        <div class="form-group mb-3">
                            <label for="full_name" class="form-label">ФИО:</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required maxlength="30" placeholder="Введите ФИО">
                        </div>
                        <div class="form-group mb-3">
                            <label for="profile_picture" class="form-label">Аватар:</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="profile_picture" name="profile_picture" accept="image/*">
                                <label class="custom-file-label" for="profile_picture">Сменить аватар</label>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">E-mail:</label>
                            <input type="email" id="email" name="email" class="form-control" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Новый пароль:</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" maxlength="255" placeholder="Введите новый пароль">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-link toggle-password-btn" tabindex="-1" onclick="togglePassword('password', this)" style="padding:0 12px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="confirm_password" class="form-label">Подтвердите пароль:</label>
                            <div class="input-group">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" maxlength="255" placeholder="Новый пароль еще раз">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-link toggle-password-btn" tabindex="-1" onclick="togglePassword('confirm_password', this)" style="padding:0 12px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="confirm-password-error"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Сохранить изменения</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Chat Layout -->
    <div class="container-fluid h-100">
        <div class="row justify-content-center h-100">
            <div class="col-md-8 col-xl-6 chat">
                <div class="card">
                    <div class="card-header d-flex">
                        <div class="input-group">
                            <span class="input-group-text menu_btn" data-toggle="modal" data-target="#profileModal">
                                <i class="fas fa-user-edit"></i>
                            </span>
                            <input type="text" placeholder="Поиск..." name="search" id="search" class="form-control search">
                            <div class="input-group-prepend">
                                <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <span class="input-group-prepend logout_menu" onclick="logout()" style="cursor:pointer" title="Выйти">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                    </div>
                    <div class="card-body contacts_body">

    <!-- ===== ГРУППЫ ===== -->
<div class="groups-section">

    <div class="groups-title" onclick="toggleGroups()">
        <i class="fas fa-users"></i> Группы
        <i class="fas fa-chevron-down toggle-icon" id="groupsToggleIcon"></i>
    </div>

    <div class="groups-wrapper collapsed" id="groupsWrapper">
        <ul class="contacts" id="groups-list"></ul>
    </div>

</div>

    <hr style="background:rgba(255,255,255,0.15); margin:15px 0;">

    <!-- ===== КОНТАКТЫ ===== -->
    <ul class="contacts" id="contacts-list"></ul>
                        <div id="contacts-empty" class="text-center text-muted mt-5" style="display:none;">Контакты не найдены.</div>
                    </div>
                    <div class="card-footer"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Fetch user profile info and set preview
        function fetchUserProfile() {
            fetch('./api/fetch_profile.php')
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                       const {
    full_name,
    email,
    profile_picture
} = result.data;
                        document.getElementById('full_name').value = full_name;
                        document.getElementById('email').value = email;
                        const profilePic = profile_picture && profile_picture !== 'default.png' ?
                            './src/images/profile-picture/' + profile_picture :
                            './src/images/profile-picture/default.png';
                        document.getElementById('modalProfilePicture').src = profilePic;
                    }
                });
        }

        // Preview uploaded profile image
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('modalProfilePicture').src = ev.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Password confirmation validation
        document.getElementById('profile-form').addEventListener('input', function() {
            const pass = document.getElementById('password').value;
            const conf = document.getElementById('confirm_password').value;
            const errorElem = document.getElementById('confirm-password-error');
            if (pass !== conf && conf.length > 0) {
                errorElem.textContent = "Passwords do not match!";
            } else {
                errorElem.textContent = "";
            }
        });

        // On submit, prevent save if not valid!
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const conf = document.getElementById('confirm_password').value;
            const errorElem = document.getElementById('confirm-password-error');
            if (pass !== conf) {
                errorElem.textContent = "Passwords do not match!";
                e.preventDefault();
                return false;
            }
            // AJAX submit
            e.preventDefault();
            const formData = new FormData(this);
            fetch('./api/fetch_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => window.location.reload());
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Failed to connect to the server. Please try again later.',
                    });
                });
        });

        fetchUserProfile();

        // Contacts search and polling
        let fetchInterval = setInterval(fetchContacts, 5000);
        const searchInput = document.getElementById('search');
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearInterval(fetchInterval);
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(function() {
                fetchContacts(searchInput.value.trim());
            }, 500);
        });
        searchInput.addEventListener('blur', function() {
            fetchInterval = setInterval(fetchContacts, 5000);
        });
		
		
function fetchGroups() {

    fetch('./api/fetch_groups.php')
        .then(response => response.json())
        .then(data => {

            const groupsList = document.getElementById('groups-list');
            groupsList.innerHTML = '';

            if (data.status === 'success' && data.data.length > 0) {

                data.data.forEach(group => {

                    const listItem = document.createElement('li');
                    listItem.style.cursor = 'pointer';

                    listItem.onclick = function() {
                        window.location.href = 'chat.php?chat_id=' + group.id;
                    };

                    listItem.innerHTML = `
                        <div class="d-flex bd-highlight">
                            <div class="img_cont">
                                <img src="./src/images/chat-avatar/${group.avatar || 'default.png'}"
                                     class="rounded-circle user_img">
                            </div>
                            <div class="user_info">
                                <span>${group.name}</span>
                                <p>Группа</p>
                            </div>
                            <div class="message_count">
                                ${group.unread_count > 0 
                                    ? `<span class="badge badge-warning">${group.unread_count}</span>` 
                                    : ''}
                            </div>
                        </div>
                    `;

                    groupsList.appendChild(listItem);
                });

            } else {

                groupsList.innerHTML = `
                    <li style="opacity:0.6; padding:10px;">
                        Нет групп
                    </li>
                `;
            }

        });
}

fetchGroups();
setInterval(fetchGroups, 5000);

       function fetchContacts(searchTerm = '') {
    fetch('./api/fetch_contacts.php?search=' + encodeURIComponent(searchTerm))
        .then(response => response.json())
        .then(data => {

            const contactsList = document.getElementById('contacts-list');
            const contactsEmpty = document.getElementById('contacts-empty');
            contactsList.innerHTML = '';

            if (data.status === 'success' && data.data.length > 0) {

                contactsEmpty.style.display = 'none';

                data.data.forEach(user => {

                    const highlightedFullName = highlightSearchTerm(user.full_name, searchTerm);
                    const unreadMessages = user.unread_messages;

                    const listItem = document.createElement('li');
                    listItem.setAttribute('onclick', `window.location.href='chat.php?id=${user.user_id}'`);

                    listItem.innerHTML = `
                        <div class="d-flex bd-highlight">
                            <div class="img_cont">
                                <img src="./src/images/profile-picture/${user.profile_picture}" 
                                    class="rounded-circle user_img" 
                                    alt="${user.full_name}">
                            </div>
                            <div class="user_info">
                                <span>${highlightedFullName}</span>
                            </div>
                            <div class="message_count">
                                ${unreadMessages > 0 ? `<span class="badge badge-warning">${unreadMessages}</span>` : ''}
                            </div>
                        </div>
                    `;

                    contactsList.appendChild(listItem);
                });

            } else {
                contactsEmpty.style.display = 'block';
            }

        });
}

        function highlightSearchTerm(text, searchTerm) {
            if (!searchTerm) return text;
            const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<span style="color: #e43c5a;">$1</span>');
        }
        fetchContacts();

        // Logout function
        function logout() {
            Swal.fire({
                title: 'Вы уверены?',
                text: "Вы уверены что хотите выйти?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Да, выйти!',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = './logout/';
                }
            });
        }
		
		function toggleGroups() {

    const wrapper = document.getElementById('groupsWrapper');
    const icon = document.getElementById('groupsToggleIcon');

    wrapper.classList.toggle('collapsed');
    icon.classList.toggle('rotated');
}
    </script>
</body>

</html>