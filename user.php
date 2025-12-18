<?php
session_start();
include("./settings/connect_datebase.php");
include("./check_session.php"); // Добавьте эту строку

// Проверка единой сессии
if (!isset($_SESSION['user']) || !checkSingleSession($_SESSION['user'], $mysqli)) {
    header("Location: login.php");
    exit;
}

// Старая проверка (оставляем для совместимости)
if (isset($_SESSION['user'])) {
    if($_SESSION['user'] == -1) {
        header("Location: login.php");
        exit;
    } else {
        // проверяем пользователя, если админ выкидываем на админа
        $user_to_query = $mysqli->query("SELECT `roll` FROM `users` WHERE `id` = ".$_SESSION['user']);
        $user_to_read = $user_to_query->fetch_row();
        
        if($user_to_read[0] == 1) {
            header("Location: admin.php");
            exit;
        }
    }
} else {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE HTML>
<html>
    <head> 
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <meta charset="utf-8">
        <title> Личный кабинет </title>
        
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="top-menu">
            <a href=# class = "singin"><img src = "img/ic-login.png"/></a>
        
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
                <!-- Изменяем кнопку выхода -->
                <a href="logout.php" class="button" style="display: inline-block; text-decoration: none; text-align: center;">Выйти</a>
                
                <div class="name" style="padding-bottom: 0px;">Личный кабинет</div>
                <div class="description">Добро пожаловать: 
                    <?php
                        $user_to_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$_SESSION['user']);
                        $user_to_read = $user_to_query->fetch_row();
                        
                        echo $user_to_read[1];
                    ?>
                    <br>Ваш идентификатор:
                    <?php
                        echo $user_to_read[0];
                    ?>
                </div>
            
                <div class="footer">
                    © КГАПОУ "Авиатехникум", 2020
                    <a href=#>Конфиденциальность</a>
                    <a href=#>Условия</a>
                </div>
            </div>
        </div>
        
        <script>
            var id_statement = -1;
            function DeleteStatementt(id_statement) {
                if(id_statement != -1) {
                    
                    var data = new FormData();
                    data.append("id_statement", id_statement);
                    
                    // AJAX запрос
                    $.ajax({
                        url         : 'ajax/delete_statement.php',
                        type        : 'POST', // важно!
                        data        : data,
                        cache       : false,
                        dataType    : 'html',
                        // отключаем обработку передаваемых данных, пусть передаются как есть
                        processData : false,
                        // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
                        contentType : false, 
                        // функция успешного ответа сервера
                        success: function (_data) {
                            console.log(_data);
                            location.reload();
                        },
                        // функция ошибки
                        error: function(){
                            console.log('Системная ошибка!');
                        }
                    });
                }
            }
            
            // Убираем старую функцию logout, так как теперь используем прямую ссылку
            // function logout() {
            //     $.ajax({
            //         url         : 'ajax/logout.php',
            //         type        : 'POST', // важно!
            //         data        : null,
            //         cache       : false,
            //         dataType    : 'html',
            //         processData : false,
            //         contentType : false, 
            //         success: function (_data) {
            //             location.reload();
            //         },
            //         error: function( ){
            //             console.log('Системная ошибка!');
            //         }
            //     });
            // }
        </script>
    </body>
</html>