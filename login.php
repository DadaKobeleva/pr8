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
				<div class = "login">
					<div class="name">Авторизация</div>
				
					<div class = "sub-name">Логин:</div>
					<input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
					<div class = "sub-name">Пароль:</div>
					<input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
					
					<a href="regin.php">Регистрация</a>
					<br><a href="recovery.php">Забыли пароль?</a>
					<input type="button" class="button" value="Войти" onclick="LogIn()"/>
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
    		function LogIn() {
        	var loading = document.getElementsByClassName("loading")[0];
        	var button = document.getElementsByClassName("button")[0];
        
        	var _login = document.getElementsByName("_login")[0].value.trim();
        	var _password = document.getElementsByName("_password")[0].value;

        
        	if(_login === "") {
            	alert("Введите логин.");
            	return;
        	}
        
        	if(_password === "") {
            	alert("Введите пароль.");
            	return;
        	}
        
        	loading.style.display = "block";
        	button.className = "button_diactive";
        
        	var data = new FormData();
        	data.append("login", _login);
        	data.append("password", _password);
        
        	// AJAX запрос
        	$.ajax({
            	url         : 'ajax/login_user.php',
            	type        : 'POST',
            	data        : data,
            	cache       : false,
            	dataType    : 'html',
            	processData : false,
            	contentType : false, 
            	success: function (_data) {
            	    console.log("Ответ сервера: " + _data);

            	    if(_data.length === 32 && /^[a-f0-9]{32}$/.test(_data)) {
            	        localStorage.setItem("token", _data);
            	        location.reload();
            	        loading.style.display = "none";
            	        button.className = "button";
            	    } 
            	    else if(_data === "ERROR_EMPTY") {
            	        alert("Заполните все поля");
            	        loading.style.display = "none";
            	        button.className = "button";
            	    }
            	    else if(_data === "ERROR_AUTH") {
            	        alert("Неверный логин или пароль");
            	        loading.style.display = "none";
            	        button.className = "button";
            	    }
            	    else if(_data === "Неверный логин или пароль") {
            	        alert("Неверный логин или пароль");
            	        loading.style.display = "none";
            	        button.className = "button";
            	    }
            	    else if(_data === "Заполните все поля") {
            	        alert("Заполните все поля");
            	        loading.style.display = "none";
            	        button.className = "button";
            	    }
            	    else {
            	        alert("Ошибка авторизации: " + _data);
            	        loading.style.display = "none";
            	        button.className = "button";
            	    }
            	},
            	error: function( ){
                	console.log('Системная ошибка!');
                	alert('Произошла системная ошибка. Попробуйте позже.');
                	loading.style.display = "none";
                	button.className = "button";
            	}
        	});
    	}
    
    	function PressToEnter(e) {
        	if (e.keyCode == 13) {
            	var _login = document.getElementsByName("_login")[0].value.trim();
            	var _password = document.getElementsByName("_password")[0].value;
            
           	 	if(_login === "" || _password === "") {
                	if(_login === "") {
                    	alert("Введите логин.");
                	} else {
                    	alert("Введите пароль.");
                	}
                	return;
            	}
            
            	LogIn();
        	}
    	}
		</script>
	</body>
</html>