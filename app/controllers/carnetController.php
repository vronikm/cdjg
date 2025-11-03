<?php
/**
 * ============================================================
 * CONTROLADOR DE CARNETS - Sistema de Colores por Mes
 * ============================================================
 * Funcionalidades:
 * - Asignar colores únicos a cada mes
 * - Validar que no haya carnets emitidos antes de modificar
 * - Prevenir colores duplicados entre meses
 * ============================================================
 */

/**
 * Listar colores del CATÁLOGO disponibles para asignar a un mes
 * @param int $color_id_actual Color actualmente asignado al mes
 * @param int $mes_actual Mes que se está configurando
 * @return string HTML con opciones del select
 */
	namespace app\controllers;
	use app\models\mainModel;
	use Exception;
	
	class carnetController extends mainModel{
        public function informacionSede($sedeid){		
            $consulta_datos="SELECT *, escuela_nombre, escuela_verticalfondo, escuela_verticalprincipal, escuela_verticalcolor
								 FROM general_sede 
								 INNER JOIN general_escuela on escuela_id = sede_escuelaid 
								 WHERE sede_id  = $sedeid";
            $datos = $this->ejecutarConsulta($consulta_datos);		
            return $datos;
        }

        public function listarAlumnos(){
			$tabla="";

			$consulta_datos = "SELECT alumno_id, alumno_identificacion, 
									CONCAT(alumno_primernombre, ' ', alumno_segundonombre) NOMBRES, 
									CONCAT(alumno_apellidopaterno, ' ', alumno_apellidomaterno) APELLIDOS, 
									alumno_carnet, 
									FechaUltPension, 
									CASE WHEN FechaUltPension >= CURDATE() THEN 'Al día' ELSE 'Pendiente' END Condicion
									FROM sujeto_alumno
									INNER JOIN (        
										SELECT pago_alumnoid, MAX(FechaPension) FechaUltPension, MAX(pago_estado) Estado
											FROM (
												SELECT MAX(pago_fecha) FechaPension, pago_estado, pago_alumnoid                               
													FROM alumno_pago 
													WHERE pago_estado <> 'E'
													GROUP BY pago_estado, pago_alumnoid
											) AS Pagos
											GROUP BY pago_alumnoid
										) EstadoPagos ON pago_alumnoid = alumno_id
									WHERE alumno_estado = 'A'";
													
			$datos = $this->ejecutarConsulta($consulta_datos);
		
			if($datos->rowCount()>0){
				$datos = $datos->fetchAll();
			}

			foreach($datos as $rows){	
		
				$tabla.='				
					<tr>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['NOMBRES'].'</td>
						<td>'.$rows['APELLIDOS'].'</td>
                        <td>'.$rows['alumno_carnet'].'</td>
						<td>'.$rows['FechaUltPension'].'</td>
						<td>'.$rows['Condicion'].'</td>
						<td>							
							<a href="'.APP_URL.'carnetFoto/'.$rows['alumno_id'].'/" class="btn float-right btn-secondary btn-xs" style="margin-right: 5px;">Ver carnet</a>	
							<a href="'.APP_URL.'representanteVinc/'.$rows['alumno_id'].'/" class="btn float-right btn-warning btn-xs" style="margin-right: 5px;">Imprimir</a>
						</td>						
					</tr>';	
			}
			return $tabla;			
		}

		public function infoAlumnoCarnet($alumnoid){		
            $consulta_datos="SELECT alumno_identificacion, 
									CONCAT(alumno_primernombre, ' ', alumno_segundonombre) Nombres, 
									CONCAT(alumno_apellidopaterno, ' ',  alumno_apellidomaterno) Apellidos, 
									alumno_fechanacimiento, horario_nombre, alumno_carnet, alumno_imagen, alumno_sedeid
								FROM sujeto_alumno
								INNER JOIN asistencia_asignahorario on asignahorario_alumnoid = alumno_id
								INNER JOIN asistencia_horario on asignahorario_horarioid = horario_id
								WHERE alumno_id = $alumnoid";
            $datos = $this->ejecutarConsulta($consulta_datos);
            return $datos;
        }

        public function EstadoAlumno($alumnoid){		
			$consulta_datos="SELECT max(FechaPension) FechaUltPension, max(pago_estado)Estado,
								CASE WHEN FechaPension >= CURDATE() THEN 'Al día' ELSE 'Pendiente' END Condicion
								from(
									SELECT max(pago_fecha) FechaPension, pago_estado
									from alumno_pago 
									where pago_alumnoid = ".$alumnoid."
										and pago_estado <> 'E'
									group by  pago_estado) as subquery;";	
			$datos = $this->ejecutarConsulta($consulta_datos);
			return $datos;
		}

		/**
		 * Listar colores del CATÁLOGO disponibles para asignar a un mes
		 * @param int $color_id_actual Color actualmente asignado al mes
		 * @param int $mes_actual Mes que se está configurando (para excluirlo de validación)
		 * @return string HTML con opciones del select
		 */
		/**
		 * Listar colores del catálogo disponibles
		 * ✅ CORREGIDO: Usa mcolor_catcolorid en lugar de mcolor_id
		 */
		public function listarOptionColor($color_id_actual = 0, $mes_actual = 0) {
			$option = "";
			
			$consulta = "SELECT 
							cc.catcolor_id, 
							cc.catcolor_nombre, 
							cc.catcolor_hex,
							(SELECT COUNT(*) 
							FROM carnet_mes_color cmc 
							WHERE cmc.mcolor_catcolorid = cc.catcolor_id 
							AND cmc.mcolor_activo = 1
							AND cmc.mcolor_mes != :mes_actual
							) as veces_asignado
						FROM carnet_catcolor cc
						WHERE cc.catcolor_activo = 1
						ORDER BY cc.catcolor_nombre ASC";
			
			$parametros = [':mes_actual' => $mes_actual];
			$datos = $this->ejecutarConsulta($consulta, $parametros);
			$datos = $datos->fetchAll();
			
			$option = '<option value="0">-- Seleccione un color --</option>';
			
			foreach($datos as $row) {
				// Disponible si no está asignado O es el actual
				$esta_disponible = ($row['veces_asignado'] == 0 || $color_id_actual == $row['catcolor_id']);
				
				$selected = ($color_id_actual == $row['catcolor_id']) ? 'selected="selected"' : '';
				$disabled = (!$esta_disponible) ? 'disabled' : '';
				$texto_ocupado = (!$esta_disponible) ? ' (Ya asignado)' : '';
				
				// ✅ CORREGIDO: Usar catcolor_id, catcolor_nombre, catcolor_hex
				$option .= '<option value="' . $row['catcolor_id'] . '" 
									data-color="' . $row['catcolor_hex'] . '" 
									' . $selected . ' 
									' . $disabled . '>
								' . $row['catcolor_nombre'] . $texto_ocupado . '
							</option>';
			}			
			return $option;
		}


		/**
		 * Buscar color asignado a un mes específico
		 * @param int $mes Número del mes (1-12)
		 * @return object PDOStatement
		 */
		public function BuscarColorPorMes($mes) {
			$consulta = "SELECT 
							cmc.mcolor_id,
							cmc.mcolor_mes,
							cmc.mcolor_catcolorid as color_id,
							cmc.mcolor_bloqueado as color_bloqueado,
							cc.catcolor_nombre as color_nombre,
							cc.catcolor_hex as color_hex,
							(SELECT COUNT(*) 
							FROM alumno_carnet ac 
							WHERE ac.carnet_mes = cmc.mcolor_mes) as total_carnets
						FROM carnet_mes_color cmc
						INNER JOIN carnet_catcolor cc ON cmc.mcolor_catcolorid = cc.catcolor_id
						WHERE cmc.mcolor_mes = :mes 
						AND cmc.mcolor_activo = 1";
			
			$parametros = [':mes' => $mes];
			$datos = $this->ejecutarConsulta($consulta, $parametros);
			
			return $datos;
		}
		/**
		 * Obtener código hexadecimal de un color del catálogo
		 * @param int $color_id ID del color en catalogo_colores
		 * @return string Código hexadecimal del color
		 */
		public function obtenerColorHex($color_id) {
			if($color_id == 0 || empty($color_id)) {
				return '#FFFFFF';
			}
			
			$sql = "SELECT catcolor_hex 
					FROM carnet_catcolor 
					WHERE catcolor_id = :id 
					AND catcolor_activo = 1";
			
			$parametros = [':id' => $color_id];
			$datos = $this->ejecutarConsulta($sql, $parametros);
			
			if($datos && $datos->rowCount() == 1) {
				$resultado = $datos->fetch();
				return $resultado['catcolor_hex'];
			}
			
			return '#CCCCCC';
		}

		
		/**
		 * Verificar si un mes tiene carnets emitidos (está bloqueado)
		 * @param int $mes Número del mes
		 * @return bool True si está bloqueado
		 */
		public function mesBloqueado($mes) {
			$sql = "SELECT 
						cmc.mcolor_bloqueado,
						(SELECT COUNT(*) 
						FROM alumno_carnet ac 
						WHERE ac.carnet_mes = :mes) as total_carnets
					FROM carnet_mes_color cmc
					WHERE cmc.mcolor_mes = :mes 
					AND cmc.mcolor_activo = 1";
			
			$parametros = [':mes' => $mes];
			$datos = $this->ejecutarConsulta($sql, $parametros);
			
			if($datos && $datos->rowCount() == 1) {
				$resultado = $datos->fetch();
				return ($resultado['mcolor_bloqueado'] == 1 || $resultado['total_carnets'] > 0);
			}
			
			return false;
		}

		/**
		 * Verificar si un color ya está asignado a otro mes
		 * @param int $color_id ID del color
		 * @param int $mes_excluir Mes a excluir de la validación
		 * @return bool True si ya está asignado
		 */
		public function colorYaAsignado($color_id, $mes_excluir = 0) {
			$sql = "SELECT COUNT(*) as total
					FROM carnet_mes_color 
					WHERE mcolor_catcolorid = :color_id 
					AND mcolor_mes != :mes_excluir
					AND mcolor_activo = 1";
			
			$parametros = [
				':color_id' => $color_id,
				':mes_excluir' => $mes_excluir
			];
			$datos = $this->ejecutarConsulta($sql, $parametros);
			
			if($datos && $datos->rowCount() == 1) {
				$resultado = $datos->fetch();
				return ($resultado['total'] > 0);
			}
			
			return false;
		}

		
		/**
		 * Actualizar asignación de colores por mes
		 * CON VALIDACIONES de bloqueo y duplicados
		 * @return string JSON con resultado
		 */
		public function actualizarColoresMeses() {
			if(!isset($_POST['color_mes']) || !is_array($_POST['color_mes'])) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Error",
					"texto" => "No se recibieron datos de colores",
					"icono" => "error"
				]);
			}
			
			$colores_mes = $_POST['color_mes'];
			$errores = [];
			$bloqueados = [];
			$actualizados = 0;
			
			// Validar duplicados
			$colores_seleccionados = array_filter($colores_mes, function($v) { return $v > 0; });
			$colores_unicos = array_unique($colores_seleccionados);
			
			if(count($colores_seleccionados) != count($colores_unicos)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Error: Colores duplicados",
					"texto" => "No puede asignar el mismo color a diferentes meses",
					"icono" => "error"
				]);
			}
			
			// Procesar cada mes
			foreach($colores_mes as $mes => $color_id_nuevo) {
				// Validar bloqueo
				if($this->mesBloqueado($mes)) {
					$bloqueados[] = $this->nombreMes($mes);
					continue;
				}
				
				// Validar asignación duplicada
				if($this->colorYaAsignado($color_id_nuevo, $mes)) {
					$errores[] = "El color para " . $this->nombreMes($mes) . " ya está asignado";
					continue;
				}
				
				// ✅ CORREGIDO: Actualizar mcolor_catcolorid
				$sql = "UPDATE carnet_mes_color 
						SET mcolor_catcolorid = :color_id
						WHERE mcolor_mes = :mes 
						AND mcolor_activo = 1";
				
				$parametros = [
					':color_id' => $color_id_nuevo,
					':mes' => $mes
				];
				
				try {
					$result = $this->ejecutarConsulta($sql, $parametros);
					if($result) {
						$actualizados++;
					}
				} catch (Exception $e) {
					$errores[] = "Error en " . $this->nombreMes($mes) . ": " . $e->getMessage();
				}
			}
			
			// Construir respuesta
			if(count($bloqueados) > 0 || count($errores) > 0) {
				$mensaje = "";
				
				if($actualizados > 0) {
					$mensaje .= "✅ Actualizados: $actualizados meses. ";
				}
				
				if(count($bloqueados) > 0) {
					$mensaje .= "🔒 Bloqueados: " . implode(", ", $bloqueados) . ". ";
				}
				
				if(count($errores) > 0) {
					$mensaje .= "❌ " . implode(", ", $errores);
				}
				
				return json_encode([
					"tipo" => ($actualizados > 0 ? "recargar" : "simple"),
					"titulo" => "Actualización parcial",
					"texto" => $mensaje,
					"icono" => "warning"
				]);
			}
			
			if($actualizados > 0) {
				return json_encode([
					"tipo" => "recargar",
					"titulo" => "¡Configuración actualizada!",
					"texto" => "Los colores se asignaron correctamente",
					"icono" => "success"
				]);
			}
			
			return json_encode([
				"tipo" => "simple",
				"titulo" => "Sin cambios",
				"texto" => "No se realizaron modificaciones",
				"icono" => "info"
			]);
		}

		private function nombreMes($mes) {
			$meses = [
				1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
				5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
				9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
			];
			return $meses[$mes] ?? 'Mes desconocido';
		}	
    }