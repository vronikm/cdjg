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

		/**
		 * Listar alumnos con pagos de pensión del mes actual
		 * @return string HTML de filas de tabla
		 */
		public function listarAlumnos() {
			$tabla = "";

			$alumnos_sin_horario = $this->verificarAlumnosSinHorarioAsignado();
			if(!empty($alumnos_sin_horario)) {
				$detalle_alumnos = [];
				foreach($alumnos_sin_horario as $alumno) {
					$detalle_alumnos[] = $alumno['alumno_nombre'] . " (" . $alumno['alumno_identificacion'] . ")";
				}

				$mensaje = htmlspecialchars(implode(", ", $detalle_alumnos), ENT_QUOTES, 'UTF-8');
				return '<tr>
							<td colspan="8" class="text-center">
								<div class="alert alert-danger mb-0">
									<i class="fas fa-exclamation-triangle"></i>
									<strong>Horario pendiente:</strong>
									Los siguientes alumnos no tienen horario de entrenamiento asignado: ' . $mensaje . '.
									Primero debe corregir esta informacion para luego listar los alumnos a los que se generara el carnet.
								</div>
							</td>
						</tr>';
			}

			$consulta_datos = "SELECT alumno_id, 
									alumno_identificacion, 
									CONCAT(alumno_primernombre, ' ', alumno_segundonombre) NOMBRES, 
									CONCAT(alumno_apellidopaterno, ' ', alumno_apellidomaterno) APELLIDOS, 
									alumno_carnet, 
									FechaUltPension, 
									CASE 
										WHEN FechaUltPension >= DATE_FORMAT(CURDATE(), '%Y-%m-01')                               
										THEN 'Al día' 
										ELSE 'Pendiente' 
									END Condicion,
									CASE
										WHEN EXISTS(
											SELECT 1
											FROM alumno_carnet ac
											WHERE ac.carnet_alumnoid = alumno_id
											AND ac.carnet_mes = MONTH(CURDATE())
											AND ac.carnet_anio = YEAR(CURDATE())
											AND ac.carnet_fecha_impresion IS NOT NULL
										) THEN 1
										ELSE 0
									END AS carnet_impreso,
									(
										SELECT MAX(ac2.carnet_fecha_impresion)
										FROM alumno_carnet ac2
										WHERE ac2.carnet_alumnoid = alumno_id
										AND ac2.carnet_mes = MONTH(CURDATE())
										AND ac2.carnet_anio = YEAR(CURDATE())
									) AS fecha_impresion
								FROM sujeto_alumno
								INNER JOIN (    
								(    
									SELECT pago_alumnoid, MAX(FechaPension) FechaUltPension, MAX(pago_estado) Estado
									FROM (
										SELECT pago_fecha as FechaPension, pago_estado, pago_alumnoid                               
											FROM alumno_pago 
											WHERE pago_fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
												and pago_estado NOT IN ('E', 'J')
												and pago_rubroid = 'RPE'
									) AS Pagos
									GROUP BY pago_alumnoid
								)
								UNION                
								SELECT descuento_alumnoid, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPago, 'Al dìa' as Estado
												from alumno_pago_descuento
												where descuento_rubroid = 'DBC'
																and descuento_valor = 0
																and descuento_estado = 'S'     
												) EstadoPagos ON pago_alumnoid = alumno_id
												WHERE alumno_estado = 'A'
												ORDER BY carnet_impreso ASC, alumno_apellidopaterno, alumno_apellidomaterno";
			
			$datos = $this->ejecutarConsulta($consulta_datos);

			if($datos->rowCount() > 0) {
				$datos = $datos->fetchAll();
				
				foreach($datos as $rows) {
					if((int)$rows['carnet_impreso'] === 1) {
						$estadoImpresion = '<span class="badge badge-success"><i class="fas fa-check"></i> Impreso</span>';
						if(!empty($rows['fecha_impresion'])) {
							$estadoImpresion .= '<br><small class="text-muted">' . $rows['fecha_impresion'] . '</small>';
						}
					} else {
						$estadoImpresion = '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>';
					}
	
					$reimpresionDisabled = ((int)$rows['carnet_impreso'] === 1)
						? ''
						: ' disabled title="Use Imprimir Todos para emitir el carnet pendiente"';

					$tabla .= '				
						<tr>
							<td>' . $rows['alumno_identificacion'] . '</td>
							<td>' . $rows['NOMBRES'] . '</td>
							<td>' . $rows['APELLIDOS'] . '</td>
							<td>' . $rows['alumno_carnet'] . '</td>
							<td>' . $rows['FechaUltPension'] . '</td>
							<td data-order="' . (int)$rows['carnet_impreso'] . '">' . $estadoImpresion . '</td>
							<td>							
								<a href="' . APP_URL . 'carnetFotoPDF/' . $rows['alumno_id'] . '/" 
								class="btn float-right btn-success btn-xs" 
								style="margin-right: 5px;">
								Ver carnet
								</a>	
							</td>
							<td style="text-align: center;">
								<div class="custom-control custom-checkbox">
									<input class="custom-control-input chk-reimpresion" 
										type="checkbox" 
										id="alumno_' . $rows['alumno_id'] . '" 
										name="pagos_seleccionados[]" 
										value="' . $rows['alumno_id'] . '"' . $reimpresionDisabled . '>								
									<label for="alumno_' . $rows['alumno_id'] . '" 
										class="custom-control-label"></label>
								</div>
							</td>						
						</tr>';	
				}
			} else {
				$tabla = '<tr>
							<td colspan="8" class="text-center">
								<div class="alert alert-info mb-0">
									<i class="fas fa-info-circle"></i> 
									No hay alumnos con pagos de pensión este mes
								</div>
							</td>
						</tr>';
			}
			
			return $tabla;			
		}

		public function verificarAlumnosSinHorarioAsignado() {
			$consulta = "SELECT
						a.alumno_id,
						a.alumno_identificacion,
						CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ',
							a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) AS alumno_nombre
						FROM sujeto_alumno a
						INNER JOIN (
							SELECT pago_alumnoid
							FROM alumno_pago
							WHERE pago_fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
								AND pago_estado NOT IN ('E', 'J')
								AND pago_rubroid = 'RPE'

							UNION

							SELECT descuento_alumnoid
							FROM alumno_pago_descuento
							WHERE descuento_rubroid = 'DBC'
								AND descuento_valor = 0
								AND descuento_estado = 'S'
						) candidatos ON candidatos.pago_alumnoid = a.alumno_id
						WHERE a.alumno_estado = 'A'
							AND NOT EXISTS (
								SELECT 1
								FROM asistencia_asignahorario ah
								WHERE ah.asignahorario_alumnoid = a.alumno_id
							)
						ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta);
			return $datos->fetchAll();
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
            if($datos && $datos->rowCount() <=0) {
				$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Error",
							"texto"=>"Alumno no tiene un horario asignado, asigne un horario para generar el carnet.",
							"icono"=>"error"
				];
				return json_encode($alerta);				
			}else{
				return $datos;
			}
        }

        public function EstadoAlumno($alumnoid){		
			$consulta_datos="SELECT FechaUltPension, Estado, 
								CASE 
										WHEN FechaUltPension >= DATE_FORMAT(CURDATE(), '%Y-%m-01')                               
										THEN 'Al dia' 
										ELSE 'Pendiente' 
										END Condicion
								FROM(SELECT max(pago_fecha) FechaUltPension, max(pago_estado)Estado
										from(
												SELECT pago_fecha, pago_estado
														FROM alumno_pago 
														WHERE pago_alumnoid = $alumnoid
															AND pago_estado NOT IN ('J','E')
														GROUP BY pago_estado, pago_fecha) as subquery) AS Total
							UNION
							SELECT DATE_FORMAT(CURDATE(), '%Y-%m-01') FechaPago, 'C' as Estado, 'Al dìa' as Condicion
                                from alumno_pago_descuento
                                where descuento_rubroid = 'DBC'
                                                and descuento_valor = 0
                                                and descuento_estado = 'S'
												and descuento_alumnoid = $alumnoid";	
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

		private function asegurarTablaConfiguracionCarnet() {
			$sql = "CREATE TABLE IF NOT EXISTS carnet_configuracion (
				config_id INT AUTO_INCREMENT PRIMARY KEY,
				config_clave VARCHAR(80) NOT NULL UNIQUE,
				config_valor VARCHAR(255) NOT NULL,
				config_descripcion VARCHAR(255) NULL,
				config_fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8";

			return $this->ejecutarConsulta($sql);
		}

		private function obtenerValorConfiguracionCarnet($clave, $valor_defecto = '') {
			$this->asegurarTablaConfiguracionCarnet();

			$sql = "SELECT config_valor FROM carnet_configuracion WHERE config_clave = :clave LIMIT 1";
			$datos = $this->ejecutarConsulta($sql, [':clave' => $clave]);

			if($datos && $datos->rowCount() == 1) {
				$fila = $datos->fetch();
				return $fila['config_valor'];
			}

			$this->guardarValorConfiguracionCarnet($clave, $valor_defecto, 'Configuracion automatica de carnets');
			return $valor_defecto;
		}

		private function guardarValorConfiguracionCarnet($clave, $valor, $descripcion = '') {
			$this->asegurarTablaConfiguracionCarnet();

			$sql = "INSERT INTO carnet_configuracion (config_clave, config_valor, config_descripcion)
					VALUES (:clave, :valor, :descripcion)
					ON DUPLICATE KEY UPDATE
						config_valor = VALUES(config_valor),
						config_descripcion = VALUES(config_descripcion)";

			return $this->ejecutarConsulta($sql, [
				':clave' => $clave,
				':valor' => $valor,
				':descripcion' => $descripcion
			]);
		}

		public function cobrarReimpresionCarnet() {
			return $this->obtenerValorConfiguracionCarnet('cobrar_reimpresion', '1') === '1';
		}

		public function valorReimpresionCarnet() {
			$valor = str_replace(',', '.', $this->obtenerValorConfiguracionCarnet('valor_reimpresion', '3.00'));

			if(!is_numeric($valor) || (float)$valor <= 0) {
				return 3.00;
			}

			return round((float)$valor, 2);
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
			$cobrar_reimpresion = (isset($_POST['cobrar_reimpresion']) && $_POST['cobrar_reimpresion'] === '1') ? '1' : '0';
			$valor_reimpresion = str_replace(',', '.', $_POST['valor_reimpresion'] ?? $this->valorReimpresionCarnet());
			$valor_anterior = number_format($this->valorReimpresionCarnet(), 2, '.', '');
			$cobro_anterior = $this->cobrarReimpresionCarnet() ? '1' : '0';
			$cobro_actualizado = false;
			$valor_actualizado = false;

			if(!is_numeric($valor_reimpresion) || (float)$valor_reimpresion <= 0) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Valor invalido",
					"texto" => "El valor de reimpresion debe ser mayor a 0",
					"icono" => "warning"
				]);
			}

			$valor_reimpresion = number_format((float)$valor_reimpresion, 2, '.', '');
			
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

			try {
				if($cobro_anterior !== $cobrar_reimpresion) {
					$this->guardarValorConfiguracionCarnet(
						'cobrar_reimpresion',
						$cobrar_reimpresion,
						'Define si la reimpresion de carnets genera cargo ROT'
					);
					$cobro_actualizado = true;
				}

				if($valor_anterior !== $valor_reimpresion) {
					$this->guardarValorConfiguracionCarnet(
						'valor_reimpresion',
						$valor_reimpresion,
						'Valor del rubro ROT para reimpresion de carnets'
					);
					$valor_actualizado = true;
				}
			} catch (Exception $e) {
				$errores[] = "Error guardando configuracion de cobro por reimpresion: " . $e->getMessage();
			}
			
			// Construir respuesta
			if(count($bloqueados) > 0 || count($errores) > 0) {
				$mensaje = "";
				
				if($actualizados > 0) {
					$mensaje .= "✅ Actualizados: $actualizados meses. ";
				}

				if($cobro_actualizado) {
					$mensaje .= "Politica de cobro por reimpresion actualizada. ";
				}

				if($valor_actualizado) {
					$mensaje .= "Valor de reimpresion actualizado. ";
				}
				
				if(count($bloqueados) > 0) {
					$mensaje .= "🔒 Bloqueados: " . implode(", ", $bloqueados) . ". ";
				}
				
				if(count($errores) > 0) {
					$mensaje .= "❌ " . implode(", ", $errores);
				}
				
				return json_encode([
					"tipo" => (($actualizados > 0 || $cobro_actualizado || $valor_actualizado) ? "recargar" : "simple"),
					"titulo" => "Actualización parcial",
					"texto" => $mensaje,
					"icono" => "warning"
				]);
			}
			
			if($actualizados > 0 || $cobro_actualizado || $valor_actualizado) {
				return json_encode([
					"tipo" => "recargar",
					"titulo" => "Configuracion actualizada",
					"texto" => "La configuracion de carnets se guardo correctamente",
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
		
				
		/**
		 * ============================================================
		 * MÉTODOS PARA GENERACIÓN E IMPRESIÓN DE CARNETS
		 * ============================================================
		 */

		/**
		 * Obtener carnets del mes actual listos para imprimir
		 * Incluye todos los alumnos con pagos de pensión (RPE) del mes
		 * @return array Array con datos de carnets
		 */
		public function obtenerCarnetsMesActual() {
			$fecha_actual = date('Y-m-d');
			$mes_actual = date('n'); // Mes actual (1-12)
			$anio_actual = date('Y');
			
			// Obtener color asignado al mes
			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();
			
			$consulta = "SELECT 
							a.alumno_id, alumno_carnet,
							a.alumno_identificacion,
							CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
								a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
							a.alumno_imagen,
							a.alumno_sedeid,
							h.horario_nombre,
							ac.carnet_id,
							ac.carnet_alumnoid,
							ac.carnet_numero,
							:mes as carnet_mes,
							:anio as carnet_anio,
							:fecha_actual as carnet_fecha_emision,
							:fecha_actual as carnet_fecha_impresion,
							0 as es_reimpresion,
							:color_hex as color_hex,
							:mes_nombre as mes_nombre
							FROM sujeto_alumno a
							INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
							INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
							INNER JOIN alumno_pago ap ON ap.pago_alumnoid = a.alumno_id
							LEFT JOIN alumno_carnet ac ON ac.carnet_alumnoid = a.alumno_id 
													AND ac.carnet_mes = :mes 
													AND ac.carnet_anio = :anio
							WHERE a.alumno_estado = 'A'
								AND ap.pago_estado NOT IN ('E', 'J')
								AND MONTH(ap.pago_fecha) = :mes
								AND YEAR(ap.pago_fecha) = :anio
								AND ap.pago_rubroid = 'RPE'
								AND ac.carnet_alumnoid IS NULL
						UNION ALL
						
						SELECT 
							a.alumno_id, 
							a.alumno_identificacion,
							CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
								a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
							a.alumno_imagen,
							a.alumno_sedeid,
							h.horario_nombre,
							ac.carnet_id,
							ac.carnet_alumnoid,
							:mes as carnet_mes,
							:anio as carnet_anio,
							:fecha_actual as carnet_fecha_emision,
							:fecha_actual as carnet_fecha_impresion,
							0 as es_reimpresion,
							:color_hex as color_hex,
							:mes_nombre as mes_nombre
							FROM sujeto_alumno a
							INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
							INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
							INNER JOIN alumno_pago_descuento apd ON apd.descuento_alumnoid = a.alumno_id
							LEFT JOIN alumno_carnet ac ON ac.carnet_alumnoid = a.alumno_id 
													AND ac.carnet_mes = :mes 
													AND ac.carnet_anio = :anio
							WHERE a.alumno_estado = 'A'
								AND apd.descuento_rubroid = 'DBC'
								AND apd.descuento_valor = 0
								AND apd.descuento_estado = 'S'
								AND ac.carnet_alumnoid IS NULL";
			
			$parametros = [
				':fecha_actual' => $fecha_actual,
				':mes' => $mes_actual,
				':anio' => $anio_actual,
				':color_hex' => $colorData['color_hex'] ?? '#CCCCCC',
				':mes_nombre' => $this->nombreMes($mes_actual)
			];
			
			$datos = $this->ejecutarConsulta($consulta, $parametros);
			$carnets = $datos->fetchAll();
			
			// Generar carnets si no existen
			$carnetsFinales = [];
			foreach($carnets as $carnet) {
				if(empty($carnet['carnet_id'])) {
					// Crear nuevo carnet
					$nuevoCarnet = $this->crearCarnet(
						$carnet['alumno_id'], 
						$carnet['alumno_carnet'], 
						$mes_actual, 
						$anio_actual
					);
					$carnet['carnet_id'] = $nuevoCarnet['carnet_id'];
					$carnet['carnet_numero'] = $nuevoCarnet['carnet_numero'];
					$carnet['carnet_fecha_emision'] = $nuevoCarnet['carnet_fecha_emision'];
				}
				$carnetsFinales[] = $carnet;
			}
			
			return $carnetsFinales;
		}

		/**
		 * Crear un nuevo carnet para un alumno
		 * @param int $alumno_id ID del alumno
		 * @param int $alumno_carnet Código de carnet del alumno
		 * @param int $mes Mes de vigencia
		 * @param int $anio Año de vigencia
		 * @return array Datos del carnet creado
		 */
		private function crearCarnet($alumno_id, $alumno_carnet, $mes, $anio) {
			// El carnet se marca como impreso solo cuando el PDF se genera correctamente.
			$conexion = $this->conectar();
			$sql = $conexion->prepare(
				"INSERT INTO alumno_carnet
					(carnet_numero, carnet_mes, carnet_anio, carnet_alumnoid, carnet_fecha_emision, carnet_fecha_impresion)
				 VALUES
					(:numero, :mes, :anio, :alumno_id, CURDATE(), NULL)"
			);
			$sql->execute([
				':numero' => $alumno_carnet,
				':mes' => $mes,
				':anio' => $anio,
				':alumno_id' => $alumno_id
			]);

			return [
				'carnet_id' => $conexion->lastInsertId(),
				'carnet_numero' => $alumno_carnet,
				'carnet_fecha_emision' => date('Y-m-d')
			];
		}

		/**
		 * Obtener el último ID insertado
		 * @return int ID insertado
		 */
		private function obtenerUltimoId() {
			$sql = "SELECT LAST_INSERT_ID() as ultimo_id";
			$datos = $this->ejecutarConsulta($sql);
			$resultado = $datos->fetch();
			return $resultado['ultimo_id'];
		}

		/**
		 * Registrar impresión de carnets
		 * @param array $carnet_ids IDs de carnets impresos
		 * @return bool
		 */
		public function registrarImpresion($carnet_ids) {
			if(empty($carnet_ids)) {
				return false;
			}
			
			$ids_string = implode(',', array_map('intval', $carnet_ids));
			
			$sql = "UPDATE alumno_carnet 
					SET carnet_fecha_impresion = NOW() 
					WHERE carnet_id IN ($ids_string)
						AND carnet_mes = MONTH(CURDATE())
						AND carnet_anio = YEAR(CURDATE())
						AND carnet_fecha_impresion IS NULL";
			
			return $this->ejecutarConsulta($sql);
		}

		public function procesarReimpresion() {
			// Limpiar y validar datos
			$alumno_ids = $_POST['pagos_seleccionados'] ?? [];
			
			if(empty($alumno_ids)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Sin selección",
					"texto" => "Debe seleccionar al menos un alumno para reimprimir",
					"icono" => "warning"
				]);
			}
			
			// Limpiar IDs
			$alumno_ids = array_map([$this, 'limpiarCadena'], $alumno_ids);
			$alumno_ids = array_filter($alumno_ids, 'is_numeric');
			
			if(empty($alumno_ids)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Error",
					"texto" => "IDs de alumnos inválidos",
					"icono" => "error"
				]);
			}
			
			$mes_actual = date('n');
			$name_mesactual = $this->nombreMes($mes_actual);
			$anio_actual = date('Y');
			$fecha_actual = date('Y-m-d');
			$cobrar_reimpresion = $this->cobrarReimpresionCarnet();
			$valor_reimpresion_pago = $this->valorReimpresionCarnet();
			
			$procesados = 0;
			$pagos_generados = 0;
			$reimpresiones_sin_cobro = 0;
			$errores = [];
			
			foreach($alumno_ids as $alumno_id) {
				try {
					// Verificar si ya tiene carnet del mes
					$sqlVerificar = "SELECT carnet_id, carnet_fecha_impresion
								FROM alumno_carnet 
								WHERE carnet_alumnoid = :alumno_id
								AND carnet_mes = :mes
								AND carnet_anio = :anio
								ORDER BY carnet_id DESC
								LIMIT 1";
					
					$datos = $this->ejecutarConsulta($sqlVerificar, [
						':alumno_id' => $alumno_id,
						':mes' => $mes_actual,
						':anio' => $anio_actual
					]);
					
					if($datos->rowCount() == 0) {
						$errores[] = "Alumno ID $alumno_id no tiene carnet original del mes";
						continue;
					}

					$carnetOriginal = $datos->fetch();
					if(empty($carnetOriginal['carnet_fecha_impresion'])) {
						$errores[] = "Alumno ID $alumno_id tiene carnet pendiente de impresion. Use Imprimir Todos antes de reimprimir";
						continue;
					}
					
					$sqlBeca100 = "SELECT 1
									FROM alumno_pago_descuento
									WHERE descuento_alumnoid = :alumno_id
										AND descuento_rubroid = 'DBC'
										AND descuento_valor = 0
										AND descuento_estado = 'S'
									LIMIT 1";

					$datosBeca100 = $this->ejecutarConsulta($sqlBeca100, [
						':alumno_id' => $alumno_id
					]);
					$tiene_beca_100 = ($datosBeca100->rowCount() > 0);

					if($cobrar_reimpresion && !$tiene_beca_100) {
						$recibo = $this->generarNumeroRecibo('ROT');

						$sqlPago = "INSERT INTO alumno_pago
								(pago_rubroid, pago_formapagoid, pago_alumnoid, pago_valor,
									pago_saldo, pago_concepto, pago_fecha, pago_fecharegistro,
									pago_periodo, pago_recibo, pago_estado)
								VALUES
								('ROT', 'FEF', :alumno_id, :valor_reimpresion, 0.00,
									'Por reimpresion de carnet extraviado',
									:fecha, :fecha, :periodo, :recibo, 'C')";

						$this->ejecutarConsulta($sqlPago, [
							':alumno_id' => $alumno_id,
							':valor_reimpresion' => $valor_reimpresion_pago,
							':fecha' => $fecha_actual,
							':periodo' => $name_mesactual . '/' . $anio_actual,
							':recibo' => $recibo
						]);

						$pagos_generados++;
					} else {
						$reimpresiones_sin_cobro++;
					}
					
					$procesados++;
					
				} catch (Exception $e) {
					$errores[] = "Error en alumno ID $alumno_id: " . $e->getMessage();
				}
			}
			
			// Construir respuesta
			if($procesados > 0 && empty($errores)) {
				// ✅ GUARDAR IDS EN SESIÓN en lugar de URL
				$alumno_ids_reimpresion = implode(',', $alumno_ids);
				unset($_SESSION['carnet_impresion_mensual_ids']);
				$_SESSION['carnet_reimpresion_ids'] = $alumno_ids_reimpresion;
				$token_reimpresion = rtrim(strtr(base64_encode($alumno_ids_reimpresion), '+/', '-_'), '=');
				$firma_reimpresion = hash_hmac('sha256', $alumno_ids_reimpresion, session_id());
				session_write_close();
				$texto = "Se generaron $pagos_generados pagos por reimpresion. Redirigiendo a impresion...";
				if($reimpresiones_sin_cobro > 0) {
					$motivo_sin_cobro = $cobrar_reimpresion ? "por beca 100%" : "por configuracion sin cobro";
					$texto = "Se generaron $pagos_generados pagos por reimpresion y $reimpresiones_sin_cobro reimpresiones sin cobro $motivo_sin_cobro. Redirigiendo a impresion...";
				}
				
				return json_encode([
					"tipo" => "redireccionar",
					"titulo" => "Reimpresion procesada",
					"texto" => $texto,
					"icono" => "success",
					"url" => APP_URL . "carnetPDF/?modo=reimpresion&reimpresion=" . rawurlencode($token_reimpresion) . "&firma=" . $firma_reimpresion
				]);
			}
			
			if($procesados > 0 && !empty($errores)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Procesamiento parcial",
					"texto" => "Procesados: $procesados (pagos generados: $pagos_generados, sin cobro: $reimpresiones_sin_cobro). Errores: " . implode(", ", $errores),
					"icono" => "warning"
				]);
			}
			
			return json_encode([
				"tipo" => "simple",
				"titulo" => "Error en procesamiento",
				"texto" => implode(", ", $errores),
				"icono" => "error"
			]);
		}

		/**
		 * Generar número de recibo único
		 * @param string $tipo Tipo de pago (ROT para reimpresión)
		 * @return string Número de recibo
		 */
		private function generarNumeroRecibo($tipo) {
			$sql = "SELECT MAX(CAST(SUBSTRING(pago_recibo, 4) AS UNSIGNED)) as ultimo
					FROM alumno_pago 
					WHERE pago_rubroid = :tipo
					AND YEAR(pago_fecha) = YEAR(CURDATE())";
			
			$datos = $this->ejecutarConsulta($sql, [':tipo' => $tipo]);
			$resultado = $datos->fetch();
			
			$siguiente = ($resultado['ultimo'] ?? 0) + 1;
			
			return $tipo . str_pad($siguiente, 6, '0', STR_PAD_LEFT);
		}

		/**
		 * Obtener carnets para reimpresión
		 * @param string $alumno_ids_string IDs separados por coma
		 * @return array Carnets con marca de reimpresión
		 */
		public function obtenerCarnetsReimpresion($alumno_ids_string) {
			$alumno_ids = explode(',', $alumno_ids_string);
			$alumno_ids = array_map('intval', $alumno_ids);
			$alumno_ids = array_filter($alumno_ids);
			
			if(empty($alumno_ids)) {
				return [];
			}
			
			$mes_actual = date('n');
			$anio_actual = date('Y');
			
			$ids_string = implode(',', $alumno_ids);
			
			// Obtener color del mes
			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();
			
			$consulta = "SELECT 
							a.alumno_id, alumno_carnet,
							a.alumno_identificacion,
							CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
								a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
							a.alumno_imagen,
							h.horario_nombre,
							ac.carnet_id,
							ac.carnet_numero,
							ac.carnet_mes,
							ac.carnet_anio,
							ac.carnet_fecha_emision,
							1 as es_reimpresion,
							:color_hex as color_hex,
							:mes_nombre as mes_nombre
						FROM sujeto_alumno a
						INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
						INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
						INNER JOIN alumno_carnet ac ON ac.carnet_id = (
							SELECT ac2.carnet_id
							FROM alumno_carnet ac2
							WHERE ac2.carnet_alumnoid = a.alumno_id
								AND ac2.carnet_mes = :mes
								AND ac2.carnet_anio = :anio
							ORDER BY ac2.carnet_id DESC
							LIMIT 1
						)
						WHERE a.alumno_id IN ($ids_string)
						AND a.alumno_estado = 'A'
						ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";
			
			$parametros = [
				':mes' => $mes_actual,
				':anio' => $anio_actual,
				':color_hex' => $colorData['color_hex'] ?? '#CCCCCC',
				':mes_nombre' => $this->nombreMes($mes_actual)
			];
			
			$datos = $this->ejecutarConsulta($consulta, $parametros);
			return $datos->fetchAll();
		}

		public function carnetPendientesImpresion() {
			$mes_actual = date('n');
			$anio_actual = date('Y');

			$consulta = "SELECT COUNT(*) AS total
						FROM (
							SELECT a.alumno_id
							FROM sujeto_alumno a
							INNER JOIN alumno_pago ap ON ap.pago_alumnoid = a.alumno_id
							WHERE a.alumno_estado = 'A'
								AND ap.pago_estado NOT IN ('E', 'J')
								AND MONTH(ap.pago_fecha) = :mes
								AND YEAR(ap.pago_fecha) = :anio
								AND ap.pago_rubroid = 'RPE'
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
								AND NOT EXISTS (
									SELECT 1 FROM alumno_carnet ac
									WHERE ac.carnet_alumnoid = a.alumno_id
										AND ac.carnet_mes = :mes
										AND ac.carnet_anio = :anio
										AND ac.carnet_fecha_impresion IS NOT NULL
								)

							UNION

							SELECT a.alumno_id
							FROM sujeto_alumno a
							INNER JOIN alumno_pago_descuento apd ON apd.descuento_alumnoid = a.alumno_id
							WHERE a.alumno_estado = 'A'
								AND apd.descuento_rubroid = 'DBC'
								AND apd.descuento_valor = 0
								AND apd.descuento_estado = 'S'
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
								AND NOT EXISTS (
									SELECT 1 FROM alumno_carnet ac
									WHERE ac.carnet_alumnoid = a.alumno_id
										AND ac.carnet_mes = :mes
										AND ac.carnet_anio = :anio
										AND ac.carnet_fecha_impresion IS NOT NULL
								)
						) AS subconsulta";

			$datos = $this->ejecutarConsulta($consulta, [
				':mes' => $mes_actual,
				':anio' => $anio_actual
			]);
			return $datos->fetchAll();
		}

		public function obtenerCarnetsPendientesMesActual() {
			$mes_actual = date('n');
			$anio_actual = date('Y');
			$fecha_actual = date('Y-m-d');

			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();
			$color_hex = $colorData['color_hex'] ?? '#CCCCCC';
			$mes_nombre = $this->nombreMes($mes_actual);

			$consulta = "SELECT
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ',
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								a.alumno_sedeid,
								(SELECT h.horario_nombre
									FROM asistencia_asignahorario ah
									INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
									WHERE ah.asignahorario_alumnoid = a.alumno_id
									LIMIT 1) as horario_nombre,
								ac.carnet_id,
								ac.carnet_numero,
								:mes as carnet_mes,
								:anio as carnet_anio,
								COALESCE(ac.carnet_fecha_emision, :fecha_actual) as carnet_fecha_emision,
								0 as es_reimpresion,
								:color_hex as color_hex,
								:mes_nombre as mes_nombre
							FROM (
								SELECT DISTINCT a.alumno_id
								FROM sujeto_alumno a
								INNER JOIN alumno_pago ap ON ap.pago_alumnoid = a.alumno_id
								WHERE a.alumno_estado = 'A'
									AND ap.pago_estado NOT IN ('E', 'J')
									AND MONTH(ap.pago_fecha) = :mes
									AND YEAR(ap.pago_fecha) = :anio
									AND ap.pago_rubroid = 'RPE'
									AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
									AND NOT EXISTS (
										SELECT 1 FROM alumno_carnet acp
										WHERE acp.carnet_alumnoid = a.alumno_id
											AND acp.carnet_mes = :mes
											AND acp.carnet_anio = :anio
											AND acp.carnet_fecha_impresion IS NOT NULL
									)

								UNION

								SELECT DISTINCT a.alumno_id
								FROM sujeto_alumno a
								INNER JOIN alumno_pago_descuento apd ON apd.descuento_alumnoid = a.alumno_id
								WHERE a.alumno_estado = 'A'
									AND apd.descuento_rubroid = 'DBC'
									AND apd.descuento_valor = 0
									AND apd.descuento_estado = 'S'
									AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
									AND NOT EXISTS (
										SELECT 1 FROM alumno_carnet acp
										WHERE acp.carnet_alumnoid = a.alumno_id
											AND acp.carnet_mes = :mes
											AND acp.carnet_anio = :anio
											AND acp.carnet_fecha_impresion IS NOT NULL
									)
							) elegibles
							INNER JOIN sujeto_alumno a ON a.alumno_id = elegibles.alumno_id
							LEFT JOIN alumno_carnet ac ON ac.carnet_id = (
								SELECT ac2.carnet_id
								FROM alumno_carnet ac2
								WHERE ac2.carnet_alumnoid = a.alumno_id
									AND ac2.carnet_mes = :mes
									AND ac2.carnet_anio = :anio
								ORDER BY ac2.carnet_id DESC
								LIMIT 1
							)
							ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta, [
				':fecha_actual' => $fecha_actual,
				':mes' => $mes_actual,
				':anio' => $anio_actual,
				':color_hex' => $color_hex,
				':mes_nombre' => $mes_nombre
			]);

			$carnets = $datos->fetchAll();
			$carnetsFinales = [];

			foreach($carnets as $carnet) {
				if(empty($carnet['carnet_id'])) {
					$nuevoCarnet = $this->crearCarnet(
						$carnet['alumno_id'],
						$carnet['alumno_carnet'],
						$mes_actual,
						$anio_actual
					);
					$carnet['carnet_id'] = $nuevoCarnet['carnet_id'];
					$carnet['carnet_numero'] = $nuevoCarnet['carnet_numero'];
					$carnet['carnet_fecha_emision'] = $nuevoCarnet['carnet_fecha_emision'];
				}

				$carnetsFinales[] = $carnet;
			}

			if(empty($carnetsFinales)) {
				$carnetsFinales = $this->obtenerCarnetsNoImpresosMesActual();
			}

			return $carnetsFinales;
		}

		public function obtenerCarnetsNoImpresosMesActual() {
			$mes_actual = date('n');
			$anio_actual = date('Y');

			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();

			$consulta = "SELECT
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ',
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								a.alumno_sedeid,
								(SELECT h.horario_nombre
									FROM asistencia_asignahorario ah
									INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
									WHERE ah.asignahorario_alumnoid = a.alumno_id
									LIMIT 1) as horario_nombre,
								ac.carnet_id,
								ac.carnet_numero,
								ac.carnet_mes,
								ac.carnet_anio,
								ac.carnet_fecha_emision,
								0 as es_reimpresion,
								:color_hex as color_hex,
								:mes_nombre as mes_nombre
							FROM alumno_carnet ac
							INNER JOIN sujeto_alumno a ON a.alumno_id = ac.carnet_alumnoid
							WHERE ac.carnet_mes = :mes
								AND ac.carnet_anio = :anio
								AND ac.carnet_fecha_impresion IS NULL
								AND a.alumno_estado = 'A'
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
							ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta, [
				':mes' => $mes_actual,
				':anio' => $anio_actual,
				':color_hex' => $colorData['color_hex'] ?? '#CCCCCC',
				':mes_nombre' => $this->nombreMes($mes_actual)
			]);

			return $datos->fetchAll();
		}

		public function obtenerCarnetsMensualesPorIds($carnet_ids_string) {
			$carnet_ids = explode(',', $carnet_ids_string);
			$carnet_ids = array_map('intval', $carnet_ids);
			$carnet_ids = array_filter($carnet_ids);

			if(empty($carnet_ids)) {
				return [];
			}

			$mes_actual = date('n');
			$anio_actual = date('Y');
			$ids_string = implode(',', $carnet_ids);

			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();

			$consulta = "SELECT
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ',
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								a.alumno_sedeid,
								(SELECT h.horario_nombre
									FROM asistencia_asignahorario ah
									INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
									WHERE ah.asignahorario_alumnoid = a.alumno_id
									LIMIT 1) as horario_nombre,
								ac.carnet_id,
								ac.carnet_numero,
								ac.carnet_mes,
								ac.carnet_anio,
								ac.carnet_fecha_emision,
								0 as es_reimpresion,
								:color_hex as color_hex,
								:mes_nombre as mes_nombre
							FROM alumno_carnet ac
							INNER JOIN sujeto_alumno a ON a.alumno_id = ac.carnet_alumnoid
							WHERE ac.carnet_id IN ($ids_string)
								AND ac.carnet_mes = :mes
								AND ac.carnet_anio = :anio
								AND a.alumno_estado = 'A'
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
							ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta, [
				':mes' => $mes_actual,
				':anio' => $anio_actual,
				':color_hex' => $colorData['color_hex'] ?? '#CCCCCC',
				':mes_nombre' => $this->nombreMes($mes_actual)
			]);

			return $datos->fetchAll();
		}

		public function prepararImpresionMensual() {
			$carnets = $this->obtenerCarnetsPendientesMesActual();

			if(empty($carnets)) {
				$carnets = $this->obtenerCarnetsNoImpresosMesActual();
			}

			if(empty($carnets)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Sin carnets pendientes",
					"texto" => "No hay carnets pendientes de impresion este mes",
					"icono" => "info"
				]);
			}

			$carnet_ids = [];
			foreach($carnets as $carnet) {
				if(!empty($carnet['carnet_id'])) {
					$carnet_ids[] = (int)$carnet['carnet_id'];
				}
			}
			$carnet_ids = array_values(array_unique($carnet_ids));

			if(empty($carnet_ids)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Error",
					"texto" => "No se pudo preparar el lote de impresion",
					"icono" => "error"
				]);
			}

			$ids_mensual = implode(',', $carnet_ids);
			unset($_SESSION['carnet_reimpresion_ids']);
			$_SESSION['carnet_impresion_mensual_ids'] = $ids_mensual;
			$token_mensual = rtrim(strtr(base64_encode($ids_mensual), '+/', '-_'), '=');
			$firma_mensual = hash_hmac('sha256', $ids_mensual, session_id());
			session_write_close();

			return json_encode([
				"tipo" => "redireccionar",
				"titulo" => "PDF preparado",
				"texto" => "Se prepararon " . count($carnet_ids) . " carnets para impresion",
				"icono" => "success",
				"url" => APP_URL . "carnetPDF/?modo=mensual&mensual=" . rawurlencode($token_mensual) . "&firma=" . $firma_mensual
			]);
		}

		public function obtenerCarnetsTodosUnificados($alumno_ids_reimpresion = '') {
			$mes_actual = date('n');
			$anio_actual = date('Y');
			$fecha_actual = date('Y-m-d');
			
			// Obtener color del mes
			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();
			$color_hex = $colorData['color_hex'] ?? '#CCCCCC';
			$mes_nombre = $this->nombreMes($mes_actual);
			
			$carnetsFinales = [];
			
			// ========================================
			// PARTE 1: CARNETS NUEVOS (Primera vez)
			// ========================================
			$consultaNuevos = "SELECT 
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								h.horario_nombre,
								NULL as carnet_id,
								NULL as carnet_numero,
								:mes as carnet_mes,
								:anio as carnet_anio,
								:fecha_actual as carnet_fecha_emision,
								0 as es_reimpresion,
								:color_hex as color_hex,
								:mes_nombre as mes_nombre
							FROM sujeto_alumno a
							INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
							INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
							INNER JOIN alumno_pago ap ON ap.pago_alumnoid = a.alumno_id
							LEFT JOIN alumno_carnet ac ON ac.carnet_alumnoid = a.alumno_id 
													AND ac.carnet_mes = :mes 
													AND ac.carnet_anio = :anio
							WHERE a.alumno_estado = 'A'
								AND ap.pago_estado NOT IN ('E', 'J')
								AND MONTH(ap.pago_fecha) = :mes
								AND YEAR(ap.pago_fecha) = :anio
								AND ap.pago_rubroid = 'RPE'
								AND ac.carnet_alumnoid IS NULL
							
							UNION ALL
							SELECT 
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								h.horario_nombre,
								NULL as carnet_id,
								NULL as carnet_numero,
								:mes as carnet_mes,
								:anio as carnet_anio,
								:fecha_actual as carnet_fecha_emision,
								0 as es_reimpresion,
								:color_hex as color_hex,
								:mes_nombre as mes_nombre
							FROM sujeto_alumno a
							INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
							INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
							INNER JOIN alumno_pago_descuento apd ON apd.descuento_alumnoid = a.alumno_id
							LEFT JOIN alumno_carnet ac ON ac.carnet_alumnoid = a.alumno_id 
													AND ac.carnet_mes = :mes 
													AND ac.carnet_anio = :anio
							WHERE a.alumno_estado = 'A'
								AND apd.descuento_rubroid = 'DBC'
								AND apd.descuento_valor = 0
								AND apd.descuento_estado = 'S'
								AND ac.carnet_alumnoid IS NULL";
			
			$parametros = [
				':fecha_actual' => $fecha_actual,
				':mes' => $mes_actual,
				':anio' => $anio_actual,
				':color_hex' => $color_hex,
				':mes_nombre' => $mes_nombre
			];
			
			$datos = $this->ejecutarConsulta($consultaNuevos, $parametros);
			$carnetsNuevos = $datos->fetchAll();
			
			// Crear carnets nuevos en BD
			foreach($carnetsNuevos as &$carnet) {
				$nuevoCarnet = $this->crearCarnet(
					$carnet['alumno_id'], 
					$carnet['alumno_carnet'], 
					$mes_actual, 
					$anio_actual
				);
				$carnet['carnet_id'] = $nuevoCarnet['carnet_id'];
				$carnet['carnet_numero'] = $nuevoCarnet['carnet_numero'];
				$carnetsFinales[] = $carnet;
			}
			
			// ========================================
			// PARTE 2: REIMPRESIONES
			// ========================================
			if(!empty($alumno_ids_reimpresion)) {
				// ✅ INTENTAR DECODIFICAR BASE64 PRIMERO
				$ids_decodificados = base64_decode($alumno_ids_reimpresion, true);
				if($ids_decodificados !== false && strpos($ids_decodificados, ',') !== false) {
					// Era base64, usar la versión decodificada
					$alumno_ids_reimpresion = $ids_decodificados;
				}
				
				$alumno_ids = explode(',', $alumno_ids_reimpresion);
				$alumno_ids = array_map('intval', $alumno_ids);
				$alumno_ids = array_filter($alumno_ids);
				
				if(!empty($alumno_ids)) {
					$ids_string = implode(',', $alumno_ids);
					
					$consultaReimpresion = "SELECT 
											a.alumno_id,
											a.alumno_carnet,
											a.alumno_identificacion,
											CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
												a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
											a.alumno_imagen,
											h.horario_nombre,
											ac.carnet_id,
											ac.carnet_numero,
											ac.carnet_mes,
											ac.carnet_anio,
											ac.carnet_fecha_emision,
											1 as es_reimpresion,
											:color_hex as color_hex,
											:mes_nombre as mes_nombre
										FROM sujeto_alumno a
										INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
										INNER JOIN asistencia_horario h ON h.horario_id = ah.asignahorario_horarioid
										INNER JOIN alumno_carnet ac ON ac.carnet_alumnoid = a.alumno_id
										WHERE a.alumno_id IN ($ids_string)
										AND ac.carnet_mes = :mes
										AND ac.carnet_anio = :anio
										ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";
					
					$parametrosReimpresion = [
						':mes' => $mes_actual,
						':anio' => $anio_actual,
						':color_hex' => $color_hex,
						':mes_nombre' => $mes_nombre
					];
					
					$datosReimpresion = $this->ejecutarConsulta($consultaReimpresion, $parametrosReimpresion);
					$carnetsReimpresion = $datosReimpresion->fetchAll();
					
					$carnetsFinales = array_merge($carnetsFinales, $carnetsReimpresion);
				}
			}
			
			return $carnetsFinales;
		}

		/**
		 * Obtener resumen de carnets a imprimir
		 * @param array $carnets Array de carnets obtenido de obtenerCarnetsTodosUnificados()
		 * @return array Resumen con totales
		 */
		public function obtenerResumenImpresion($carnets) {
			$nuevos = 0;
			$reimpresiones = 0;
			
			foreach($carnets as $carnet) {
				if($carnet['es_reimpresion'] == 1) {
					$reimpresiones++;
				} else {
					$nuevos++;
				}
			}
			
			return [
				'total' => count($carnets),
				'nuevos' => $nuevos,
				'reimpresiones' => $reimpresiones
			];
		}
    }
