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

		private function filtroSedeCarnetSQL($sedeid, $alias = 'a') {
			$sedeid = (int)$sedeid;
			if($sedeid <= 0) {
				return "";
			}

			$alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
			$campo = $alias !== '' ? $alias . ".alumno_sedeid" : "alumno_sedeid";
			return " AND " . $campo . " = " . $sedeid;
		}

		private function sanitizarAliasSQL($alias) {
			return preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
		}

		private function campoSQL($alias, $campo) {
			$alias = $this->sanitizarAliasSQL($alias);
			return ($alias !== '' ? $alias . "." : "") . $campo;
		}

		private function sqlFechaPagoCarnet($alias = 'ap') {
			$pagoRubroid = $this->campoSQL($alias, 'pago_rubroid');
			$pagoFecha = $this->campoSQL($alias, 'pago_fecha');
			$pagoFechaRegistro = $this->campoSQL($alias, 'pago_fecharegistro');

			return "CASE WHEN ".$pagoRubroid." = 'RVA' THEN DATE(".$pagoFechaRegistro.") ELSE ".$pagoFecha." END";
		}

		private function sqlMesPagoPeriodo($alias = 'ap') {
			$pagoPeriodo = $this->campoSQL($alias, 'pago_periodo');
			$mesPeriodo = "LOWER(TRIM(SUBSTRING_INDEX(".$pagoPeriodo.", '/', 1)))";

			return "CASE ".$mesPeriodo."
						WHEN 'enero' THEN 1
						WHEN 'febrero' THEN 2
						WHEN 'marzo' THEN 3
						WHEN 'abril' THEN 4
						WHEN 'mayo' THEN 5
						WHEN 'junio' THEN 6
						WHEN 'julio' THEN 7
						WHEN 'agosto' THEN 8
						WHEN 'septiembre' THEN 9
						WHEN 'setiembre' THEN 9
						WHEN 'octubre' THEN 10
						WHEN 'noviembre' THEN 11
						WHEN 'diciembre' THEN 12
						WHEN '1' THEN 1
						WHEN '01' THEN 1
						WHEN '2' THEN 2
						WHEN '02' THEN 2
						WHEN '3' THEN 3
						WHEN '03' THEN 3
						WHEN '4' THEN 4
						WHEN '04' THEN 4
						WHEN '5' THEN 5
						WHEN '05' THEN 5
						WHEN '6' THEN 6
						WHEN '06' THEN 6
						WHEN '7' THEN 7
						WHEN '07' THEN 7
						WHEN '8' THEN 8
						WHEN '08' THEN 8
						WHEN '9' THEN 9
						WHEN '09' THEN 9
						WHEN '10' THEN 10
						WHEN '11' THEN 11
						WHEN '12' THEN 12
						ELSE MONTH(".$this->sqlFechaPagoCarnet($alias).")
					END";
		}

		private function sqlAnioPagoPeriodo($alias = 'ap') {
			$pagoPeriodo = $this->campoSQL($alias, 'pago_periodo');
			$anioPeriodo = "CAST(TRIM(SUBSTRING_INDEX(".$pagoPeriodo.", '/', -1)) AS UNSIGNED)";

			return "CASE
						WHEN ".$anioPeriodo." BETWEEN 2000 AND 2100 THEN ".$anioPeriodo."
						ELSE YEAR(".$this->sqlFechaPagoCarnet($alias).")
					END";
		}

		private function sqlFechaPeriodoPago($alias = 'ap') {
			return "STR_TO_DATE(CONCAT(".$this->sqlAnioPagoPeriodo($alias).", '-', LPAD(".$this->sqlMesPagoPeriodo($alias).", 2, '0'), '-05'), '%Y-%m-%d')";
		}

		public function listarOptionSedeCarnet($sedeid = 0) {
			$sedeid = (int)$sedeid;
			$option = '<option value="0">Todas las sedes</option>';
			$consulta_datos = "SELECT sede_id, sede_nombre FROM general_sede ORDER BY sede_nombre";
			$datos = $this->ejecutarConsulta($consulta_datos);

			foreach($datos->fetchAll() as $rows) {
				$selected = ((int)$rows['sede_id'] === $sedeid) ? ' selected="selected"' : '';
				$option .= '<option value="' . (int)$rows['sede_id'] . '"' . $selected . '>' .
					htmlspecialchars($rows['sede_nombre'], ENT_QUOTES, 'UTF-8') .
				'</option>';
			}

			return $option;
		}

		private function condicionPagoCarnetActual($alias = 'ap') {
			$pagoRubroid = $this->campoSQL($alias, 'pago_rubroid');
			$fechaPago = $this->sqlFechaPagoCarnet($alias);
			$fechaPeriodo = $this->sqlFechaPeriodoPago($alias);

			return "(".$pagoRubroid." IN ('RPE', 'RVA')
					AND (
						(".$fechaPago." >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
							AND ".$fechaPago." <= LAST_DAY(CURDATE()))
						OR (".$fechaPeriodo." >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
							AND ".$fechaPeriodo." <= LAST_DAY(CURDATE()))
					))";
		}

		private function condicionPagoCarnetActualSiguiente($alias = 'ap') {
			$pagoRubroid = $this->campoSQL($alias, 'pago_rubroid');
			$fechaPago = $this->sqlFechaPagoCarnet($alias);
			$fechaPeriodo = $this->sqlFechaPeriodoPago($alias);

			return "(".$pagoRubroid." IN ('RPE', 'RVA')
					AND (
						(".$fechaPago." >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
							AND ".$fechaPago." <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)))
						OR (".$fechaPeriodo." >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
							AND ".$fechaPeriodo." <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)))
					))";
		}

		private function condicionPagoCarnetMes($alias = 'ap') {
			$pagoRubroid = $this->campoSQL($alias, 'pago_rubroid');
			return "(".$pagoRubroid." IN ('RPE', 'RVA')
					AND ".$this->sqlMesPagoPeriodo($alias)." = :mes
					AND ".$this->sqlAnioPagoPeriodo($alias)." = :anio)";
		}

		/**
		 * Listar alumnos con pagos de pensión del mes actual
		 * @return string HTML de filas de tabla
		 */
		public function listarAlumnos($sedeid = 0) {
			$tabla = "";
			$sedeid = (int)$sedeid;

			$alumnos_sin_horario = $this->verificarAlumnosSinHorarioAsignado($sedeid);
			if(!empty($alumnos_sin_horario)) {
				$detalle_alumnos = [];
				foreach($alumnos_sin_horario as $alumno) {
					$detalle_alumnos[] = $alumno['alumno_nombre'] . " (" . $alumno['alumno_identificacion'] . ")";
				}

				$mensaje = htmlspecialchars(implode(", ", $detalle_alumnos), ENT_QUOTES, 'UTF-8');
				return '<tr>
							<td>
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
									FechaPeriodoCarnet,
									MONTH(FechaPeriodoCarnet) AS carnet_mes_objetivo,
									YEAR(FechaPeriodoCarnet) AS carnet_anio_objetivo,
									CASE 
										WHEN FechaPeriodoCarnet >= DATE_FORMAT(CURDATE(), '%Y-%m-01')                               
										THEN 'Al día' 
										ELSE 'Pendiente' 
									END Condicion,
									CASE
										WHEN EXISTS(
											SELECT 1
											FROM alumno_carnet ac
											WHERE ac.carnet_alumnoid = alumno_id
											AND ac.carnet_mes = MONTH(FechaPeriodoCarnet)
											AND ac.carnet_anio = YEAR(FechaPeriodoCarnet)
											AND ac.carnet_fecha_impresion IS NOT NULL
										) THEN 1
										ELSE 0
									END AS carnet_impreso,
									(
										SELECT MAX(ac2.carnet_fecha_impresion)
										FROM alumno_carnet ac2
										WHERE ac2.carnet_alumnoid = alumno_id
										AND ac2.carnet_mes = MONTH(FechaPeriodoCarnet)
										AND ac2.carnet_anio = YEAR(FechaPeriodoCarnet)
									) AS fecha_impresion
								FROM sujeto_alumno
								INNER JOIN (    
								(    
									SELECT pago_alumnoid, MAX(FechaPension) FechaUltPension, MAX(pago_estado) Estado, MAX(FechaPeriodoCarnet) FechaPeriodoCarnet
									FROM (
										SELECT ".$this->sqlFechaPagoCarnet('ap')." as FechaPension,
												".$this->sqlFechaPeriodoPago('ap')." as FechaPeriodoCarnet,
												ap.pago_estado,
												ap.pago_alumnoid
											FROM alumno_pago ap
											WHERE ap.pago_estado NOT IN ('E', 'J')
												AND ".$this->condicionPagoCarnetActualSiguiente('ap')."
									) AS Pagos
									GROUP BY pago_alumnoid
								)
								UNION                
								SELECT descuento_alumnoid, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPago, 'Al dìa' as Estado
												, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPeriodoCarnet
												from alumno_pago_descuento
												where descuento_rubroid = 'DBC'
																and descuento_valor = 0
																and descuento_estado = 'S'     
												) EstadoPagos ON pago_alumnoid = alumno_id
												WHERE alumno_estado = 'A'
												".$this->filtroSedeCarnetSQL($sedeid, '')."
												ORDER BY carnet_anio_objetivo ASC, carnet_mes_objetivo ASC, carnet_impreso ASC, alumno_apellidopaterno, alumno_apellidomaterno";
			
			$datos = $this->ejecutarConsulta($consulta_datos);

			if($datos->rowCount() > 0) {
				$datos = $datos->fetchAll();
				
				foreach($datos as $rows) {
					$mesObjetivo = (int)($rows['carnet_mes_objetivo'] ?? date('n'));
					$anioObjetivo = (int)($rows['carnet_anio_objetivo'] ?? date('Y'));
					$periodoCarnet = $this->nombreMes($mesObjetivo) . ' ' . $anioObjetivo;
					$fechaUltPago = htmlspecialchars((string)$rows['FechaUltPension'], ENT_QUOTES, 'UTF-8');
					$fechaUltPago .= '<br><small class="badge badge-info">' . htmlspecialchars($periodoCarnet, ENT_QUOTES, 'UTF-8') . '</small>';

					if((int)$rows['carnet_impreso'] === 1) {
						$estadoImpresion = '<span class="badge badge-success"><i class="fas fa-check"></i> Impreso</span>';
						if(!empty($rows['fecha_impresion'])) {
							$estadoImpresion .= '<br><small class="text-muted">' . $rows['fecha_impresion'] . '</small>';
						}
					} else {
						$estadoImpresion = '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>';
					}
	
					$esPeriodoActual = ($mesObjetivo === (int)date('n') && $anioObjetivo === (int)date('Y'));
					$reimpresionDisabled = ((int)$rows['carnet_impreso'] === 1 && $esPeriodoActual)
						? ''
						: ' disabled title="Use Imprimir Todos para emitir el carnet pendiente o espere al mes correspondiente para reimprimir"';

					$tabla .= '				
						<tr>
							<td>' . $rows['alumno_identificacion'] . '</td>
							<td>' . $rows['NOMBRES'] . '</td>
							<td>' . $rows['APELLIDOS'] . '</td>
							<td>' . $rows['alumno_carnet'] . '</td>
							<td>' . $fechaUltPago . '</td>
							<td data-order="' . (int)$rows['carnet_impreso'] . '">' . $estadoImpresion . '</td>
							<td>							
								<a href="' . APP_URL . 'carnetFotoPDF/' . $rows['alumno_id'] . '/' . $mesObjetivo . '/' . $anioObjetivo . '/"
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
				$tabla = '';
			}
			
			return $tabla;			
		}

		public function verificarAlumnosSinHorarioAsignado($sedeid = 0) {
			$sedeid = (int)$sedeid;
			$consulta = "SELECT
						a.alumno_id,
						a.alumno_identificacion,
						CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ',
							a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) AS alumno_nombre
						FROM sujeto_alumno a
						INNER JOIN (
							SELECT ap.pago_alumnoid
							FROM alumno_pago ap
							WHERE ap.pago_estado NOT IN ('E', 'J')
								AND ".$this->condicionPagoCarnetActualSiguiente('ap')."

							UNION

							SELECT descuento_alumnoid
							FROM alumno_pago_descuento
							WHERE descuento_rubroid = 'DBC'
								AND descuento_valor = 0
								AND descuento_estado = 'S'
						) candidatos ON candidatos.pago_alumnoid = a.alumno_id
						WHERE a.alumno_estado = 'A'
							".$this->filtroSedeCarnetSQL($sedeid, 'a')."
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
			$alumnoid = (int)$alumnoid;
            $consulta_datos="SELECT a.alumno_identificacion,
									CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre) Nombres,
									CONCAT(a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) Apellidos,
									a.alumno_fechanacimiento,
									COALESCE(
										(SELECT CASE
											WHEN COUNT(DISTINCT CONCAT(TIME_FORMAT(hora.hora_inicio, '%H:%i'), '-', TIME_FORMAT(hora.hora_fin, '%H:%i'))) = 1
											THEN MIN(CONCAT(TIME_FORMAT(hora.hora_inicio, '%H:%i'), '-', TIME_FORMAT(hora.hora_fin, '%H:%i')))
											ELSE NULL
										END
										FROM asistencia_horario_detalle detalle
										INNER JOIN asistencia_hora hora ON hora.hora_id = detalle.detalle_horaid
										WHERE detalle.detalle_horarioid = h.horario_id),
										h.horario_nombre
									) AS horario_nombre,
									a.alumno_carnet, a.alumno_imagen, a.alumno_sedeid
								FROM sujeto_alumno a
								INNER JOIN asistencia_asignahorario ah ON ah.asignahorario_alumnoid = a.alumno_id
								INNER JOIN asistencia_horario h ON ah.asignahorario_horarioid = h.horario_id
								WHERE a.alumno_id = $alumnoid";
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

        public function EstadoAlumno($alumnoid, $mes = null, $anio = null){
			$alumnoid = (int)$alumnoid;
			$mes = (int)$mes;
			$anio = (int)$anio;
			$filtroPeriodo = "";
			$descuentoPeriodo = "";
			$fechaPagoCarnet = $this->sqlFechaPagoCarnet('ap');
			$fechaPeriodoCarnet = $this->sqlFechaPeriodoPago('ap');

			if($mes >= 1 && $mes <= 12 && $anio > 2000) {
				$filtroPeriodo = " AND ".$this->sqlMesPagoPeriodo('ap')." = $mes
									AND ".$this->sqlAnioPagoPeriodo('ap')." = $anio";

				if($mes !== (int)date('n') || $anio !== (int)date('Y')) {
					$descuentoPeriodo = " AND 1 = 0";
				}
			}

			$consulta_datos="SELECT FechaUltPension, Estado, 
								CASE 
										WHEN FechaPeriodoCarnet >= DATE_FORMAT(CURDATE(), '%Y-%m-01')                               
										THEN 'Al dia' 
										ELSE 'Pendiente' 
										END Condicion
								FROM(
									SELECT
										MAX(".$fechaPagoCarnet.") FechaUltPension,
										MAX(".$fechaPeriodoCarnet.") FechaPeriodoCarnet,
										MAX(ap.pago_estado) Estado
									FROM alumno_pago ap
									WHERE ap.pago_alumnoid = $alumnoid
										AND ap.pago_estado NOT IN ('J','E')
										$filtroPeriodo
								) AS Total
								WHERE FechaUltPension IS NOT NULL
							UNION
							SELECT DATE_FORMAT(CURDATE(), '%Y-%m-01') FechaPago, 'C' as Estado, 'Al dìa' as Condicion
                                from alumno_pago_descuento
                                where descuento_rubroid = 'DBC'
                                                and descuento_valor = 0
                                                and descuento_estado = 'S'
												and descuento_alumnoid = $alumnoid
												$descuentoPeriodo";
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
		 * Reemplaza el nombre almacenado del horario por su franja real cuando
		 * todos los detalles asignados al alumno comparten la misma hora.
		 */
		private function aplicarHorarioRealCarnet(array $carnets) {
			$alumnoIds = [];
			foreach($carnets as $carnet) {
				$alumnoId = (int)($carnet['alumno_id'] ?? 0);
				if($alumnoId > 0) {
					$alumnoIds[$alumnoId] = $alumnoId;
				}
			}

			if(empty($alumnoIds)) {
				return $carnets;
			}

			$idsSql = implode(',', $alumnoIds);
			$consulta = "SELECT ah.asignahorario_alumnoid AS alumno_id,
							COUNT(DISTINCT CONCAT(TIME_FORMAT(hora.hora_inicio, '%H:%i'), '-', TIME_FORMAT(hora.hora_fin, '%H:%i'))) AS total_franjas,
							MIN(CONCAT(TIME_FORMAT(hora.hora_inicio, '%H:%i'), '-', TIME_FORMAT(hora.hora_fin, '%H:%i'))) AS horario_real
						FROM asistencia_asignahorario ah
						INNER JOIN asistencia_horario_detalle detalle ON detalle.detalle_horarioid = ah.asignahorario_horarioid
						INNER JOIN asistencia_hora hora ON hora.hora_id = detalle.detalle_horaid
						WHERE ah.asignahorario_alumnoid IN (".$idsSql.")
						GROUP BY ah.asignahorario_alumnoid";

			try {
				$horarios = [];
				foreach($this->ejecutarConsulta($consulta)->fetchAll() as $horario) {
					$franjas = (int)($horario['total_franjas'] ?? 0);
					$horarioReal = trim((string)($horario['horario_real'] ?? ''));
					if($franjas === 1 && $horarioReal !== '') {
						$horarios[(int)$horario['alumno_id']] = $horarioReal;
					}
				}

				foreach($carnets as &$carnet) {
					$alumnoId = (int)($carnet['alumno_id'] ?? 0);
					if(isset($horarios[$alumnoId])) {
						$carnet['horario_nombre'] = $horarios[$alumnoId];
					}
				}
				unset($carnet);
			}catch(\Throwable $e) {
				// Un horario incompleto no debe impedir la impresion de los carnets.
			}

			return $carnets;
		}
		
				
		/**
		 * ============================================================
		 * MÉTODOS PARA GENERACIÓN E IMPRESIÓN DE CARNETS
		 * ============================================================
		 */

		/**
		 * Obtener carnets del mes actual listos para imprimir
		 * Incluye todos los alumnos con pagos de pensión (RPE) o vacacional (RVA) del mes
		 * @return array Array con datos de carnets
		 */
		public function obtenerCarnetsMesActual() {
			$fecha_actual = date('Y-m-d');
			$mes_actual = date('n'); // Mes actual (1-12)
			$anio_actual = date('Y');
			
			// Obtener color asignado al mes
			$colorMes = $this->BuscarColorPorMes($mes_actual);
			$colorData = $colorMes->fetch();
			
			$consulta = "SELECT DISTINCT
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
								AND ".$this->condicionPagoCarnetMes('ap')."
								AND ac.carnet_alumnoid IS NULL
						UNION
						
						SELECT
							a.alumno_id,
							a.alumno_carnet,
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
			$carnets = $this->aplicarHorarioRealCarnet($datos->fetchAll());
			
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
							a.alumno_sedeid,
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
			return $this->aplicarHorarioRealCarnet($datos->fetchAll());
		}

		public function carnetPendientesImpresion($sedeid = 0) {
			$sedeid = (int)$sedeid;
			$consulta = "SELECT COUNT(*) AS total
						FROM (
							SELECT a.alumno_id
							FROM (
								SELECT pago_alumnoid, MAX(FechaPension) FechaUltPension, MAX(FechaPeriodoCarnet) FechaPeriodoCarnet
								FROM (
									SELECT ap.pago_alumnoid,
											".$this->sqlFechaPagoCarnet('ap')." AS FechaPension,
											".$this->sqlFechaPeriodoPago('ap')." AS FechaPeriodoCarnet
									FROM alumno_pago ap
									WHERE ap.pago_estado NOT IN ('E', 'J')
										AND ".$this->condicionPagoCarnetActualSiguiente('ap')."

									UNION ALL

									SELECT apd.descuento_alumnoid, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPension, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPeriodoCarnet
									FROM alumno_pago_descuento apd
									WHERE apd.descuento_rubroid = 'DBC'
										AND apd.descuento_valor = 0
										AND apd.descuento_estado = 'S'
								) pagos
								GROUP BY pago_alumnoid
							) elegibles
							INNER JOIN sujeto_alumno a ON a.alumno_id = elegibles.pago_alumnoid
							WHERE a.alumno_estado = 'A'
								".$this->filtroSedeCarnetSQL($sedeid, 'a')."
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
								AND NOT EXISTS (
									SELECT 1 FROM alumno_carnet ac
									WHERE ac.carnet_alumnoid = a.alumno_id
										AND ac.carnet_mes = MONTH(elegibles.FechaPeriodoCarnet)
										AND ac.carnet_anio = YEAR(elegibles.FechaPeriodoCarnet)
										AND ac.carnet_fecha_impresion IS NOT NULL
								)
						) AS subconsulta";

			$datos = $this->ejecutarConsulta($consulta);
			return $datos->fetchAll();
		}

		public function obtenerCarnetsPendientesMesActual($sedeid = 0) {
			$sedeid = (int)$sedeid;
			$fecha_actual = date('Y-m-d');

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
								COALESCE(ac.carnet_mes, MONTH(elegibles.FechaPeriodoCarnet)) as carnet_mes,
								COALESCE(ac.carnet_anio, YEAR(elegibles.FechaPeriodoCarnet)) as carnet_anio,
								COALESCE(ac.carnet_fecha_emision, :fecha_actual) as carnet_fecha_emision,
								0 as es_reimpresion,
								COALESCE(cc.catcolor_hex, '#CCCCCC') as color_hex,
								'' as mes_nombre
							FROM (
								SELECT pago_alumnoid, MAX(FechaPension) FechaUltPension, MAX(FechaPeriodoCarnet) FechaPeriodoCarnet
								FROM (
									SELECT ap.pago_alumnoid,
											".$this->sqlFechaPagoCarnet('ap')." AS FechaPension,
											".$this->sqlFechaPeriodoPago('ap')." AS FechaPeriodoCarnet
									FROM alumno_pago ap
									WHERE ap.pago_estado NOT IN ('E', 'J')
										AND ".$this->condicionPagoCarnetActualSiguiente('ap')."

									UNION ALL

									SELECT apd.descuento_alumnoid, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPension, DATE_FORMAT(CURDATE(), '%Y-%m-05') FechaPeriodoCarnet
									FROM alumno_pago_descuento apd
									WHERE apd.descuento_rubroid = 'DBC'
										AND apd.descuento_valor = 0
										AND apd.descuento_estado = 'S'
								) pagos
								GROUP BY pago_alumnoid
							) elegibles
							INNER JOIN sujeto_alumno a ON a.alumno_id = elegibles.pago_alumnoid
							LEFT JOIN alumno_carnet ac ON ac.carnet_id = (
								SELECT ac2.carnet_id
								FROM alumno_carnet ac2
								WHERE ac2.carnet_alumnoid = a.alumno_id
									AND ac2.carnet_mes = MONTH(elegibles.FechaPeriodoCarnet)
									AND ac2.carnet_anio = YEAR(elegibles.FechaPeriodoCarnet)
								ORDER BY ac2.carnet_id DESC
								LIMIT 1
							)
							LEFT JOIN carnet_mes_color cmc ON cmc.mcolor_mes = MONTH(elegibles.FechaPeriodoCarnet)
								AND cmc.mcolor_activo = 1
							LEFT JOIN carnet_catcolor cc ON cc.catcolor_id = cmc.mcolor_catcolorid
								AND cc.catcolor_activo = 1
							WHERE a.alumno_estado = 'A'
								".$this->filtroSedeCarnetSQL($sedeid, 'a')."
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
								AND NOT EXISTS (
									SELECT 1 FROM alumno_carnet acp
									WHERE acp.carnet_alumnoid = a.alumno_id
										AND acp.carnet_mes = MONTH(elegibles.FechaPeriodoCarnet)
										AND acp.carnet_anio = YEAR(elegibles.FechaPeriodoCarnet)
										AND acp.carnet_fecha_impresion IS NOT NULL
								)
							ORDER BY a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta, [
				':fecha_actual' => $fecha_actual
			]);

			$carnets = $this->aplicarHorarioRealCarnet($datos->fetchAll());
			$carnetsFinales = [];

			foreach($carnets as $carnet) {
				$mesCarnet = (int)$carnet['carnet_mes'];
				$anioCarnet = (int)$carnet['carnet_anio'];
				$carnet['mes_nombre'] = $this->nombreMes($mesCarnet);

				if(empty($carnet['carnet_id'])) {
					$nuevoCarnet = $this->crearCarnet(
						$carnet['alumno_id'],
						$carnet['alumno_carnet'],
						$mesCarnet,
						$anioCarnet
					);
					$carnet['carnet_id'] = $nuevoCarnet['carnet_id'];
					$carnet['carnet_numero'] = $nuevoCarnet['carnet_numero'];
					$carnet['carnet_fecha_emision'] = $nuevoCarnet['carnet_fecha_emision'];
				}

				$carnetsFinales[] = $carnet;
			}

			if(empty($carnetsFinales)) {
				$carnetsFinales = $this->obtenerCarnetsNoImpresosMesActual($sedeid);
			}

			return $carnetsFinales;
		}

		public function obtenerCarnetsNoImpresosMesActual($sedeid = 0) {
			$sedeid = (int)$sedeid;
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
								COALESCE(cc.catcolor_hex, '#CCCCCC') as color_hex,
								'' as mes_nombre
							FROM alumno_carnet ac
							INNER JOIN sujeto_alumno a ON a.alumno_id = ac.carnet_alumnoid
							LEFT JOIN carnet_mes_color cmc ON cmc.mcolor_mes = ac.carnet_mes
								AND cmc.mcolor_activo = 1
							LEFT JOIN carnet_catcolor cc ON cc.catcolor_id = cmc.mcolor_catcolorid
								AND cc.catcolor_activo = 1
							WHERE (
									(ac.carnet_mes = MONTH(CURDATE()) AND ac.carnet_anio = YEAR(CURDATE()))
									OR (ac.carnet_mes = MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
										AND ac.carnet_anio = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)))
								)
								AND ac.carnet_fecha_impresion IS NULL
								AND a.alumno_estado = 'A'
								".$this->filtroSedeCarnetSQL($sedeid, 'a')."
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
							ORDER BY ac.carnet_anio, ac.carnet_mes, a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta);
			$carnets = $this->aplicarHorarioRealCarnet($datos->fetchAll());

			foreach($carnets as &$carnet) {
				$carnet['mes_nombre'] = $this->nombreMes((int)$carnet['carnet_mes']);
			}
			unset($carnet);

			return $carnets;
		}

		public function obtenerCarnetsMensualesPorIds($carnet_ids_string) {
			$carnet_ids = explode(',', $carnet_ids_string);
			$carnet_ids = array_map('intval', $carnet_ids);
			$carnet_ids = array_filter($carnet_ids);

			if(empty($carnet_ids)) {
				return [];
			}

			$ids_string = implode(',', $carnet_ids);

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
								COALESCE(cc.catcolor_hex, '#CCCCCC') as color_hex,
								'' as mes_nombre
							FROM alumno_carnet ac
							INNER JOIN sujeto_alumno a ON a.alumno_id = ac.carnet_alumnoid
							LEFT JOIN carnet_mes_color cmc ON cmc.mcolor_mes = ac.carnet_mes
								AND cmc.mcolor_activo = 1
							LEFT JOIN carnet_catcolor cc ON cc.catcolor_id = cmc.mcolor_catcolorid
								AND cc.catcolor_activo = 1
							WHERE ac.carnet_id IN ($ids_string)
								AND a.alumno_estado = 'A'
								AND EXISTS (SELECT 1 FROM asistencia_asignahorario ah WHERE ah.asignahorario_alumnoid = a.alumno_id)
							ORDER BY ac.carnet_anio, ac.carnet_mes, a.alumno_apellidopaterno, a.alumno_apellidomaterno";

			$datos = $this->ejecutarConsulta($consulta);
			$carnets = $this->aplicarHorarioRealCarnet($datos->fetchAll());

			foreach($carnets as &$carnet) {
				$carnet['mes_nombre'] = $this->nombreMes((int)$carnet['carnet_mes']);
			}
			unset($carnet);

			return $carnets;
		}

		public function prepararImpresionMensual($sedeid = 0) {
			$sedeid = (int)$sedeid;
			$carnets = $this->obtenerCarnetsPendientesMesActual($sedeid);

			if(empty($carnets)) {
				$carnets = $this->obtenerCarnetsNoImpresosMesActual($sedeid);
			}

			if(empty($carnets)) {
				return json_encode([
					"tipo" => "simple",
					"titulo" => "Sin carnets pendientes",
					"texto" => "No hay carnets pendientes de impresion para el mes actual o el siguiente",
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
			$consultaNuevos = "SELECT DISTINCT
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								a.alumno_sedeid,
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
								AND ".$this->condicionPagoCarnetMes('ap')."
								AND ac.carnet_alumnoid IS NULL
							
							UNION
							SELECT
								a.alumno_id,
								a.alumno_carnet,
								a.alumno_identificacion,
								CONCAT(a.alumno_primernombre, ' ', a.alumno_segundonombre, ' ', 
									a.alumno_apellidopaterno, ' ', a.alumno_apellidomaterno) as alumno_nombre,
								a.alumno_imagen,
								a.alumno_sedeid,
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
											a.alumno_sedeid,
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
			
			return $this->aplicarHorarioRealCarnet($carnetsFinales);
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
