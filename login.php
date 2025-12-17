<?php
session_start();
include("./settings/connect_datebase.php");

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user'])) {
    if($_SESSION['user'] != -1) {
        // БЕЗОПАСНО: Используем подготовленные выражения
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $stmt->bind_param("i", $_SESSION['user']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($user_read = $result->fetch_assoc()) {
            if($user_read['roll'] == 0) {
                header("Location: user.php");
                exit;
            } else if($user_read['roll'] == 1) {
                header("Location: admin.php");
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
    <head> 
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Авторизация - Безопасность веб-приложений</title>
        
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <link rel="stylesheet" href="style.css">
        <style>
            #codeModal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 1000;
            }
            
            .code-modal-content {
                background: white;
                width: 380px;
                margin: 120px auto;
                padding: 30px;
                border-radius: 12px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                position: relative;
            }
            
            .close-modal {
                position: absolute;
                top: 15px;
                right: 20px;
                font-size: 24px;
                cursor: pointer;
                color: #999;
            }
            
            .close-modal:hover {
                color: #333;
            }
            
            #authCode {
                width: 220px;
                padding: 18px;
                font-size: 28px;
                text-align: center;
                letter-spacing: 8px;
                border: 2px solid #4CAF50;
                border-radius: 8px;
                margin: 20px 0;
                font-family: 'Courier New', monospace;
                background: #f9f9f9;
                transition: all 0.3s;
            }
            
            #authCode:focus {
                outline: none;
                border-color: #2196F3;
                box-shadow: 0 0 10px rgba(33, 150, 243, 0.3);
            }
            
            #codeError {
                color: #f44336;
                margin-top: 15px;
                min-height: 25px;
                font-size: 14px;
                padding: 8px;
                border-radius: 4px;
                background: #ffebee;
            }
            
            .code-success {
                color: #4CAF50 !important;
                background: #e8f5e9 !important;
            }
            
            #codeInfo {
                color: #666;
                margin-top: 12px;
                font-size: 13px;
            }
            
            .code-resend {
                margin-top: 15px;
                font-size: 13px;
                color: #2196F3;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                padding: 5px 10px;
                border-radius: 4px;
                transition: all 0.3s;
            }
            
            .code-resend:hover {
                background: #e3f2fd;
                text-decoration: none;
            }
            
            .code-resend.disabled {
                color: #999;
                cursor: not-allowed;
                background: #f5f5f5;
            }
            
            .button {
                background: #4CAF50;
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                font-weight: bold;
                transition: all 0.3s;
            }
            
            .button:hover {
                background: #45a049;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            
            .button_diactive {
                background: #ccc !important;
                cursor: not-allowed !important;
                transform: none !important;
                box-shadow: none !important;
            }
            
            .loading {
                display: none;
                margin: 15px auto;
                width: 32px;
                height: 32px;
            }
            
            .demo-warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
                padding: 10px;
                border-radius: 6px;
                margin: 15px 0;
                font-size: 13px;
            }
            
            .demo-code {
                font-size: 20px;
                font-weight: bold;
                color: #d63031;
                background: #fab1a0;
                padding: 8px 15px;
                border-radius: 6px;
                margin: 10px 0;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div class="top-menu">
            <a href="#"><img src="img/logo1.png" alt="Логотип"/></a>
            <div class="name">
                <a href="index.php">
                    <div class="subname">БЕЗОПАСНОСТЬ ВЕБ-ПРИЛОЖЕНИЙ</div>
                    Пермский авиационный техникум им. А. Д. Швецова
                </a>
            </div>
        </div>
        <div class="space"> </div>
        <div class="main">
            <div class="content">
                <div class="login">
                    <div class="name">Авторизация</div>
                    
                    <div class="sub-name">Логин:</div>
                    <input name="_login" type="text" placeholder="Введите логин" 
                           onkeypress="return PressToEnter(event)" autocomplete="username"/>
                    
                    <div class="sub-name">Пароль:</div>
                    <input name="_password" type="password" placeholder="Введите пароль" 
                           onkeypress="return PressToEnter(event)" autocomplete="current-password"/>
                    
                    <div style="margin-top: 20px;">
                        <a href="regin.php" style="color: #2196F3; text-decoration: none; margin-right: 20px;">
                            📝 Регистрация
                        </a>
                        <a href="recovery.php" style="color: #666; text-decoration: none;">
                            🔓 Забыли пароль?
                        </a>
                    </div>
                    
                    <input type="button" class="button" value="Войти" onclick="LogIn()" style="margin-top: 25px;"/>
                    <img src="img/loading.gif" class="loading" alt="Загрузка"/>
                    
                    <div style="margin-top: 20px; font-size: 12px; color: #777;">
                        🔒 Используется двухфакторная аутентификация. После ввода логина и пароля потребуется код подтверждения.
                    </div>
                </div>
                
                <div class="footer">
                    © КГАПОУ "Авиатехникум", 2020
                    <a href="#">Конфиденциальность</a>
                    <a href="#">Условия</a>
                </div>
            </div>
        </div>
        
        <!-- Модальное окно для ввода кода -->
        <div id="codeModal">
            <div class="code-modal-content">
                <div class="close-modal" onclick="hideCodeModal()">×</div>
                
                <h3 style="margin-bottom:15px; color: #333;">🔐 Подтверждение входа</h3>
                <p id="codeEmailInfo" style="margin-bottom: 10px;">
                    На почту <strong id="userEmail"></strong> отправлен 6-значный код
                </p>
                <p style="color: #666; font-size: 14px; margin-bottom: 5px;">
                    Введите код подтверждения:
                </p>
                
                <input type="text" id="authCode" maxlength="6" placeholder="000000" autocomplete="off"/>
                
                <div id="demoInfo" style="display: none;">
                    <div class="demo-warning">
                        <strong>Демо-режим:</strong> Email не отправлен (не настроен сервер)<br>
                        <div class="demo-code" id="demoCodeDisplay"></div>
                        Используйте этот код для тестирования
                    </div>
                </div>
                
                <div style="margin-top:25px; display: flex; gap: 15px; justify-content: center;">
                    <button onclick="verifyCode()" class="button" style="padding:12px 30px;">
                        ✅ Подтвердить
                    </button>
                    <button onclick="hideCodeModal()" class="button" 
                            style="padding:12px 20px; background:#f44336; color:white;">
                        ❌ Отмена
                    </button>
                </div>
                
                <div id="codeError"></div>
                
                <div class="code-resend" onclick="resendCode()" id="resendLink">
                    ↻ Отправить код повторно
                </div>
                
                <div id="codeInfo">
                    ⏱️ Код действителен 10 минут
                </div>
            </div>
        </div>
        
        <script>
            // Глобальные переменные
            var codeLoading = false;
            var resendTimer = null;
            var resendCooldown = 60; // 60 секунд между отправками
            var currentResendTime = 0;
            
            // Показать модальное окно с кодом
            function showCodeModal() {
                document.getElementById('codeModal').style.display = 'block';
                document.getElementById('authCode').focus();
                document.getElementById('authCode').value = '';
                document.getElementById('codeError').innerHTML = '';
                document.getElementById('demoInfo').style.display = 'none';
                
                // Блокируем повторную отправку
                startResendTimer();
            }
            
            // Таймер для повторной отправки
            function startResendTimer() {
                var resendLink = document.getElementById('resendLink');
                currentResendTime = resendCooldown;
                
                resendLink.classList.add('disabled');
                resendLink.textContent = 'Отправить код повторно (через ' + currentResendTime + ' сек)';
                
                // Очищаем предыдущий таймер
                if(resendTimer) clearInterval(resendTimer);
                
                // Запускаем новый таймер
                resendTimer = setInterval(function() {
                    currentResendTime--;
                    resendLink.textContent = 'Отправить код повторно (через ' + currentResendTime + ' сек)';
                    
                    if(currentResendTime <= 0) {
                        clearInterval(resendTimer);
                        resendLink.classList.remove('disabled');
                        resendLink.textContent = '↻ Отправить код повторно';
                    }
                }, 1000);
            }
            
            // Повторная отправка кода
            function resendCode() {
                var resendLink = document.getElementById('resendLink');
                
                // Проверяем, активен ли таймер
                if(resendLink.classList.contains('disabled')) {
                    return;
                }
                
                var errorDiv = document.getElementById('codeError');
                errorDiv.innerHTML = '<span style="color:#2196F3;">Отправка нового кода...</span>';
                errorDiv.className = '';
                
                $.ajax({
                    url: 'ajax/resend_code.php',
                    type: 'POST',
                    success: function(response) {
                        console.log("Ответ resend_code:", response);
                        
                        if(response === "SUCCESS") {
                            errorDiv.innerHTML = '<span class="code-success">✅ Новый код отправлен на вашу почту!</span>';
                            errorDiv.className = 'code-success';
                            startResendTimer();
                            document.getElementById('demoInfo').style.display = 'none';
                        } 
                        else if(response === "ERROR_TOO_SOON") {
                            errorDiv.innerHTML = '⚠️ Подождите минуту перед повторной отправкой';
                        } 
                        else if(response.startsWith("DEMO_CODE:")) {
                            var demoCode = response.substring(10);
                            errorDiv.innerHTML = '<span class="code-success">✅ Код сгенерирован (демо-режим)</span>';
                            errorDiv.className = 'code-success';
                            
                            // Показываем демо-код
                            document.getElementById('demoInfo').style.display = 'block';
                            document.getElementById('demoCodeDisplay').textContent = demoCode;
                            
                            startResendTimer();
                        } 
                        else if(response === "ERROR_SESSION") {
                            errorDiv.innerHTML = 'Сессия истекла. Попробуйте войти снова';
                            setTimeout(function() {
                                hideCodeModal();
                            }, 2000);
                        } 
                        else {
                            errorDiv.innerHTML = 'Ошибка: ' + response;
                        }
                    },
                    error: function(xhr, status, error) {
                        errorDiv.innerHTML = '❌ Ошибка соединения с сервером';
                        console.error("Ошибка AJAX:", status, error);
                    }
                });
            }
            
            // Скрыть модальное окно
            function hideCodeModal() {
                document.getElementById('codeModal').style.display = 'none';
                
                // Очищаем таймер
                if(resendTimer) {
                    clearInterval(resendTimer);
                    resendTimer = null;
                }
                
                // Отменяем авторизацию на сервере
                $.ajax({
                    url: 'ajax/cancel_auth.php',
                    type: 'POST'
                });
            }
            
            // Проверка введенного кода
            function verifyCode() {
                if(codeLoading) return;
                
                var code = document.getElementById('authCode').value.trim();
                var errorDiv = document.getElementById('codeError');
                
                // Валидация кода
                if(code.length !== 6) {
                    errorDiv.innerHTML = '❌ Код должен содержать 6 цифр';
                    errorDiv.className = '';
                    return;
                }
                
                if(!/^\d+$/.test(code)) {
                    errorDiv.innerHTML = '❌ Код должен содержать только цифры';
                    errorDiv.className = '';
                    return;
                }
                
                // Начинаем проверку
                codeLoading = true;
                errorDiv.innerHTML = '<span style="color:#2196F3;">🔍 Проверка кода...</span>';
                
                var data = new FormData();
                data.append("code", code);
                
                $.ajax({
                    url: 'ajax/verify_code.php',
                    type: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        codeLoading = false;
                        console.log("Ответ verify_code:", response);
                        
                        if(response === "ERROR_CODE") {
                            errorDiv.innerHTML = '❌ Неверный код. Попробуйте еще раз';
                            errorDiv.className = '';
                            // Сбрасываем поле ввода
                            document.getElementById('authCode').value = '';
                            document.getElementById('authCode').focus();
                        } 
                        else if(response === "ERROR_EXPIRED") {
                            errorDiv.innerHTML = '⏰ Код истек. Попробуйте войти снова';
                            errorDiv.className = '';
                            setTimeout(function() {
                                hideCodeModal();
                            }, 3000);
                        } 
                        else if(response === "ERROR_SESSION") {
                            errorDiv.innerHTML = '🔓 Сессия истекла. Попробуйте войти снова';
                            errorDiv.className = '';
                            setTimeout(function() {
                                hideCodeModal();
                            }, 2000);
                        } 
                        else if(response.startsWith("SUCCESS:")) {
                            // Успешная авторизация
                            errorDiv.innerHTML = '<span class="code-success">✅ Авторизация успешна! Перенаправление...</span>';
                            errorDiv.className = 'code-success';
                            
                            // Перенаправляем через 1 секунду
                            setTimeout(function() {
                                window.location.href = "user.php";
                            }, 1000);
                        } 
                        else {
                            errorDiv.innerHTML = '⚠️ Неизвестный ответ: ' + response;
                            errorDiv.className = '';
                        }
                    },
                    error: function(xhr, status, error) {
                        codeLoading = false;
                        errorDiv.innerHTML = '❌ Ошибка соединения с сервером';
                        errorDiv.className = '';
                        console.error("Ошибка AJAX:", status, error);
                    }
                });
            }
            
            // Обработка Enter в поле кода
            document.getElementById('authCode').addEventListener('keypress', function(e) {
                if(e.keyCode === 13) {
                    verifyCode();
                }
            });
            
            // Основная функция авторизации
            function LogIn() {
                var loading = document.getElementsByClassName("loading")[0];
                var button = document.querySelector('input.button[value="Войти"]');
                
                var _login = document.getElementsByName("_login")[0].value.trim();
                var _password = document.getElementsByName("_password")[0].value;

                // Валидация полей
                if(_login === "") {
                    alert("⚠️ Введите логин.");
                    document.getElementsByName("_login")[0].focus();
                    return;
                }
                
                if(_password === "") {
                    alert("⚠️ Введите пароль.");
                    document.getElementsByName("_password")[0].focus();
                    return;
                }
                
                // Показываем загрузку
                loading.style.display = "block";
                button.className = "button_diactive";
                button.disabled = true;
                button.value = "Подождите...";
                
                var data = new FormData();
                data.append("login", _login);
                data.append("password", _password);
                
                $.ajax({
                    url: 'ajax/login_user.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'html',
                    processData: false,
                    contentType: false, 
                    success: function (_data) {
                        console.log("Ответ сервера:", _data);
                        
                        // Сбрасываем состояние кнопки
                        loading.style.display = "none";
                        button.className = "button";
                        button.disabled = false;
                        button.value = "Войти";
                        
                        // Нужен код подтверждения
                        if(_data === "NEED_CODE") {
                            document.getElementById('userEmail').textContent = 'пользователя';
                            showCodeModal();
                        } 
                        // Демо-режим (если почта не настроена)
                        else if(_data.startsWith("NEED_CODE_DEMO:")) {
                            var demoCode = _data.substring(15);
                            
                            document.getElementById('userEmail').textContent = 'пользователя';
                            document.getElementById('demoInfo').style.display = 'block';
                            document.getElementById('demoCodeDisplay').textContent = demoCode;
                            
                            showCodeModal();
                        }
                        // Ошибки
                        else if(_data === "ERROR_EMPTY") {
                            alert("⚠️ Заполните все поля");
                        }
                        else if(_data === "ERROR_AUTH") {
                            alert("❌ Неверный логин или пароль");
                            document.getElementsByName("_password")[0].value = '';
                            document.getElementsByName("_password")[0].focus();
                        }
                        else if(_data === "ERROR_EMAIL_NOT_FOUND") {
                            alert("⚠️ У пользователя не указан email. Обратитесь к администратору.");
                        }
                        else if(_data === "ERROR_EMAIL_SEND") {
                            alert("⚠️ Ошибка отправки кода. Попробуйте позже или обратитесь к администратору.");
                        }
                        // Успешная авторизация (старый формат)
                        else if(_data.length === 32 && /^[a-f0-9]{32}$/.test(_data)) {
                            // Сохраняем токен
                            localStorage.setItem("token", _data);
                            location.reload();
                        }
                        else {
                            alert("⚠️ Ошибка авторизации: " + _data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Системная ошибка!', status, error);
                        
                        loading.style.display = "none";
                        button.className = "button";
                        button.disabled = false;
                        button.value = "Войти";
                        
                        alert('❌ Произошла системная ошибка. Попробуйте позже или проверьте подключение к интернету.');
                    }
                });
            }
            
            // Обработка нажатия Enter в полях логина/пароля
            function PressToEnter(e) {
                if (e.keyCode == 13) {
                    var _login = document.getElementsByName("_login")[0].value.trim();
                    var _password = document.getElementsByName("_password")[0].value;
                    
                    if(_login === "" || _password === "") {
                        if(_login === "") {
                            alert("⚠️ Введите логин.");
                            document.getElementsByName("_login")[0].focus();
                        } else {
                            alert("⚠️ Введите пароль.");
                            document.getElementsByName("_password")[0].focus();
                        }
                        return;
                    }
                    
                    LogIn();
                }
            }
            
            // Автофокус на поле логина при загрузке
            window.onload = function() {
                document.getElementsByName("_login")[0].focus();
            };
        </script>
    </body>
</html>