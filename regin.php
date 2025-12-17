<?php
	session_start();
	include("./settings/connect_datebase.php");
	
	// ПЕРЕНЕСЕМ ОБРАБОТЧИК AJAX В НАЧАЛО ФАЙЛА
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
		// Это AJAX запрос
		$login = $_POST['login'];
		$password = $_POST['password'];
		$email = isset($_POST['email']) ? $_POST['email'] : '';
		
		// Функция для проверки пароля
		function validatePasswordPHP($password) {
			if(strlen($password) < 9) {
				return "Пароль должен содержать более 8 символов";
			}
			
			if(!preg_match('/[a-zA-Z]/', $password)) {
				return "Пароль должен содержать латинские буквы";
			}
			
			if(!preg_match('/[A-Z]/', $password)) {
				return "Пароль должен содержать хотя бы одну заглавную букву";
			}
			
			if(!preg_match('/[0-9]/', $password)) {
				return "Пароль должен содержать цифры";
			}
			
			if(!preg_match('/[!@#$%^&?*\-_=]/', $password)) {
				return "Пароль должен содержать специальные символы (!@#$%^&?*-_=)";
			}
			
			if(!preg_match('/^[a-zA-Z0-9!@#$%^&?*\-_=]+$/', $password)) {
				return "Пароль содержит недопустимые символы";
			}
			
			return true;
		}
		
		// Проверка email (если есть)
		if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			echo "ERROR:Введите корректный email";
			exit;
		}
		
		// Проверка пароля
		$passwordValidation = validatePasswordPHP($password);
		if($passwordValidation !== true) {
			echo "ERROR_PASSWORD:" . $passwordValidation;
			exit;
		}
		
		// Экранируем данные для безопасности
		$login = $mysqli->real_escape_string($login);
		$email = $mysqli->real_escape_string($email);
		
		// Проверяем, существует ли пользователь
		$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."'");
		$id = -1;
		
		if($query_user->num_rows > 0) {
			echo $id;
		} else {
			// Хешируем пароль
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			
			// Определяем, есть ли email в таблице
			$check_columns = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'email'");
			
			if($check_columns->num_rows > 0) {
				// Таблица имеет поле email
				$mysqli->query("INSERT INTO `users`(`login`, `password`, `email`, `roll`, `auth_code`) 
								VALUES ('".$login."', '".$hashed_password."', '".$email."', 0, 0)");
			} else {
				// Старая таблица без email
				$mysqli->query("INSERT INTO `users`(`login`, `password`, `roll`, `auth_code`) 
								VALUES ('".$login."', '".$hashed_password."', 0, 0)");
			}
			
			// Получаем ID нового пользователя
			$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."'");
			if($user_new = $query_user->fetch_row()) {
				$id = $user_new[0];
				$_SESSION['user'] = $id;
			}
			
			echo $id;
		}
		exit; // Завершаем выполнение для AJAX
	}
	
	// ОСТАЛЬНОЙ КОД СТРАНИЦЫ (только для GET запросов)
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
		<title> Регистрация </title>
		
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
				<div class = "login">
					<div class="name">Регистрация</div>
				
					<div class = "sub-name">Логин:</div>
					<input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
					
					<!-- ПРОВЕРЬТЕ, НУЖНО ЛИ ВАМ ПОЛЕ EMAIL -->
					<?php 
					// Проверяем, есть ли поле email в таблице
					$check_email = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'email'");
					if($check_email->num_rows > 0): ?>
					<div class = "sub-name">Email:</div>
					<input name="_email" type="email" placeholder="" onkeypress="return PressToEnter(event)"/>
					<?php endif; ?>
					
					<div class = "sub-name">Пароль:</div>
					<input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
					<div class = "sub-name">Повторите пароль:</div>
					<input name="_passwordCopy" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
					
					<a href="login.php">Вернуться</a>
					<input type="button" class="button" value="Зайти" onclick="RegIn()" style="margin-top: 0px;"/>
					<img src = "img/loading.gif" class="loading" style="margin-top: 0px;"/>
				</div>
				
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
			var loading = document.getElementsByClassName("loading")[0];
			var button = document.getElementsByClassName("button")[0];
			
			function RegIn() {
				var _login = document.getElementsByName("_login")[0].value.trim();
				var _password = document.getElementsByName("_password")[0].value;
				var _passwordCopy = document.getElementsByName("_passwordCopy")[0].value;
				
				// Проверяем, есть ли поле email
				var emailInputs = document.getElementsByName("_email");
				var _email = "";
				if(emailInputs.length > 0) {
					_email = emailInputs[0].value.trim();
				}
				
				if(_login === "") {
					alert("Введите логин.");
					return;
				}
				
				// Если есть поле email, проверяем его
				if(emailInputs.length > 0 && _email === "") {
					alert("Введите email.");
					return;
				}
				
				if(emailInputs.length > 0 && _email !== "") {
					var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					if(!emailRegex.test(_email)) {
						alert("Введите корректный email.");
						return;
					}
				}
				
				if(_password === "") {
					alert("Введите пароль.");
					return;
				}
				
				// Проверка пароля
				if(CheckPassword(_password) === false) {
					alert("Пароль не соответствует требованиям");
					return;
				}
				
				if(_password !== _passwordCopy) {
					alert("Пароли не совпадают.");
					return;
				}
				
				loading.style.display = "block";
				button.className = "button_diactive";
				
				var data = new FormData();
				data.append("login", _login);
				data.append("password", _password);
				
				// Добавляем email только если поле есть
				if(emailInputs.length > 0) {
					data.append("email", _email);
				}
				
				// AJAX запрос
				$.ajax({
					url         : 'regin.php', // Тот же файл
					type        : 'POST',
					data        : data,
					cache       : false,
					dataType    : 'html',
					processData : false,
					contentType : false, 
					success: function (_data) {
						console.log("Ответ сервера: " + _data);

						if(_data.startsWith("ERROR:")) {
							var errorMessage = _data.substring(6);
							alert(errorMessage);
						} else if(_data.startsWith("ERROR_PASSWORD:")) {
							var errorMessage = _data.substring(15);
							alert("Ошибка в пароле: " + errorMessage);
						} else if(_data === "-1") {
							alert("Пользователь с таким логином существует.");
						} else {
							// Успешная регистрация
							alert("Регистрация успешна!");
							window.location.href = "user.php";
							return;
						}
						
						loading.style.display = "none";
						button.className = "button";
					},
					error: function( ){
						console.log('Системная ошибка!');
						alert('Произошла системная ошибка. Попробуйте позже.');
						loading.style.display = "none";
						button.className = "button";
					}
				});
			}
			
			function CheckPassword(value) {
				if(value.length < 9) {
					return false;
				}
			
				if(!/[a-zA-Z]/.test(value)) {
					return false;
				}
			
				if(!/[A-Z]/.test(value)) {
					return false;
				}
			
				if(!/[0-9]/.test(value)) {			
					return false;
				}
			
				if(!/[!@#$%^&?*\-_=]/.test(value)) {			
					return false;
				}
			
				if(!/^[a-zA-Z0-9!@#$%^&?*\-_=]+$/.test(value)) {			
					return false;
				}
			
				return true;			
			}
			
			function PressToEnter(e) {
				if (e.keyCode == 13) {
					var _login = document.getElementsByName("_login")[0].value;
					var _password = document.getElementsByName("_password")[0].value;
					var _passwordCopy = document.getElementsByName("_passwordCopy")[0].value;
					
					if(_password != "") {
						if(_login != "") {
							if(_passwordCopy != "") {
								RegIn();
							}
						}
					}
				}
			}
			
		</script>
	</body>
</html>