<?php
	
	$peticionAjax = true;
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";

    use app\controllers\feriadosController;

	if(isset($_POST['modulo_feriado'])){
		$ins_feriado = new feriadosController();

		/*--------- Registrar feriado ---------*/
		if($_POST['modulo_feriado'] == "registrar"){
			echo $ins_feriado->registrarFeriadoControlador();
		}

		/*--------- Actualizar feriado ---------*/
		if($_POST['modulo_feriado'] == "actualizar"){
			echo $ins_feriado->actualizarFeriadoControlador();
		}

		/*--------- Eliminar feriado ---------*/
		if($_POST['modulo_feriado'] == "eliminar"){
			echo $ins_feriado->eliminarFeriadoControlador();
		}

	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}