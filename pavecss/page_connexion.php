<?php
session_start();
?>
<html>
<head>
<title>Connexion-ProjetPHP</title>
<!-- Custom Theme files -->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all"/>
<!-- Custom Theme files -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="keywords" content="Login form web template, Sign up Web Templates, Flat Web Templates, Login signup Responsive web template, Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design" />
<!--Google Fonts-->
<link href='http://fonts.useso.com/css?family=Roboto:500,900italic,900,400italic,100,700italic,300,700,500italic,100italic,300italic,400' rel='stylesheet' type='text/css'>
<link href='http://fonts.useso.com/css?family=Droid+Serif:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
<!--Google Fonts-->
</head>
<body>
<div class="login">
	<h2>Bienvenue !</h2>
	<div class="login-top">
		<h1>ProjetPHP</h1>
		<?php
            echo "
		<form  method=\"post\" action=\"page_accueil.php\">
			<input type=\"text\" name=\"nom\" value=\"nom\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'User Id';}\">
			<input type=\"text\" name=\"prenom\" value=\"prenom\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'User Id';}\">
			<input type=\"text\" name=\"id\" value=\"numéro d'étudiant\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'User Id';}\">
			<input type=\"text\" name=\"mdp\" value=\"mot de passe\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'User Id';}\">
			 <input type=\"submit\" value=\"Se connecter\" style=\"float:center\">
	    </form>
		"?>
	    
	</div>
	<div class="login-bottom">
		<h3><a href="#">s'inscrire</a></h3>
	</div>
</div>	


</body>
</html>