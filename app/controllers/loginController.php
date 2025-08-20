<?php

	namespace app\controllers;
	use app\models\mainModel;
	
	class loginController extends mainModel{

		/*----------  Controlador iniciar sesion  ----------*/
		/**
		 * Inicia sesión del usuario.
		 *  - Valida formato de usuario y contraseña.
		 *  - Usa consulta preparada (evita SQL-Injection).
		 *  - Verifica estado y bloqueo.
		 *  - Regenera el ID de sesión.
		 *  - Redirige según el rol.
		 */
		public function iniciarSesionControlador(): void
		{
			// Arranque de sesión
			if (session_status() === PHP_SESSION_NONE) {
				session_set_cookie_params([
					'lifetime' => 3600,
					'path' => '/',
					'domain' => 'clubjorgeguzman.digitech.com.ec',
					'secure' => true,
					'httponly' => true,
					'samesite' => 'Strict'
				]);
				session_start();
			}

			/* ----------  1. Validación básica de entrada  ---------- */
			$usuario = $_POST['login_usuario'] ?? '';
			$clave   = $_POST['login_clave']   ?? '';

			if ($usuario === '' || $clave === '') {
				$this->showError('Debes rellenar todos los campos.');
			}

			if (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $usuario)) {
				$this->showError('El usuario no cumple el formato solicitado.');
			}

			if (!preg_match('/^[a-zA-Z0-9$@.\-]{7,100}$/', $clave)) {
				$this->showError('La contraseña no cumple el formato solicitado.');
			}

			/* ----------  2. Consulta preparada  ---------- */
			try {
				$sql = "
					SELECT  usuario_empleadoid,
							empleado_identificacion,
							empleado_nombre,
							empleado_correo,
							empleado_celular,
							empleado_foto,
							sede_nombre AS sede,
							usuario_estado,
							usuario_tienebloqueo,
							usuario_usuario,
							usuario_rolid,
							usuario_clave,
							usuario_id
					FROM    seguridad_usuario
					LEFT    JOIN sujeto_empleado ON empleado_id   = usuario_empleadoid
					LEFT    JOIN general_sede    ON empleado_sedeid = sede_id
					WHERE   usuario_usuario = :usuario
					LIMIT   1";

				$stmt = $this->ejecutarConsulta($sql, ['usuario' => $usuario]);

				if ($stmt->rowCount() !== 1) {
					/* ───── mensaje genérico: evita enumeración de usuarios ───── */
					$this->showError('Usuario o contraseña incorrectos.');
				}

				$user = $stmt->fetch();

			} catch (\Throwable $e) {
				/* Aquí podrías loguear $e->getMessage() */
				$this->showError('Ocurrió un error inesperado. Inténtalo de nuevo.');
			}

			/* ----------  3. Comprobaciones de estado ---------- */
			if ($user['usuario_estado'] === 'I') {
				$this->showError('Usuario inactivo. Contacte al administrador.');
			}
			if ($user['usuario_tienebloqueo'] === 'S') {
				$this->showError('Usuario bloqueado. Contacte al administrador.');
			}
			if (!password_verify($clave, $user['usuario_clave'])) {
				$this->showError('Usuario o contraseña incorrectos.');
			}

			/* ----------  4. Login correcto ---------- */
			session_regenerate_id(true);        // Previene Session Fixation

			$_SESSION = [
				'usuario'        => $user['usuario_usuario'],
				'usuarioid'      => $user['usuario_id'],
				'rol'            => (int)$user['usuario_rolid'],
				'foto'           => $user['empleado_foto'],
				'sede'           => $user['sede'],
				'identificacion' => $user['empleado_identificacion'],
				'usuario_id'     => $user['usuario_empleadoid'],
				'nombre'         => $user['empleado_nombre'] ?: $user['usuario_rolid'],
			];

			/* ----------  5. Redirección según rol ---------- */
			$destino = match ($_SESSION['rol']) {
				//4       => APP_URL . 'representanteList/',
				1, 2, 4, 5 => APP_URL . 'dashboard/',
				default => APP_URL . 'empleadoEntrada/',
			};

			$this->redirect($destino);
		}

		/* ========== Helpers ========== */

		/** Muestra un SweetAlert2 de error y detiene la ejecución. */
		private function showError(string $mensaje): void
		{
			echo "<script>
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: '{$mensaje}'
					});
				</script>";
			exit;
		}

		/** Redirige de forma segura, compatible con cabeceras ya enviadas. */
		private function redirect(string $url): void
		{
			if (headers_sent()) {
				echo "<script>window.location.href='{$url}';</script>";
				echo "<noscript><meta http-equiv='refresh' content='0;url={$url}'></noscript>";
			} else {
				header("Location: {$url}");
			}
			exit;
		}



		/*----------  Controlador cerrar sesion  ----------*/
		public function cerrarSesionControlador() {
			// Inicia la sesión si aún no está activa
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}

			// Limpia las variables de sesión
			session_unset();

			// Destruye la sesión
			session_destroy();

			// Asegura que no quede nada en memoria
			$_SESSION = [];    

			// Borra manualmente la cookie de sesión
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}

			// Redirige al login
			$urlLogin = APP_URL . "login/";

			if (headers_sent()) {
				echo "<script>window.location.href='" . $urlLogin . "';</script>";
				echo "<noscript><meta http-equiv='refresh' content='0;url=$urlLogin'></noscript>";
			} else {
				header("Location: $urlLogin");
			}

			exit();
		}


	}