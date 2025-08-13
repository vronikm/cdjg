<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href=<?php echo APP_URL."alumnoNew/";?> class="nav-link ">Nuevo alumno</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href=<?php echo APP_URL."alumnoList/";?> class="nav-link ">Buscar alumno</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href=<?php echo APP_URL."pagosList/";?> class="nav-link ">Registrar pago</a>
        </li>    
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">       
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                 <!--i class="nav-icon far fa-futbol text-info"></i-->
                <div class="user-panel pb-2">                   
                    <?php
                        if(is_file("app/views/imagenes/fotos/empleado/".$_SESSION['foto'])){
                            echo '<img class="img-circle elevation-2" alt="User Image" src="'.APP_URL.'app/views/imagenes/fotos/empleado/'.$_SESSION['foto'].'">';
                        }else{
                            echo '<img class="img-circle elevation-2" alt="User Image" src="'.APP_URL.'app/views/dist/img/default.png">';
                        }
                    ?>       
                    <span ><?php echo  $_SESSION['usuario'];?></span>                             
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-xs dropdown-menu-right">              
                <!--a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> 4 new messages                    
                </a>
                <div class="dropdown-divider"></div-->
                <a href="#" class="dropdown-item" data-target="#modal-default" data-toggle="modal">
                    <i class="fas fa-key mr-2"></i> Cambiar contraseña                   
                </a>
                <div class="dropdown-divider"></div>
                <a href=<?php echo APP_URL."logOut/";?> class="dropdown-item" id="btn_exit">
                    <i class="fas fa-times mr-2"></i> Salir                  
                </a>                
            </div>
        </li>     
    </ul>  
</nav>

<div class="modal fade" id="modal-default">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/usuarioAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
                <input type="hidden" name="modulo_usuario" value="CAMBIAR_CLAVE">
                <input type="hidden" name="usuario" value="<?php echo $_SESSION['usuario']; ?>">
                <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuarioid']; ?>">              
                <div class="modal-header">
                    <h6 class="modal-title">Cambiar contraseña</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usuario_clave">Contraseña Actual</label>
                        <input type="password" class="form-control" id="usuario_clave" name="usuario_clave" required utocomplete="off">	
                    </div>
                    <div class="form-group">
                        <label for="usuario_clave_nueva">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="usuario_clave_nueva" name="usuario_clave_nueva" required utocomplete="off">	
                    </div>
                    <div class="form-group">
                        <label for="usuario_clave_confirmar">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="usuario_clave_confirmar" name="usuario_clave_confirmar" required utocomplete="off">	
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cerrar</button>                 
                    <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
<!-- /.modal-dialog -->
</div>

<script>
    document.getElementById('usuario_clave_confirmar').addEventListener('input', function() {
        let claveNueva = document.getElementById('usuario_clave_nueva').value.trim();
        let claveConfirmar = this.value.trim();

        if (claveNueva !== claveConfirmar) {
            this.setCustomValidity("Las contraseñas no coinciden");
        } else {
            this.setCustomValidity("");
        }
    });
</script>
<!-- /.navbar -->