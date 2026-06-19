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
        $menuHTML = $insGenerar->ConstruirMenu($GenerarMenu, $url[0] ?? '');
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  var sidebar = document.querySelector('.main-sidebar .sidebar');
  var menu = document.querySelector('.main-sidebar .nav-sidebar');
  var storageKey = 'cdjg.sidebar.scrollTop';

  if (!sidebar || !menu || !window.sessionStorage) {
    return;
  }

  function saveSidebarPosition() {
    sessionStorage.setItem(storageKey, String(sidebar.scrollTop));
  }

  function getDirectNavLink(item) {
    for (var i = 0; i < item.children.length; i++) {
      if (item.children[i].matches && item.children[i].matches('a.nav-link')) {
        return item.children[i];
      }
    }

    return null;
  }

  function openActiveBranches() {
    var activeLinks = menu.querySelectorAll('.nav-treeview .nav-link.active');

    activeLinks.forEach(function (activeLink) {
      var treeview = activeLink.closest('.nav-treeview');

      while (treeview && menu.contains(treeview)) {
        var parentItem = treeview.closest('.nav-item');

        if (!parentItem) {
          break;
        }

        parentItem.classList.add('menu-open');

        var parentLink = getDirectNavLink(parentItem);
        if (parentLink) {
          parentLink.classList.add('active');
        }

        treeview = parentItem.parentElement ? parentItem.parentElement.closest('.nav-treeview') : null;
      }
    });
  }

  function keepActiveItemVisible() {
    var activeLinks = menu.querySelectorAll('.nav-link.active');
    var activeLink = activeLinks.length ? activeLinks[activeLinks.length - 1] : null;

    if (!activeLink) {
      return;
    }

    var sidebarRect = sidebar.getBoundingClientRect();
    var activeRect = activeLink.getBoundingClientRect();
    var offset = 16;

    if (activeRect.top < sidebarRect.top) {
      sidebar.scrollTop -= (sidebarRect.top - activeRect.top) + offset;
    } else if (activeRect.bottom > sidebarRect.bottom) {
      sidebar.scrollTop += (activeRect.bottom - sidebarRect.bottom) + offset;
    }
  }

  function restoreSidebarPosition() {
    openActiveBranches();

    var savedScrollTop = parseInt(sessionStorage.getItem(storageKey), 10);

    if (!Number.isNaN(savedScrollTop)) {
      sidebar.scrollTop = savedScrollTop;
    }

    keepActiveItemVisible();
  }

  menu.addEventListener('click', function (event) {
    if (event.target.closest('a.nav-link')) {
      saveSidebarPosition();
    }
  }, true);

  sidebar.addEventListener('scroll', saveSidebarPosition, { passive: true });
  window.addEventListener('beforeunload', saveSidebarPosition);

  openActiveBranches();
  window.requestAnimationFrame(restoreSidebarPosition);
  window.setTimeout(restoreSidebarPosition, 250);
});
</script>
