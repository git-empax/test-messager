<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ./login/");
    exit;
}

include './config.php';
$query = new Database();

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$chat_id     = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : null;

/*
|--------------------------------------------------------------------------
| Если это групповой чат
|--------------------------------------------------------------------------
*/
if ($chat_id) {

    // Проверяем что пользователь состоит в чате
    $isMember = $query->select(
        'chat_users',
        '*',
        'chat_id = ? AND user_id = ?',
        [$chat_id, $sender_id],
        'ii'
    );

    if (empty($isMember)) {
        header("Location: ./");
        exit;
    }

    $chat = $query->select(
        'chats',
        '*',
        'id = ?',
        [$chat_id],
        'i'
    )[0];

} else {

// Это личный чат
if (!$receiver_id || $sender_id == $receiver_id) {
    header("Location: ./");
    exit;
}

$receiverData = $query->select(
    'users',
    '*',
    'id = ?',
    [$receiver_id],
    'i'
);

if (empty($receiverData)) {
    header("Location: ./");
    exit;
}

$receiver_user = $receiverData[0];

}



if ($receiver_id) {
    $blocked_sender = $query->select(
        'block_users',
        '*',
        'blocked_by = ? AND blocked_user = ?',
        [$receiver_id, $sender_id],
        'ii'
    );

    $receiver_blocked = $query->select(
        'block_users',
        '*',
        'blocked_by = ? AND blocked_user = ?',
        [$sender_id, $receiver_id],
        'ii'
    );
} else {
    $blocked_sender = [];
    $receiver_blocked = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
MY.МЕРАЛ.ПРО | 
<?= isset($chat_id) && $chat_id ? $chat['name'] : $receiver_user['full_name'] ?>
</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="./src/css/style.css">

</head>

<body>
    <div class="container-fluid h-100">
        <div class="row justify-content-center align-items-center h-100">

            <div class="col-xl-10">
    <div class="row h-100">
<div class="col-md-2 contacts-column d-none d-md-block">
    <div class="card contacts-card">

        <div class="card-header msg_head">
            <div class="user_info">
                <span>Группы</span>
            </div>
        </div>

        <div class="card-body contacts_body">

            <div style="padding:10px;">
                <button class="btn btn-sm btn-success w-100"
                        onclick="createGroupPrompt()">
                    + Создать группу
                </button>
            </div>

            <ul class="contacts" id="groups-list"></ul>

        </div>
    </div>
</div>
        <div class="col-md-8 chat">

            <!-- ===== Mobile Contacts ===== -->
         <div class="mobile-contacts d-md-none">
    <div class="mobile-contacts-wrapper" id="mobile-contacts-list">
    </div>
</div>
                <div class="card">
                    <div class="card-header msg_head">

                        <div class="d-flex bd-highlight">
                                               <!-- Кнопка назад -->
    <div class="back-button mr-3" onclick="window.location.href='./';">
        <i class="fas fa-arrow-left"></i>
    </div>
                            <div class="img_cont">
                               <?php
$avatar = 'default.png';

if ($chat_id && isset($chat['avatar']) && !empty($chat['avatar'])) {
    $avatar = $chat['avatar'];
}
?>

<img src="<?= $chat_id 
    ? './src/images/chat-avatar/' . $avatar
    : './src/images/profile-picture/' . $receiver_user['profile_picture'] ?>"
    class="rounded-circle user_img"
    style="cursor: pointer;"
    <?= !$chat_id ? 'onclick="openProfileModal()"' : '' ?>>


                                  
                            </div>
                            <div class="user_info">
                                <span>
<?= isset($chat_id) && $chat_id 
    ? $chat['name'] 
    : $receiver_user['full_name'] ?>
</span>

                                                        				<?php if ($chat_id): ?>
<p>группа  <button class="btn btn-sm btn-primary"
            onclick="openAddUserModal(<?= $chat_id ?>)">
        <i class="fas fa-user-plus"></i> Добавить
    </button></p>
<?php endif; ?></div>
                        </div>
                        <span id="action_menu_btn_user" style="padding: 5px;" onclick="createMenu(null, null)">
 <i class="fas fa-ellipsis-v"></i>
                        </span>
                        <div class="action_menu_user" style="display: none;"></div>
                    </div>

                    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document" style="display:flex; justify-content:center;">
                            <div class="modal-content" style="background: #7F7FD5; background: -webkit-linear-gradient(to right, #91EAE4, #86A8E7, #7F7FD5); background: linear-gradient(to right, #91EAE4, #86A8E7, #7F7FD5); border: none; border-radius: 11px; max-width:calc(100% - 20px); top: 15px">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="profileModalLabel">
<?= isset($receiver_user) ? $receiver_user['full_name'] . "" : 'Profile' ?>
</h5>
                                    <button type="button" class="close" id="closeModalBtn" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center">
                                        <img src="./src/images/profile-picture/<?= $receiver_user['profile_picture'] ?>" class="rounded-circle mb-4" width="100" height="100">
                                        <h5><?= $receiver_user['full_name'] ?></h5>
                                        <p><?= $receiver_user['email'] ?></p>
                                       
                                        <p>Регистрация: <?= date("F j, Y", strtotime($receiver_user['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <!-- Вывод участников чата -->
<?php if ($chat_id): ?>
<div class="chat-members-section">

    <div class="chat-members-header" onclick="toggleMembers()">
        <span><i class="fas fa-users"></i> Участники</span>
        <i class="fas fa-chevron-down" id="membersToggleIcon"></i>
    </div>

    <div class="chat-members-wrapper" id="membersWrapper">
        <div class="chat-members" id="chat-members"></div>
    </div>
</div>
<?php endif; ?>


                    <div class="card-body msg_card_body" id="messages-container">
					
                        <!-- Message Container -->
                    </div>


                    <div class="blocked"></div>

<div class="card-footer">

    <div id="dropZone" class="drop-zone">
        Перетащите файл сюда
    </div>

    <div class="input-group" id="send_msg">

        <div class="input-group-append">
            <span class="input-group-text attach_btn"
                  onclick="document.getElementById('fileInput').click();">
                <i class="fas fa-paperclip"></i>
            </span>
        </div>

        <input type="file" id="fileInput" style="display:none;">

        <textarea class="form-control type_msg"
                  placeholder="Введите свое сообщение..."></textarea>

        <div class="input-group-append">
            <span class="input-group-text send_btn">
                <i class="fas fa-location-arrow"></i>
            </span>
        </div>

    </div>

    <div id="previewContainer"></div>

</div>


                </div>
            </div>        <!-- Правая колонка -->
<div class="col-md-2 contacts-column d-none d-md-block">
    <div class="card contacts-card">
        <div class="card-header contacts-header">

    <div class="contacts-title">
        Контакты
    </div>

    <div class="contacts-search-wrapper">
        <input type="text"
               id="contactsSearch"
               placeholder="Поиск по имени...">
    </div>

</div>

        <div class="card-body contacts_body">

            <ul class="contacts" id="contacts-list"></ul>
            <div id="contacts-empty" class="text-center text-muted mt-4" style="display:none;">
                Контакты не найдены 
            </div>
        </div>
    </div>
</div>

    </div>
</div>
            </div>
</div>
        </div>
                </div>



    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.all.min.js"></script>
    <script>
        // Fetch Message

            const receiverId = <?= isset($receiver_id) && $receiver_id ? (int)$receiver_id : 'null' ?>;
const chatId     = <?= isset($chat_id) && $chat_id ? (int)$chat_id : 'null' ?>;

            const senderId = <?= $sender_id ?>;
            let countScrollHeight = 0;

            const messagesContainer = document.getElementById('messages-container');
			let firstLoad = true;
let lastMessageId = 0;
let oldestMessageId = 0;
let isLoadingOld = false;
let pollingActive = true;
let loadedMessageIds = new Set();

        function LoadMessages(forceScroll = false) {
    return $.ajax({
        url: './api/fetch_messages.php',
        type: 'POST',
        data: chatId
    ? { chat_id: chatId, last_id: lastMessageId }
    : { id: receiverId, last_id: lastMessageId },
        dataType: 'json',

        success: function(response) {

            const Messages = response.data;
if (!Messages || Messages.length === 0) {
    return;
}

            const isAtBottom =
                messagesContainer.scrollTop + messagesContainer.clientHeight >=
                messagesContainer.scrollHeight - 20;

            let html = '';

            Messages.forEach(function(Message) {
				if (loadedMessageIds.has(Message.id)) {
    return;
}
loadedMessageIds.add(Message.id);
				  if (Message.id > lastMessageId) {
        lastMessageId = Message.id;
    }
	if (oldestMessageId === 0 || Message.id < oldestMessageId) {
    oldestMessageId = Message.id;
}
                const content = Message.content || '';

                const isSystem =
                    content.includes('добавлен в группу') ||
                    content.includes('удален из группы') ||
                    content.includes('стал администратором') ||
                    content.includes('покинул группу');

                if (isSystem) {
                    html += `
                        <div class="d-flex justify-content-center mb-3">
                            <div class="system-message">
                                ${content}
                            </div>
                        </div>
                    `;
                    return;
                }

                const isSender = Message.sender_id == senderId;

                if (isSender) {

                    html += `
                        <div class="d-flex justify-content-end mb-4 message-container"
                             data-message-id="${Message.id}">
                            <div class="msg_cotainer_send">
                                ${formatMessage(content)}
                                <span class="msg_time_send">${Message.created_at}</span>
                            </div>
                        </div>
                    `;

                } else {

                    if (chatId) {

                        html += `
                            <div class="d-flex justify-content-start mb-4 message-container"
                                 data-message-id="${Message.id}">
                                 
                                <div class="img_cont_msg">
                                   <img src="./src/images/profile-picture/${Message.profile_picture}"
     class="rounded-circle user_img_msg clickable-user"
     onclick="openPrivateChat(${Message.sender_id})">
                                </div>

                                <div>
                                    <div class="group-sender-name clickable-user"
     onclick="openPrivateChat(${Message.sender_id})">
    ${Message.full_name}
</div>

                                    <div class="msg_cotainer">
                                        ${formatMessage(content)}
                                        <span class="msg_time">${Message.created_at}</span>
                                    </div>
                                </div>

                            </div>
                        `;

                    } else {

                        html += `
                            <div class="d-flex justify-content-start mb-4 message-container"
                                 data-message-id="${Message.id}">
                                <div class="msg_cotainer">
                                    ${formatMessage(content)}
                                    <span class="msg_time">${Message.created_at}</span>
                                </div>
                            </div>
                        `;
                    }
                }

            });
if (firstLoad) {

    messagesContainer.innerHTML = html;
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    firstLoad = false;

} else {

if (html.trim() !== '') {

    messagesContainer.insertAdjacentHTML('beforeend', html);

    if (forceScroll) {

        messagesContainer.scrollTop = messagesContainer.scrollHeight;

    } else {

        const isAtBottom =
            messagesContainer.scrollTop + messagesContainer.clientHeight >=
            messagesContainer.scrollHeight - 20;

        if (isAtBottom) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
}
}

        }
    });
}

           
		   
        function formatMessage(content) {

    if (!content) return '';

    if (content.startsWith('uploads/')) {

        const extension = content.split('.').pop().toLowerCase();
        const fileName = content.split('/').pop();
 // ==== ЕСЛИ ИЗОБРАЖЕНИЕ ====
if (['jpg','jpeg','png','gif','webp'].includes(extension)) { return `<div style="max-width:220px;"><img src="${content}" style="max-width:100%;border-radius:10px;display:block;">
<div style="display:flex;
                justify-content:space-between;
                align-items:center;
                margin-top:5px;
                font-size:12px;
                opacity:0.85;
            "><span style="
                    max-width:150px;
                    overflow:hidden;
                    text-overflow:ellipsis;
                    white-space:nowrap;
                "> ${fileName}</span>
                <a href="${content}" download
   style="
       width:42px;
       height:42px;
       background:#4cd137;
       color:#fff;
       border-radius:50%;
       display:flex;
       align-items:center;
       justify-content:center;
       font-size:18px;
       text-decoration:none;
       transition:all 0.2s ease;
       box-shadow:0 3px 8px rgba(0,0,0,0.3);
   "
   onmouseover="this.style.background='#44bd32'; this.style.transform='scale(1.1)'"
   onmouseout="this.style.background='#4cd137'; this.style.transform='scale(1)'"
><i class="fas fa-download"></i></a></div></div>`;
}

// ==== ЕСЛИ ОБЫЧНЫЙ ФАЙЛ ====

let icon = "fas fa-file";
let iconColor = "#ccc";

if (extension === "pdf") {
    icon = "fas fa-file-pdf";
    iconColor = "#e74c3c";
} else if (["doc","docx"].includes(extension)) {
    icon = "fas fa-file-word";
    iconColor = "#2e86de";
} else if (["xls","xlsx"].includes(extension)) {
    icon = "fas fa-file-excel";
    iconColor = "#27ae60";
} else if (["zip","rar"].includes(extension)) {
    icon = "fas fa-file-archive";
    iconColor = "#f39c12";
} else if (["txt"].includes(extension)) {
    icon = "fas fa-file-alt";
    iconColor = "#bdc3c7";
}

return `
    <div style="
        
        padding:12px;
        border-radius:10px;
        max-width:260px;
        display:flex;
        justify-content:space-between;
        align-items:center;
    ">
        <div style="
            display:flex;
            align-items:center;
            gap:10px;
            overflow:hidden;
        ">
            <i class="${icon}" style="font-size:22px;color:${iconColor};"></i>

            <span style="
                max-width:140px;
                overflow:hidden;
                text-overflow:ellipsis;
                white-space:nowrap;
                font-weight:500;
            "><a href="${content}" target="_blank">
                ${fileName}
            </a></span>
        </div>

<a href="${content}" download
   style="
       width:42px;
       height:42px;
       background:#4cd137;
       color:#fff;
       border-radius:50%;
       display:flex;
       align-items:center;
       justify-content:center;
       font-size:18px;
       text-decoration:none;
       transition:all 0.2s ease;
       box-shadow:0 3px 8px rgba(0,0,0,0.3);
   "
   onmouseover="this.style.background='#44bd32'; this.style.transform='scale(1.1)'"
   onmouseout="this.style.background='#4cd137'; this.style.transform='scale(1)'"
>
    <i class="fas fa-download"></i>
</a>
    </div>
`;

    }

    return content;
}
function createMenu(id, user) {

    const action_menu_user = document.querySelector('.action_menu_user');

    // ===== ГЛАВНОЕ МЕНЮ (три точки в шапке) =====
    if (id == null && user == null) {

        action_menu_user.style = `top: 22px; right: 22px;`;

        if (chatId) {
            // Группа
            action_menu_user.innerHTML = `
                <ul>
            <li onclick="editGroup(${chatId})">
                <i class="fas fa-edit"></i> Редактировать группу
            </li>

            <li onclick="openGroupManager(${chatId})">
                <i class="fas fa-users"></i> Управление группой
            </li>

            <li style="color:red;" onclick="deleteGroup(${chatId})">
                <i class="fas fa-trash"></i> Удалить группу
            </li>
        </ul>
            `;
        } else {
            // Личный чат
            action_menu_user.innerHTML = `
                <ul>
                    <li onclick="openProfileModal()">
    <i class="fas fa-user-circle"></i> Смотреть профиль
</li>
                    <li style="color: orange" onclick="clearMessages()">
                        <i class="fas fa-times-circle"></i> Очистить
                    </li>
                    <li style="color: red" onclick="block(${receiverId})">
                        <i class="fas fa-ban"></i> Заблокировать
                    </li>
                </ul>
            `;
        }

    }
    // ===== МЕНЮ СООБЩЕНИЯ ОТПРАВИТЕЛЯ =====
    else if (user == 'sender') {

        action_menu_user.style = `top: 90px; right: 90px;`;
        action_menu_user.innerHTML = `
            <ul>
                <li onclick="copyMessage(${id})"><i class="fas fa-copy"></i> Copy</li>
                <li onclick="edit(${id})"><i class="fas fa-edit"></i> Edit</li>
                <li onclick="deleteMessage(${id})"><i class="fas fa-trash-alt"></i> Delete</li>
            </ul>
        `;
    }
    // ===== МЕНЮ ЧУЖОГО СООБЩЕНИЯ =====
    else {

        action_menu_user.style = `top: 90px; left: 90px;`;
        action_menu_user.innerHTML = `
            <ul>
                <li onclick="copyMessage(${id})"><i class="fas fa-copy"></i> Copy</li>
                <li onclick="deleteMessage(${id})"><i class="fas fa-trash-alt"></i> Delete</li>
            </ul>
        `;
    }
}
        // Copy Message
        function copyMessage(id) {

            const senderMessageElement = document.querySelector(`[data-message-id="${id}"] .msg_cotainer_send div`);
            const receiverMessageElement = document.querySelector(`[data-message-id="${id}"] .msg_cotainer div`);

            const messageElement = senderMessageElement || receiverMessageElement;

            if (messageElement) {
                const messageText = messageElement.innerText;

                navigator.clipboard.writeText(messageText).then(() => {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Message copied to clipboard!',
                        showConfirmButton: false,
                        timer: 1500,
                        toast: true,
                        background: '#4CAF50',
                        color: '#fff'
                    });
                })
            }
        }


        // Block Function
        function block(userId) {
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('action', 'block');

            fetch('./api/change_user_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1000
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            showConfirmButton: true
                        });
                    }
                })
        }

        // unBlock Function
        function unBlock(userId) {
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('action', 'unblock');

            fetch('./api/change_user_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1000
                            })
                            .then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            showConfirmButton: true
                        });
                    }
                });
        }
    </script>
    <script>
        let isOpen = null;

        function toggleActionMenu(event, actionMenuSelector) {
            event.stopPropagation();

            const actionMenu = document.querySelector(actionMenuSelector);

            if (isOpen && isOpen !== actionMenu) {
                isOpen.style.display = 'none';
            }

            if (actionMenu.style.display === 'block') {
                actionMenu.style.display = 'none';
                isOpen = null;
            } else {
                actionMenu.style.display = 'block';
                isOpen = actionMenu;
            }
        }

        document.getElementById('action_menu_btn_user').addEventListener('click', function(event) {
            toggleActionMenu(event, '.action_menu_user');
        });

        document.querySelector('.msg_card_body').addEventListener('click', function(event) {
            if (event.target.closest('.action_menu_btn')) {
                const messageContainer = event.target.closest('.message-container');
                const messageId = messageContainer ? messageContainer.getAttribute('data-message-id') : null;

                createMenu(messageId, messageContainer.id);
                toggleActionMenu(event, '.action_menu_user');
            }
        });



        document.getElementById('closeModalBtn').addEventListener('click', function() {
            const modal = document.getElementById('profileModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        document.getElementById('profileModal').addEventListener('click', function(event) {
            const modalContent = document.querySelector('.modal-content');
            if (!modalContent.contains(event.target)) {
                const modal = document.getElementById('profileModal');
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        document.addEventListener('click', function(event) {
            if (isOpen && !isOpen.contains(event.target) && !event.target.closest('.action_menu_btn') && !event.target.closest('#action_menu_btn_user')) {
                isOpen.style.display = 'none';
                isOpen = null;
            }
        });
    </script>
    <script>
    // Отправка по Enter
document.querySelector('.type_msg').addEventListener('keydown', function(e) {

    // Если нажали Enter без Shift
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault(); // отменяем перенос строки
        document.querySelector('.send_btn').click(); // вызываем клик кнопки
    }

});
        // Send Message
        document.querySelector('.send_btn').addEventListener('click', function(event) {
            event.preventDefault();

            const receiverId = <?= isset($receiver_id) && $receiver_id ? (int)$receiver_id : 'null' ?>;
             
			const chatId     = <?= isset($chat_id) && $chat_id ? (int)$chat_id : 'null' ?>;

            const block_by = <?= isset($receiver_user) 
    ? '"' . $receiver_user['full_name'] . '"' 
    : 'null' ?>;


           async function userBlocked() {

    if (!receiverId) return false; // если это группа — блокировки нет

    const response = await fetch('./api/check_user_status.php?receiver_id=' + receiverId);
    const data = await response.json();

    return data.status === 'blocked';
}


            userBlocked().then(isBlocked => {

                if (isBlocked) {
                    Swal.fire({
                        title: `You are blocked.`,
                        text: `You have been blocked by "${block_by}" You cannot send messages.`,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33',
                        background: '#fff3f3',
                        customClass: {
                            title: 'swal-title',
                            content: 'swal-content',
                        }
                    });
                } else {
                    const messageInput = document.querySelector('.type_msg');
                    const message = messageInput.value.trim();
if (!message && !selectedFile) {
    return;
}
                    const receiver_id = <?= isset($receiver_id) ? (int)$receiver_id : 0 ?>;
                    const formData = new FormData();

formData.append('content', message);

if (chatId) {
    formData.append('chat_id', chatId);
} else {
    formData.append('receiver_id', receiver_id);
}

if (selectedFile) {
    formData.append('file', selectedFile);
}

$.ajax({
    url: './api/send_message.php',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {

        if (response.status === 'success') {

            messageInput.value = '';
            selectedFile = null;
            previewContainer.innerHTML = '';
            fileInput.value = '';

            LoadMessages(true);

        } else {
            console.log("SERVER ERROR:", response.message);
        }

    },
    error: function(err) {
        console.log("AJAX ERROR:", err);
    }
});
                }

            });
        });

        // Edit Message
        function edit(messageId) {
            const messageContainer = document.querySelector(`.message-container[data-message-id="${messageId}"]`);

            if (messageContainer) {
                const messageElement = messageContainer.querySelector('.msg_cotainer_send div');

                if (messageElement) {
                    const messageText = messageElement.textContent.trim();

                    Swal.fire({
                        title: 'Edit your message',
                        input: 'textarea',
                        inputValue: messageText,
                        inputPlaceholder: 'Write your message here...',
                        showCancelButton: true,
                        confirmButtonText: 'Сохранить изменения',
                        cancelButtonText: 'Отменить',
                        inputAttributes: {
                            'aria-label': 'Type your message'
                        },
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to write something!';
                            }
                        },
                        customClass: {
                            input: 'swal2-textarea'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const newMessage = result.value;

                            $.ajax({
                                url: './api/edit_message.php',
                                method: 'POST',
                                data: {
                                    message_id: messageId,
                                    new_message: newMessage
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        messageElement.textContent = newMessage;
                                        Swal.fire({
                                            title: 'Updated!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1000
                                        });
                                    }
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire('Error!', 'Message content not found. Please try again.', 'error');
                }
            } else {
                Swal.fire('Error!', 'Message container not found. Please try again.', 'error');
            }
        }

        // Delete Message 
        function deleteMessage(messageId) {
            Swal.fire({
                title: 'Вы уверены?',
                text: "Удалить сообщение?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Да, удалить!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: './api/delete_message.php',
                        method: 'POST',
                        data: {
                            message_id: messageId
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Удалено!', 'Ваше сообщение удалено.', 'success');
                                Swal.fire({
                                    title: 'Удалено!',
                                    text: 'Your message has been deleted.',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                                $(`.message-container[data-message-id="${messageId}"]`).remove();

                                let countElement = document.querySelector('.user_info p b');
                                if (countElement) {
                                    let currentCount = parseInt(countElement.textContent.trim());
                                    if (!isNaN(currentCount) && currentCount > 0) {
                                        countElement.textContent = currentCount - 1;
                                    }
                                }
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Clear Messages
        function clearMessages() {
            Swal.fire({
                title: 'Вы уверены?',
                text: 'Все сообщения в этом чате будут удалены?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить!',
                cancelButtonText: 'Нет, оставить'
            }).then((result) => {
                if (result.isConfirmed) {

                    const receiverId = <?= isset($receiver_id) ? (int)$receiver_id : 0 ?>;


                    $.ajax({
                        url: './api/clear_messages.php',
                        method: 'POST',
                        data: {
                            clear: true,
                            receiver_id: receiverId
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Удалено!',
                                    text: response.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                            }
                        }
                    });
                }
            });
        }
    </script>
    <script>
function fetchContacts(searchTerm = '') {

    return fetch('./api/fetch_contacts.php?search=' + encodeURIComponent(searchTerm))
        .then(response => response.json())
        .then(data => {

            const contactsList = document.getElementById('contacts-list');
            const mobileList = document.getElementById('mobile-contacts-list');
mobileList.innerHTML = '';
            const contactsEmpty = document.getElementById('contacts-empty');
            contactsList.innerHTML = '';

            if (data.status === 'success' && data.data.length > 0) {

                contactsEmpty.style.display = 'none';

                data.data.forEach(user => {

                    const currentReceiverId = <?= isset($receiver_id) ? (int)$receiver_id : 0 ?>;
                    const isActive = user.user_id == currentReceiverId;

                    const listItem = document.createElement('li');
                    listItem.style.cursor = 'pointer';
                    // ===== Mobile version =====
const mobileItem = document.createElement('div');
mobileItem.className = 'mobile-contact-item ' + (isActive ? 'active' : '');

mobileItem.onclick = function() {
    window.location.href = 'chat.php?id=' + user.user_id;
};

mobileItem.innerHTML = `
    <div class="mobile-avatar-wrapper">
        <img src="./src/images/profile-picture/${user.profile_picture}">
        ${user.unread_messages > 0 
            ? `<span class="mobile-badge">${user.unread_messages}</span>` 
            : ''}
    </div>
    <span>${user.full_name}</span>
`;


mobileList.appendChild(mobileItem);
                    listItem.className = isActive ? 'active-contact' : '';

                    listItem.onclick = function() {
                        window.location.href = 'chat.php?id=' + user.user_id;
                    };

                    listItem.innerHTML = `
                        <div class="d-flex align-items-center">
                            <img src="./src/images/profile-picture/${user.profile_picture}" 
                                 class="rounded-circle mr-2" 
                                 width="40" height="40">
                            <div>
                                <div style="font-weight:500;">${user.full_name}</div>
                                ${user.unread_messages > 0 
                                    ? `<span class="badge badge-warning">${user.unread_messages}</span>` 
                                    : ''}
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

fetchContacts();
const contactsSearchInput = document.getElementById('contactsSearch');

let contactsSearchTimeout = null;

contactsSearchInput.addEventListener('input', function() {

    if (contactsSearchTimeout) clearTimeout(contactsSearchTimeout);

    contactsSearchTimeout = setTimeout(function() {
        fetchContacts(contactsSearchInput.value.trim());
    }, 400);

});
</script>

<script>

const dropZone = document.getElementById('dropZone');
const previewContainer = document.getElementById('previewContainer');
const fileInput = document.getElementById('fileInput');

let selectedFile = null;
const MAX_SIZE = 10 * 1024 * 1024;

/* Drag */
document.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('active');
});

document.addEventListener('dragleave', e => {
    e.preventDefault();
    dropZone.classList.remove('active');
});

document.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('active');

    if (e.dataTransfer.files.length > 0) {
        handleFile(e.dataTransfer.files[0]);
    }
});

/* Select */
fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        handleFile(this.files[0]);
    }
});

function handleFile(file) {

    if (file.size > MAX_SIZE) {
        alert("Файл больше 10MB");
        return;
    }

    selectedFile = file;
    previewContainer.innerHTML = '';

    if (file.type.startsWith('image/')) {

        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML =
                `<img src="${e.target.result}">`;
        };
        reader.readAsDataURL(file);

    } else {
        previewContainer.innerHTML =
            `<div>📎 ${file.name}</div>`;
    }
}

</script>

<?php if ($chat_id): ?>
<script>
function fetchChatMembers() {

    const chatId = <?= (int)$chat_id ?>;

    fetch('./api/fetch_chat_members.php?chat_id=' + chatId)
        .then(res => res.json())
        .then(data => {

            const container = document.getElementById('chat-members');
            container.innerHTML = '';

            if (data.status === 'success') {

                data.data.forEach(member => {

                    const item = document.createElement('div');
                    item.className = 'chat-member-item';

                    item.innerHTML = `
    <img src="./src/images/profile-picture/${member.profile_picture}">
    <span>${member.full_name}</span>
    ${member.role === 'admin' 
    ? `<i class="fas fa-crown" style="color:#f1c40f;margin-left:6px;"></i>` 
    : ''}
`;

                    item.style.cursor = 'pointer';

item.onclick = function() {
    if (member.user_id == <?= $sender_id ?>) return;
    window.location.href = 'chat.php?id=' + member.user_id;
};

                    container.appendChild(item);
                });
            }
        });
}

fetchChatMembers();
</script>
<?php endif; ?>

<script>
function fetchGroups() {

    return fetch('./api/fetch_groups.php')
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
                        <div class="d-flex align-items-center justify-content-between">
                            
                            <div class="d-flex align-items-center">
                                <div style="position:relative;">
                                    <img src="./src/images/chat-avatar/${group.avatar || 'default.png'}"
                                         class="rounded-circle mr-2"
                                         width="40" height="40">

                                    <!-- online indicator (заготовка) -->
                                    <span class="online_icon" 
                                          style="display:none;"></span>
                                </div>

                                <div style="font-weight:500;">
                                    ${group.name}
                                </div>
                            </div>

                            ${group.unread_count > 0 
                                ? `<span class="badge badge-warning">${group.unread_count}</span>` 
                                : ''}

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
</script>

<script>
function editGroup(chatId) {

    Swal.fire({
        title: 'Редактировать группу',
        html: `
            <input id="groupNameEdit" class="swal2-input" placeholder="Новое название">

            <p style="margin-top:10px;">Выберите стандартный аватар:</p>

            <div style="display:flex; gap:10px; justify-content:center;">
                <img src="./src/images/chat-avatar/group1.png" width="60" style="cursor:pointer;" onclick="selectAvatarEdit('group1.png')">
                <img src="./src/images/chat-avatar/group2.png" width="60" style="cursor:pointer;" onclick="selectAvatarEdit('group2.png')">
                <img src="./src/images/chat-avatar/group3.png" width="60" style="cursor:pointer;" onclick="selectAvatarEdit('group3.png')">
            </div>

            <p style="margin-top:10px;">Или загрузите новый:</p>
            <input type="file" id="avatarFileEdit" class="swal2-file">
        `,
        showCancelButton: true,
        confirmButtonText: 'Сохранить',
        preConfirm: () => {

            const name = document.getElementById('groupNameEdit').value;
            const file = document.getElementById('avatarFileEdit').files[0];

            const formData = new FormData();
            formData.append('chat_id', chatId);
            formData.append('chat_name', name);

            if (window.selectedAvatarEdit) {
                formData.append('default_avatar', window.selectedAvatarEdit);
            }

            if (file) {
                formData.append('avatar', file);
            }

            return fetch('./api/update_chat.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    throw new Error(data.message);
                }
                return data;
            })
            .catch(err => {
                Swal.showValidationMessage(err);
            });
        }
    }).then(result => {
        if (result.isConfirmed) {
            location.reload();
        }
    });
}

function selectAvatarEdit(name) {
    window.selectedAvatarEdit = name;
}
</script>



<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function createGroupPrompt() {

    Swal.fire({
        title: 'Создать группу',
        html: `
            <input id="groupName" class="swal2-input" placeholder="Название">

            <p style="margin-top:10px;">Выберите стандартный аватар:</p>

            <div style="display:flex; gap:10px; justify-content:center;">
                <img src="./src/images/chat-avatar/group1.png" width="60" style="cursor:pointer;" onclick="selectAvatar('group1.png')">
                <img src="./src/images/chat-avatar/group2.png" width="60" style="cursor:pointer;" onclick="selectAvatar('group2.png')">
                <img src="./src/images/chat-avatar/group3.png" width="60" style="cursor:pointer;" onclick="selectAvatar('group3.png')">
            </div>

            <p style="margin-top:10px;">Или загрузите свой:</p>
            <input type="file" id="avatarFile" class="swal2-file">
        `,
        showCancelButton: true,
        confirmButtonText: 'Создать',
        preConfirm: () => {

            const name = document.getElementById('groupName').value;
            const file = document.getElementById('avatarFile').files[0];

            const formData = new FormData();
            formData.append('chat_name', name);

            if (window.selectedAvatar) {
                formData.append('default_avatar', window.selectedAvatar);
            }

            if (file) {
                formData.append('avatar', file);
            }

            return fetch('./api/create_chat.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    throw new Error(data.message);
                }
                return data;
            })
            .catch(err => {
                Swal.showValidationMessage(err);
            });
        }
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = 'chat.php?chat_id=' + result.value.chat_id;
        }
    });
}

function selectAvatar(name) {
    window.selectedAvatar = name;
}


function deleteGroup(chatId) {

    Swal.fire({
        title: 'Удалить группу?',
        text: 'Это действие нельзя отменить',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Удалить'
    }).then(result => {

        if (!result.isConfirmed) return;

        fetch('./api/delete_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'chat_id=' + chatId
        })
        .then(res => res.json())
        .then(data => {

            if (data.status === 'success') {
                window.location.href = './';
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }

        });

    });
}

function openGroupManager(chatId) {

    fetch('./api/fetch_chat_members.php?chat_id=' + chatId)
        .then(res => res.json())
        .then(data => {

            if (data.status !== 'success') return;

            let membersHtml = '';

            data.data.forEach(member => {

                membersHtml += `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <div>
                            <img src="./src/images/profile-picture/${member.profile_picture}" width="30" style="border-radius:50%;">
                            ${member.full_name}
                            ${member.role === 'admin' ? '👑' : ''}
                        </div>
                        <div>
                            ${member.role !== 'admin' ? 
                                `<button onclick="makeAdmin(${chatId}, ${member.user_id})">👑</button>` : ''}

                            <button onclick="removeUser(${chatId}, ${member.user_id})" style="color:red;">✖</button>
                        </div>
                    </div>
                `;
            });

            Swal.fire({
                title: 'Управление группой',
                html: `
                    <div style="max-height:300px; overflow:auto;">
                        ${membersHtml}
                    </div>

                    <hr>

                    <button onclick="addUserPrompt(${chatId})" 
                            style="width:100%; margin-top:10px;">
                        ➕ Добавить участника
                    </button>
                `,
                showConfirmButton: false
            });

        });
}


function addUserPrompt(chatId) {

   fetch('./api/fetch_users_not_in_chat.php?chat_id=' + chatId)
    .then(res => res.json())
    .then(data => {

        if (data.status !== 'success') return;

        if (data.data.length === 0) {
            Swal.fire('Нет доступных пользователей');
            return;
        }

        let options = '';

        data.data.forEach(user => {
            options += `<option value="${user.id}">${user.full_name}</option>`;
        });

        Swal.fire({
            title: 'Добавить участника',
            html: `
                <select id="newUser" class="swal2-select">
                    ${options}
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Добавить'
        }).then(result => {

            if (!result.isConfirmed) return;

            const userId = document.getElementById('newUser').value;

            fetch('./api/add_user_to_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `chat_id=${chatId}&user_id=${userId}`
            })
            .then(res => res.json())
            .then(response => {

                if (response.status === 'success') {
                    openGroupManager(chatId);
                    fetchChatMembers();
                } else {
                    Swal.fire('Ошибка', response.message, 'error');
                }

            });

        });

    });
}

function makeAdmin(chatId, userId) {

    fetch('./api/make_admin.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `chat_id=${chatId}&user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === 'success') {
            openGroupManager(chatId);
			fetchChatMembers();
        }

    });
}

function removeUser(chatId, userId) {

    fetch('./api/remove_user.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `chat_id=${chatId}&user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {

        console.log(data); // ← ВАЖНО для отладки

        if (data.status === 'success') {
            openGroupManager(chatId);
			fetchChatMembers();
        } else {
            Swal.fire('Ошибка', data.message, 'error');
        }

    });
}

function openAddUserModal(chatId) {

    fetch('./api/fetch_users_not_in_chat.php?chat_id=' + chatId)
        .then(res => res.json())
        .then(data => {

            if (data.status !== 'success') return;

            if (data.data.length === 0) {
                Swal.fire('Нет доступных пользователей');
                return;
            }

            let html = '<select id="newUser" class="swal2-select">';

            data.data.forEach(user => {
                html += `<option value="${user.id}">
                            ${user.full_name}
                         </option>`;
            });

            html += '</select>';

            Swal.fire({
                title: 'Добавить участника',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Добавить'
            }).then(result => {

                if (!result.isConfirmed) return;

                const userId = document.getElementById('newUser').value;

                fetch('./api/add_user_to_chat.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: `chat_id=${chatId}&user_id=${userId}`
                })
                .then(res => res.json())
                .then(response => {

                    if (response.status === 'success') {
                        fetchChatMembers();
                        Swal.fire('Добавлено!', '', 'success');
                    } else {
                        Swal.fire('Ошибка', response.message, 'error');
                    }

                });

            });

        });
}

function toggleMembers() {
    const wrapper = document.getElementById('membersWrapper');
    const icon = document.getElementById('membersToggleIcon');

    wrapper.classList.toggle('open');
    icon.classList.toggle('rotate');
}

function openProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function openPrivateChat(userId) {
    window.location.href = 'chat.php?id=' + userId;
}


// ===== ПОКАЗ КНОПКИ ТОЛЬКО ПРИ СКРОЛЛЕ ВВЕРХ =====

const loadMoreBtn = document.getElementById('loadMoreBtn');

messagesContainer.addEventListener('scroll', function() {

    const isAtBottom =
        messagesContainer.scrollTop + messagesContainer.clientHeight >=
        messagesContainer.scrollHeight - 10;

    if (isAtBottom) {
        loadMoreBtn.style.display = 'none';
    } else {
        loadMoreBtn.style.display = 'inline-block';
    }

});

let globalLoopActive = true;

async function globalSmartLoop() {

    if (!globalLoopActive) return;

    try {

await Promise.all([
    fetchContacts(),
    fetchGroups(),
    LoadMessages()
]);

        // 4️⃣ Проверка блокировки (только для личного чата)
        if (receiverId) {
            const response = await fetch('./api/check_user_status.php?receiver_id=' + receiverId);
            const data = await response.json();

            let blocked = document.querySelector('.blocked');

            if (data.status === 'blocked') {
                blocked.innerHTML = `
                    <div class="blocked-message">
                        <i class="fas fa-ban"></i>
                        <p>You are blocked!</p>
                    </div>
                `;
            } else {
                blocked.innerHTML = '';
            }
        }

    } catch (e) {
        console.log("Global loop error:", e);
    }

    // повтор через 3 секунды
    setTimeout(globalSmartLoop, 3000);
}

// Запуск
// 🚀 Мгновенная первая загрузка
LoadMessages(true);
messagesContainer.addEventListener('scroll', function() {

    if (messagesContainer.scrollTop === 0 && !isLoadingOld && oldestMessageId > 0) {

        isLoadingOld = true;

        $.ajax({
            url: './api/fetch_messages.php',
            type: 'POST',
            data: chatId
                ? { chat_id: chatId, older_than: oldestMessageId }
                : { id: receiverId, older_than: oldestMessageId },
            dataType: 'json',
            success: function(response) {

                const Messages = response.data;
                if (!Messages || Messages.length === 0) {
                    isLoadingOld = false;
                    return;
                }

                let previousHeight = messagesContainer.scrollHeight;
                let html = '';

                Messages.forEach(function(Message) {

                    if (loadedMessageIds.has(Message.id)) return;

                    loadedMessageIds.add(Message.id);

                    if (Message.id < oldestMessageId) {
                        oldestMessageId = Message.id;
                    }

                    html += `
                        <div class="d-flex justify-content-start mb-4 message-container"
                             data-message-id="${Message.id}">
                            <div class="msg_cotainer">
                                ${formatMessage(Message.content)}
                                <span class="msg_time">${Message.created_at}</span>
                            </div>
                        </div>
                    `;
                });

                messagesContainer.insertAdjacentHTML('afterbegin', html);

                let newHeight = messagesContainer.scrollHeight;
                messagesContainer.scrollTop = newHeight - previousHeight;

                isLoadingOld = false;
            }
        });
    }

});
globalSmartLoop();
</script>
</body>

</html>