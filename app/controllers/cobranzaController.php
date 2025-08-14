<?php

	namespace app\controllers;
	use app\models\mainModel;

	class cobranzaController extends mainModel{	
        /*----------  Matriz de representantes con opciones Ver, Actualizar, Eliminar  ----------*/
		public function pensionesValormora(){
			$tabla="";
			$consulta_datos = "select alumno_repreid as repre_id, sede_nombre, repre_identificacion, REPRE, repre_celular, ALUMNO,
                                    SUM(SALDO) + SUM(PENSION) AS TOTAL_MORA
                                     FROM(
                                        SELECT  alumno_repreid, 
                                                repre_identificacion,
                                                CONCAT_WS(' ',repre_primernombre, repre_segundonombre, repre_apellidopaterno, repre_apellidomaterno) as REPRE,
                                                IFNULL(P.SALDO,0) AS SALDO, 
                                                IFNULL(PEN.TOTAL,0) AS PENSION, 
                                                PEN.FECHA,
                                                P.FECHASALDOS,
                                                FECHATRX.FECHATRX,
                                                sede_nombre,
                                                repre_celular,
												CONCAT_WS(' ',alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) as ALUMNO
                                            FROM sujeto_alumno A
                                            inner join general_sede on sede_id = alumno_sedeid
                                            LEFT JOIN (
                                                SELECT 
                                                pago_alumnoid, alumno_repreid as REPRESALDOS,
                                                MAX(pago_fecha) AS FECHASALDOS,
                                                COUNT(pago_saldo) AS TOTAL, 
                                                SUM(pago_saldo) AS SALDO
                                                FROM alumno_pago
                                                    INNER JOIN sujeto_alumno ON alumno_id = pago_alumnoid
                                                WHERE pago_rubroid = 'RPE' AND pago_estado = 'P' AND pago_saldo > 0
                                                GROUP BY pago_alumnoid, alumno_repreid
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
                                                WHERE pago_rubroid = 'RPE' AND alumno_estado <> 'I' 
                                                GROUP BY 
                                                    pago_alumnoid
                                                ) BASE
                                            ) PEN ON PEN.pago_alumnoid = A.alumno_id
                                            inner join alumno_representante R on R.repre_id = A.alumno_repreid
                                            left join(					
                                                select pago_alumnoid, transaccion_pagoid, transaccion_estado, max(transaccion_fecha) FECHATRX
                                                    from alumno_pago_transaccion
                                                    inner join alumno_pago on pago_id = transaccion_pagoid
                                                    where transaccion_estado = 'C'
                                                        and pago_estado = 'P'
                                                    group by pago_alumnoid, transaccion_pagoid, transaccion_estado						
                                            ) FECHATRX on FECHATRX.pago_alumnoid = A.alumno_id
                                            WHERE (PEN.TOTAL > 0 OR P.SALDO > 0) 
      											  AND A.alumno_estado = 'A'  -- CONDICIÓN PARA ALUMNOS ACTIVOS
                                        ) as VALORESMORA			
                                    group by alumno_repreid, repre_identificacion, REPRE, sede_nombre, repre_celular, ALUMNO";
				
			$datos = $this->ejecutarConsulta($consulta_datos);
		
			if($datos->rowCount()>0){
				$datos = $datos->fetchAll();
			}

			foreach ($datos as $rows) {
				$celular = substr($rows['repre_celular'], 1);				
				$tabla.='				
					<tr>
                        <td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['repre_identificacion'].'</td>
						<td>'.$rows['REPRE'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['TOTAL_MORA'].'</td>
						<td>                            
							<a href="https://wa.me/593'.$celular.'?text=Estimado representante, Club Deportivo Jorge Guzmán le recuerda que a la presente fecha usted mantiene un saldo pendiente de pensiones, por el valor de USD $'.$rows["TOTAL_MORA"].', agradecemos su gentileza en realizar el pago correspondiente." target="_blank" class="btn float-right btn-actualizar btn-xs" style="margin-right: 5px;">Notificar</a>										
                            <a href="'.APP_URL.'cobranzaDetallePension/'.$rows['repre_id'].'/" class="btn float-right btn-ver btn-xs" style="margin-right: 5px;">Detalle</a>
						</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function pensionesValormoraInactivos(){
			$tabla="";
			$consulta_datos = "SELECT alumno_repreid as repre_id, sede_nombre, repre_identificacion, REPRE, repre_celular, ALUMNO,
                                    SUM(SALDO) + SUM(PENSION) AS TOTAL_MORA
                                     FROM(
                                        SELECT  alumno_repreid, 
                                                repre_identificacion,
                                                CONCAT_WS(' ',repre_primernombre, repre_segundonombre, repre_apellidopaterno, repre_apellidomaterno) as REPRE,
                                                IFNULL(P.SALDO,0) AS SALDO, 
                                                IFNULL(PEN.TOTAL,0) AS PENSION, 
												PEN.FECHA,
                                                P.FECHASALDOS,
                                                FECHATRX.FECHATRX,
                                                sede_nombre,
                                                repre_celular,
												CONCAT_WS(' ',alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) as ALUMNO
                                            FROM sujeto_alumno A
                                            inner join general_sede on sede_id = alumno_sedeid
                                            LEFT JOIN (
                                                SELECT 
                                                pago_alumnoid, alumno_repreid as REPRESALDOS,
                                                MAX(pago_fecha) AS FECHASALDOS,
                                                COUNT(pago_saldo) AS TOTAL, 
                                                SUM(pago_saldo) AS SALDO
                                                FROM alumno_pago
                                                    INNER JOIN sujeto_alumno ON alumno_id = pago_alumnoid
                                                WHERE pago_rubroid = 'RPE' AND pago_estado = 'P' AND pago_saldo > 0
                                                GROUP BY pago_alumnoid, alumno_repreid
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
                                                WHERE pago_rubroid = 'RPE' AND alumno_estado <> 'I' 
                                                GROUP BY 
                                                    pago_alumnoid
                                                ) BASE
                                            ) PEN ON PEN.pago_alumnoid = A.alumno_id
                                            inner join alumno_representante R on R.repre_id = A.alumno_repreid
                                            left join(					
                                                select pago_alumnoid, transaccion_pagoid, transaccion_estado, max(transaccion_fecha) FECHATRX
                                                    from alumno_pago_transaccion
                                                    inner join alumno_pago on pago_id = transaccion_pagoid
                                                    where transaccion_estado = 'C'
                                                        and pago_estado = 'P'
                                                    group by pago_alumnoid, transaccion_pagoid, transaccion_estado						
                                            ) FECHATRX on FECHATRX.pago_alumnoid = A.alumno_id
                                            WHERE (PEN.TOTAL > 0 OR P.SALDO > 0) 
      											  AND A.alumno_estado = 'I'  -- CONDICIÓN PARA ALUMNOS ACTIVOS
                                        ) as VALORESMORA			
                                    group by alumno_repreid, repre_identificacion, REPRE, sede_nombre, repre_celular, ALUMNO";
				
			$datos = $this->ejecutarConsulta($consulta_datos);
		
			if($datos->rowCount()>0){
				$datos = $datos->fetchAll();
			}

			foreach($datos as $rows){
                $celular = substr($rows['repre_celular'], 1);				
				$tabla.='				
					<tr>
                        <td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['repre_identificacion'].'</td>
						<td>'.$rows['REPRE'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['TOTAL_MORA'].'</td>
						<td>                            
							<a href="https://wa.me/593'.$celular.'?text=Estimado representante, Club Deportivo Jorge Guzmán le recuerda que a la presente fecha usted mantiene un saldo pendiente de pensiones, por el valor de USD $'.$rows["TOTAL_MORA"].', agradecemos su gentileza en realizar el pago correspondiente." target="_blank" class="btn float-right btn-actualizar btn-xs" style="margin-right: 5px;">Notificar</a>										
                            <a href="'.APP_URL.'cobranzaDetallePension/'.$rows['repre_id'].'/" class="btn float-right btn-ver btn-xs" style="margin-right: 5px;">Detalle</a>
						</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function uniformesValormora(){
			$tabla="";
			$consulta_datos = "SELECT alumno_repreid as repre_id, sede_nombre, repre_identificacion, REPRE, repre_celular, ALUMNO, SUM(SALDO) AS TOTAL_MORA
									FROM(
										SELECT 
												alumno_repreid, 
												repre_identificacion,
												CONCAT_WS(' ',repre_primernombre, repre_segundonombre, repre_apellidopaterno, repre_apellidomaterno) as REPRE,
												IFNULL(P.SALDO,0) AS SALDO, 
												P.FECHASALDOS,
												FECHATRX.FECHATRX,
												sede_nombre,
												repre_celular,
												CONCAT_WS(' ',alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) as ALUMNO
											FROM sujeto_alumno A
											inner join general_sede on sede_id = alumno_sedeid
											LEFT JOIN (
												SELECT 
												pago_alumnoid, alumno_repreid as REPRESALDOS,
												MAX(pago_fecha) AS FECHASALDOS,
												COUNT(pago_saldo) AS TOTAL, 
												SUM(pago_saldo) AS SALDO
												FROM alumno_pago
													INNER JOIN sujeto_alumno ON alumno_id = pago_alumnoid
												WHERE pago_rubroid = 'RNU' AND pago_estado = 'P' AND pago_saldo > 0
												GROUP BY pago_alumnoid, alumno_repreid
											) P ON P.pago_alumnoid = A.alumno_id                                      
											inner join alumno_representante R on R.repre_id = A.alumno_repreid
											left join(					
												select pago_alumnoid, transaccion_pagoid, transaccion_estado, max(transaccion_fecha) FECHATRX
													from alumno_pago_transaccion
													inner join alumno_pago on pago_id = transaccion_pagoid
													where transaccion_estado = 'C'
														and pago_estado = 'P'
															group by pago_alumnoid, transaccion_pagoid, transaccion_estado						
													) FECHATRX on FECHATRX.pago_alumnoid = A.alumno_id
													WHERE P.SALDO > 0
												) as VALORESMORA			
											group by alumno_repreid, repre_identificacion, REPRE, sede_nombre, repre_celular, ALUMNO";
				
			$datos = $this->ejecutarConsulta($consulta_datos);
		
			if($datos->rowCount()>0){
				$datos = $datos->fetchAll();
			}

			foreach($datos as $rows){
                $celular = substr($rows['repre_celular'], 1);				
				$tabla.='				
					<tr>
                        <td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['repre_identificacion'].'</td>
						<td>'.$rows['REPRE'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['TOTAL_MORA'].'</td>
						<td>                            
							<a href="https://wa.me/593'.$celular.'?text=Estimado representante, Club Deportivo Jorge Guzmán le recuerda que a la presente fecha usted mantiene un saldo pendiente de uniformes, por el valor de USD $'.$rows["TOTAL_MORA"].', agradecemos su gentileza en realizar el pago correspondiente." target="_blank" class="btn float-right btn-actualizar btn-xs" style="margin-right: 5px;">Notificar</a>
                            <a href="'.APP_URL.'cobranzaDetalleUniforme/'.$rows['repre_id'].'/" class="btn float-right btn-ver btn-xs" style="margin-right: 5px;">Detalle</a>
						</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function pensionesPendientes($repre_id){		
			$tabla="";
			$NUM_SALDO = 0;
			$SALDO = 0;
			$NUM_PENSION = 0;
			$PENSION = 0;
			$consulta_datos="SELECT 
									alumno_id, 
									alumno_identificacion,
                                    sede_nombre, 
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
									WHERE pago_rubroid = 'RPE' AND pago_estado = 'P' AND pago_saldo > 0 AND alumno_repreid = ".$repre_id." 
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
									WHERE pago_rubroid = 'RPE' AND alumno_estado <> 'I' AND alumno_repreid = ".$repre_id." 
									GROUP BY 
										pago_alumnoid
									) BASE
								) PEN ON PEN.pago_alumnoid = A.alumno_id
                                 inner join general_sede on alumno_sedeid = sede_id
								WHERE PEN.TOTAL > 0 OR P.SALDO > 0
								ORDER BY PEN.PENSIONES DESC";
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){	
				$NUM_SALDO += $rows['NUM_SALDO'];
				$SALDO += $rows['SALDO'];
				$NUM_PENSION += $rows['NUM_PENSION'];
				$PENSION += $rows['PENSION'];

				$tabla.='
					<tr data-widget="expandable-table" aria-expanded="false">
                        <td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['NOMBRES'].'</td>
						<td>'.$rows['NUM_SALDO'].'</td>
						<td>'.$rows['SALDO'].'</td>
						<td>'.$rows['NUM_PENSION'].'</td>
						<td>'.$rows['PENSION'].'</td>						
					</tr>';							
			}

			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td colspan="3">SUB TOTAL</td>					
					<td>'.$NUM_SALDO.'</td>
					<td>'.number_format($SALDO, 2, '.',',').'</td>
					<td>'.$NUM_PENSION.'</td>
					<td>'.number_format($PENSION, 2, '.',',').'</td>						
				</tr>';	

			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td colspan="5">TOTAL</td>				
					<td>'.$NUM_PENSION + $NUM_SALDO.'</td>
					<td>'.number_format($PENSION + $SALDO, 2, '.',',').'</td>						
				</tr>';
				
			return $tabla;
		}
		
		public function uniformesPendientes($repre_id){		
			$tabla="";
			$NUM_SALDO = 0;
			$SALDO = 0;
			$NUM_PENSION = 0;
			$PENSION = 0;
			$consulta_datos="SELECT 
									alumno_id, 
									alumno_identificacion,
                                    sede_nombre, 
									CONCAT_WS(' ', alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) AS NOMBRES,  
									IFNULL(P.TOTAL,0) AS NUM_SALDO, 
									IFNULL(P.SALDO,0) AS SALDO
								FROM sujeto_alumno A
								LEFT JOIN (
									SELECT 
									pago_alumnoid, 
									COUNT(pago_saldo) AS TOTAL, 
									SUM(pago_saldo) AS SALDO
									FROM alumno_pago
										INNER JOIN sujeto_alumno ON alumno_id = pago_alumnoid
									WHERE pago_rubroid = 'RNU' AND pago_estado = 'P' AND pago_saldo > 0 AND alumno_repreid = ".$repre_id." 
									GROUP BY pago_alumnoid
								) P ON P.pago_alumnoid = A.alumno_id
                                 inner join general_sede on alumno_sedeid = sede_id
								WHERE P.SALDO > 0";
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){	
				$NUM_SALDO += $rows['NUM_SALDO'];
				$SALDO += $rows['SALDO'];

				$tabla.='
					<tr data-widget="expandable-table" aria-expanded="false">
                        <td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['NOMBRES'].'</td>
						<td>'.$rows['NUM_SALDO'].'</td>
						<td>'.$rows['SALDO'].'</td>					
					</tr>';							
			}

			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td colspan="3">SUB TOTAL</td>					
					<td>'.$NUM_SALDO.'</td>
					<td>'.number_format($SALDO, 2, '.',',').'</td>					
				</tr>';	

			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td colspan="3">TOTAL</td>				
					<td>'.$NUM_SALDO.'</td>
					<td>'.number_format($SALDO, 2, '.',',').'</td>						
				</tr>';
				
			return $tabla;
		}
    }
		
