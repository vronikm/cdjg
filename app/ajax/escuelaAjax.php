<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";

	use app\controllers\escuelaController;

	if(isset($_POST['modulo_escuela'])){

		$insEscuela = new escuelaController();

		if($_POST['modulo_escuela']=="registrar"){
			echo $insEscuela->registrarEscuelaControlador();
		}

		if($_POST['modulo_escuela']=="eliminar"){
			echo $insEscuela->eliminarEscuelaControlador();
		}

		if($_POST['modulo_escuela']=="actualizar"){
			echo $insEscuela->actualizarEscuelaControlador();
		}
		
	}
	elseif(isset($_POST['modulo_sede'])){
		$insSede = new escuelaController();

		if($_POST['modulo_sede']=="registrar"){
			echo $insSede->registrarSedeControlador();
		}

		if($_POST['modulo_sede']=="actualizar"){
			echo $insSede->actualizarSedeControlador();
		}
		if($_POST['modulo_sede']=="eliminar"){
			echo $insSede->eliminarSedeControlador();
		}
	}	
	else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}
	