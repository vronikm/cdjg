<?php
	namespace app\controllers;
	use app\models\mainModel;

	class dashboardController extends mainModel{

		/*----------  Obtener total alumnos activos  ----------*/
		public function obtenerAlumnosActivos($sedeid){
			$alumnosActivos=$this->ejecutarConsulta("SELECT count(*) totalActivos FROM sujeto_alumno WHERE alumno_estado='A' and alumno_sedeid = $sedeid");
		    return $alumnosActivos;
		}

		/*----------  Obtener total alumnos inactivos  ----------*/
		public function obtenerAlumnosInactivos($sedeid){
			$alumnosInactivos=$this->ejecutarConsulta("SELECT count(*) totalInactivos FROM sujeto_alumno WHERE alumno_estado='I' and alumno_sedeid = $sedeid");
		    return $alumnosInactivos;
		}

		/*----------  Obtener total pagos cancelados  ----------*/
		public function obtenerPagosCancelados($sede_id){
			$pagosCancelados=$this->ejecutarConsulta("SELECT sum(totalCancelado) totalCancelados 
																			FROM (SELECT COUNT(*) totalCancelado 
																						FROM alumno_pago, sujeto_alumno 
																						WHERE pago_alumnoid = alumno_id 
																							AND alumno_sedeid = ".$sede_id." 
																							AND pago_estado <> 'E'
																					UNION ALL
																					SELECT COUNT(*) totalCancelado
																						FROM alumno_pago, alumno_pago_transaccion, sujeto_alumno 
																						WHERE pago_alumnoid = alumno_id 
																							AND pago_id = transaccion_pagoid 
																							AND alumno_sedeid = ".$sede_id." 
																							AND transaccion_estado<> 'E') AS DATOS");
			return $pagosCancelados;
		}

		/*----------  Obtener total pagos pendientes  ----------*/
		public function obtenerPagosPendientes($sedeid){
			$pagosPendientes=$this->ejecutarConsulta("SELECT count(*) as totalPendientes 
															FROM (
																SELECT 
																	alumno_id, 
																	alumno_identificacion, 
																	CONCAT_WS(' ', alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) AS NOMBRES,  
																	IFNULL(P.TOTAL,0) AS NUM_SALDO, 
																	IFNULL(P.SALDO,0) AS SALDO, 
																	IFNULL(PEN.PENSIONES,0) AS NUM_PENSION, 
																	IFNULL(PEN.TOTAL,0) AS PENSION, 
																	PEN.FECHA
																FROM sujeto_alumno A
																LEFT JOIN (
																	SELECT 
																		pago_alumnoid, 
																		COUNT(pago_saldo) AS TOTAL, 
																		SUM(pago_saldo) AS SALDO
																	FROM alumno_pago
																		INNER JOIN sujeto_alumno ON alumno_id = pago_alumnoid
																	WHERE pago_estado = 'P' AND pago_saldo > 0 AND alumno_sedeid = ".$sedeid." 
																	GROUP BY pago_alumnoid
																) P ON P.pago_alumnoid = A.alumno_id
																LEFT JOIN (
																	SELECT 
																		BASE.FECHA,
																		BASE.pago_alumnoid,
																		CASE WHEN BASE.FECHA > CURDATE() THEN 0 ELSE
																			GREATEST(0, TIMESTAMPDIFF(MONTH, BASE.FECHA, CURDATE()) + (DAY(CURDATE()) < DAY(BASE.FECHA))) END AS PENSIONES,
																		CASE WHEN BASE.FECHA > CURDATE() THEN 0 ELSE
																			GREATEST(0, TIMESTAMPDIFF(MONTH, BASE.FECHA, CURDATE()) + (DAY(CURDATE()) < DAY(BASE.FECHA))) * COALESCE(BASE.descuento_valor, BASE.sede_pension) END AS TOTAL
																	FROM (
																		SELECT 
																			MAX(pago_fecha) AS FECHA, 
																			pago_alumnoid, 
																			MAX(descuento_valor) AS descuento_valor, 
																			MAX(sede_pension) AS sede_pension   
																		FROM 
																			sujeto_alumno
																			LEFT JOIN alumno_pago ON pago_alumnoid = alumno_id 
																			LEFT JOIN alumno_pago_descuento ON descuento_alumnoid = alumno_id AND descuento_estado = 'S'
																			LEFT JOIN general_sede ON sede_id = alumno_sedeid
																		WHERE pago_rubroid = 'RPE' AND alumno_estado <> 'I' AND alumno_sedeid = ".$sedeid."
																		GROUP BY 
																			pago_alumnoid
																	) BASE
																) PEN ON PEN.pago_alumnoid = A.alumno_id
																WHERE A.alumno_estado <> 'E'
																	AND PEN.TOTAL > 0 OR P.SALDO > 0 
															) AS subconsulta;");
			return $pagosPendientes;
		}

		public function dashboardAlumnos($sedeid, $estado){
			$tabla="";
			$consulta_datos="SELECT * FROM sujeto_alumno WHERE alumno_sedeid = $sedeid AND alumno_estado = '".$estado."'";
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['alumno_numcamiseta'].'</td>
						<td>'.$rows['alumno_primernombre'].' '.$rows['alumno_segundonombre'].'</td>
						<td>'.$rows['alumno_apellidopaterno'].' '.$rows['alumno_apellidomaterno'].'</td>
						<td>'.$rows['alumno_fechanacimiento'].'</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function informacionSede($sedeid){		
			$consulta_datos="SELECT * FROM general_sede WHERE sede_id  = $sedeid";
			$datos = $this->ejecutarConsulta($consulta_datos);		
			return $datos;
		}
		public function obtenerRepresentantes(){
			$representantes=$this->ejecutarConsulta("SELECT count(*) totalRepresentantes FROM alumno_representante WHERE repre_estado='A'");
		    return $representantes;
		}

		public function totalAlumnosActivos(){
			$alumnosActivos=$this->ejecutarConsulta("SELECT count(*) totalAlumnosActivos FROM sujeto_alumno WHERE alumno_estado='A'");
		    return $alumnosActivos;
		}
		public function totalAlumnosInactivos(){
			$alumnosInactivos=$this->ejecutarConsulta("SELECT count(*) totalAlumnosInactivos FROM sujeto_alumno WHERE alumno_estado='I'");
		    return $alumnosInactivos;
		}
	}

		