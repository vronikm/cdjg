<?php

    require_once "./config/app.php";
    require_once "./autoload.php";

    /*---------- Iniciando sesion ----------*/
    require_once "./app/views/inc/session_start.php";

    if(isset($_GET['views'])){
        $url=explode("/", $_GET['views']);
    }else{
        $url=["login"];
    }

    use app\controllers\viewsController;
    use app\controllers\loginController;

    $insLogin = new loginController();

    $viewsController= new viewsController();
    $vista=$viewsController->obtenerVistasControlador($url[0]);

    if($vista=="login" || $vista=="404"){
        require_once "app/views/content/".$vista."-view.php";
    }else{

      # Cerrar sesion #
      if((!isset($_SESSION['usuario']) || $_SESSION['usuario']=="")){
          $insLogin->cerrarSesionControlador();
          exit();
      }
      
      require_once $vista;

    } 
