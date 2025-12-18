<?php
session_start();
include("./settings/connect_datebase.php");

if (isset($_SESSION['user'])) {
    if($_SESSION['user'] != -1) {
        
        $user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$_SESSION['user']);
        while($user_read = $user_query->fetch_row()) {
            if($user_read[3] == 0) header("Location: user.php");
            else if($user_read[3] == 1) header("Location: admin.php");
        }
    }
 }
?>
<html>
    <head> 
        <meta charset="utf-8">
        <title> Авторизация </title>
        
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="top-menu">
            <a href=#><img src = "img/logo1.png"/></a>
            <div class="name">
                <a href="index.php">
                    <div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
                    Пермский авиационный техникум им. А. Д. Швецова
                </a>
            </div>
        </div>
        <div class="space"> </div>
        <div class="main">
            <div class="content">
                <!-- Форма для ввода логина и пароля -->
                <div class = "login" id="loginForm">
                    <div class="name">Авторизация</div>
                
                    <div class = "sub-name">Логин:</div>
                    <input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
                    <div class = "sub-name">Пароль:</div>
                    <input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
                    
                    <a href="regin.php">Регистрация</a>
                    <br><a href="recovery.php">Забыли пароль?</a>
                    <input type="button" class="button" value="Войти" onclick="sendAuthCode()"/>
                    <img src = "img/loading.gif" class="loading"/>
                </div>
                
                <!-- Форма для ввода кода подтверждения (изначально скрыта) -->
                <div class = "login" id="codeForm" style="display: none;">
                    <div class="name">Введите код из email</div>
                    
                    <div class = "sub-name">6-значный код:</div>
                    <input name="_auth_code" type="text" placeholder="" maxlength="6" onkeypress="return PressToEnterCode(event)"/>
                    
                    <input type="button" class="button" value="Подтвердить" onclick="verifyAuthCode()"/>
                    <a href="javascript:void(0)" onclick="showLoginForm()">Вернуться к авторизации</a>
                    <img src = "img/loading.gif" class="loading"/>
                </div>
                
                <div class="footer">
                    © КГАПОУ "Авиатехникум", 2020
                    <a href=#>Конфиденциальность</a>
                    <a href=#>Условия</a>
                </div>
            </div>
        </div>
        
        <script>
            // Функция для отправки запроса на получение кода
            function sendAuthCode() {
                var loading = document.getElementsByClassName("loading")[0];
                var button = document.getElementsByClassName("button")[0];
                
                var _login = document.getElementsByName("_login")[0].value;
                var _password = document.getElementsByName("_password")[0].value;
                
                if(_login == "" || _password == "") {
                    alert("Заполните все поля");
                    return;
                }
                
                loading.style.display = "block";
                button.className = "button_diactive";
                
                var data = new FormData();
                data.append("login", _login);
                data.append("password", _password);
                
                $.ajax({
                    url: 'ajax/send_auth_code.php',
                    type: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        loading.style.display = "none";
                        button.className = "button";
                        
                        try {
                            var data = JSON.parse(response);
                            if(data.success) {
                                // Показываем форму для ввода кода
                                document.getElementById("loginForm").style.display = "none";
                                document.getElementById("codeForm").style.display = "block";
                            } else {
                                alert(data.message);
                            }
                        } catch(e) {
                            alert("Ошибка обработки ответа сервера");
                        }
                    },
                    error: function() {
                        loading.style.display = "none";
                        button.className = "button";
                        alert("Системная ошибка!");
                    }
                });
            }
            
            // Функция для проверки введенного кода
            function verifyAuthCode() {
                var loading = document.getElementById("codeForm").getElementsByClassName("loading")[0];
                var button = document.getElementById("codeForm").getElementsByClassName("button")[0];
                var code = document.getElementsByName("_auth_code")[0].value;
                
                if(code.length !== 6) {
                    alert("Введите 6-значный код");
                    return;
                }
                
                loading.style.display = "block";
                button.className = "button_diactive";
                
                var data = new FormData();
                data.append("code", code);
                
                $.ajax({
                    url: 'ajax/verify_auth_code.php',
                    type: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        loading.style.display = "none";
                        button.className = "button";
                        
                        try {
                            var data = JSON.parse(response);
                            if(data.success) {
                                localStorage.setItem("token", data.token);
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        } catch(e) {
                            alert("Ошибка обработки ответа сервера");
                        }
                    },
                    error: function() {
                        loading.style.display = "none";
                        button.className = "button";
                        alert("Системная ошибка!");
                    }
                });
            }
            
            // Функция для возврата к форме авторизации
            function showLoginForm() {
                document.getElementById("loginForm").style.display = "block";
                document.getElementById("codeForm").style.display = "none";
            }
            
            // Обработка нажатия Enter в форме авторизации
            function PressToEnter(e) {
                if (e.keyCode == 13) {
                    var _login = document.getElementsByName("_login")[0].value;
                    var _password = document.getElementsByName("_password")[0].value;
                    
                    if(_password != "") {
                        if(_login != "") {
                            sendAuthCode();
                        }
                    }
                }
            }
            
            // Обработка нажатия Enter в форме ввода кода
            function PressToEnterCode(e) {
                if (e.keyCode == 13) {
                    var code = document.getElementsByName("_auth_code")[0].value;
                    if(code.length === 6) {
                        verifyAuthCode();
                    }
                }
            }
            
        </script>
    </body>
</html>