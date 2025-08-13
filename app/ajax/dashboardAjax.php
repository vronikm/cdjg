<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";

	use app\controllers\dashboardController;

	if(isset($_POST['modulo_dashboard'])){

		$insDashboard = new dashboardController();

		if($_POST['modulo_dashboard']=="totales"){
			echo $insDashboard->obtenerAlumnosActivosSedeL();
		}

		if($_POST['modulo_dashboard']=="eliminar"){
			echo $insEscuela->eliminarEscuelaControlador();
		}

		if($_POST['modulo_dashboard']=="actualizar"){
			echo $insEscuela->actualizarEscuelaControlador();
		}
		
	}
	else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}