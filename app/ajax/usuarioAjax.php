<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\userController;

	if(isset($_POST['modulo_usuario'])){

		$insUsuario = new userController();

		if($_POST['modulo_usuario']=="registrar"){
			echo $insUsuario->registrarUsuarioControlador();
		}

		if($_POST['modulo_usuario']=="actualizar"){
			echo $insUsuario->actualizarUsuarioControlador();
		}

		if($_POST['modulo_usuario']=="crearRol"){
			echo $insUsuario->crearRol();
		}

		if($_POST['modulo_usuario']=="actualizarRol"){
			echo $insUsuario->actualizarRol();
		}

		if($_POST['modulo_usuario']=="eliminarRol"){
			echo $insUsuario->eliminarRol();
		}		
		#-----------------------------validar---------------------------
		if($_POST['modulo_usuario']=="eliminarFoto"){
			echo $insUsuario->eliminarFotoUsuarioControlador();
		}

		if($_POST['modulo_usuario']=="actualizarFoto"){
			echo $insUsuario->actualizarFotoUsuarioControlador();
		}

		if($_POST['modulo_usuario']=="actualizarestado"){
			echo $insUsuario->actualizarUsuarioEstadoControlador();
		}
		
		if($_POST['modulo_usuario']=="CAMBIAR_CLAVE"){
			echo $insUsuario->actualizarClaveUsuarioControlador();
		}		

	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}