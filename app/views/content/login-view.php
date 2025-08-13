<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo APP_NAME; ?> | Login</title>

  <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
  <link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">

</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
	<img src="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png" alt="ClubJorgeGuzman Logo" class="img-fluid" style="width: 250px; height: 298px;">
      <a href="index.html" class="h1"></a>
    </div>
    <div class="card-body">
      <form class="box login" action="" method="POST" autocomplete="off" >
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="login_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="15" placeholder="Usuario" required >
          <div class="input-group-append">
            <div class="input-group-text">
              <!-- Icono -->
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="login_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" placeholder="Contraseña"  required >
          <div class="input-group-append">
            <div class="input-group-text">
              <!-- Icono -->
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
          <!-- /.col -->
          <div class="col-14">
            <button type="submit" class="btn btn-info btn-block">Iniciar sesión</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
      <!-- /.social-auth-links -->
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->  

<!-- jQuery -->
<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>

<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>
</body>
</html>

<?php
	if(isset($_POST['login_usuario']) && isset($_POST['login_clave'])){
		$insLogin->iniciarSesionControlador();
	}
?>