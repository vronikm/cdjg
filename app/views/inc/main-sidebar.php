<!-- Main Sidebar Container -->
<?php 
  use app\controllers\menuController;
  $insGenerar = new menuController();	

    $nombre= ($_SESSION['sede'] != "") ? $_SESSION['sede'] : "CD Jorge Guzman";
    $session_rolid= $_SESSION['rol'];
    $usuario_login=$_SESSION['usuario'];

    if($usuario_login != ""){
      if ($session_rolid <> 1 && $session_rolid <> 2){
        $GenerarMenu=$insGenerar->ObtenerMenu($usuario_login);		
        // Generar el menú dinámico
        $menuHTML = $insGenerar->ConstruirMenu($GenerarMenu);
      }
    }else{
      session_destroy();
		  header("Location: ".APP_URL."login/");
    }
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png" alt="CDJorgeGuzman Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light"><?php echo $nombre; ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">               
              
          <?php
            if ($session_rolid <> 1 && $session_rolid <> 2){
              echo $menuHTML;  
            } else{
              require_once "app/views/inc/menu_admin.php";
            } 
          ?>
          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->

    </div>
    <!-- /.sidebar -->
</aside>