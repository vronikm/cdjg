<?php	
	namespace app\models;

	class viewsModel{
		/*---------- Modelo obtener vista ----------*/
		protected function obtenerVistasModelo($vista){
			$listaBlanca=["dashboard","userNew","userList","userUpdate","userSearch","userPhoto"
						  ,"logOut","roList","escuelaNew","alumnoList","alumnoNew","alumnoUpdate"
						  ,"pagos","userProfile","escuelaNew","sedeList","sedeNew","sedeProfile"
						  ,"sedeUpdate","pagosList","pagosNew","alumnoProfile","pagosUpdate"
						  ,"dashboard","pagosPendiente","pagospendienteUpdate","pagosRecibo"
						  ,"pagospendienteRecibo","pagosDescuento","pagosReciboPDF","pagospendienteReciboPDF"
						  ,"pagosReciboEnvio","reportePagos","reportePendientes","pagospendienteReciboEnvio"
						  ,"asistenciaHora","asistenciaLugar","asistenciaHorario","tablasNew"
						  ,"catalogosNew",'asistenciaListHorario',"representanteList", "representanteNew"
						  ,"representanteProfile","representanteUpdate","representanteVinc"
						  ,"empleadoList","torneoList","equipoList","asistenciaVerHorario","asistenciaHorarioPDF"
						  ,"jugadorLista","jugadorNew", "asistenciaHorarioJugador","cobranzaPension"
						  ,"cobranzaUniforme","cobranzaDetallePension","cobranzaDetalleUniforme"
						  ,"jugadorListaPDF", "empleadoIE", "empleadoDescargaEgreso","pagosUniformeUpdate"
						  ,"reporteRubros","reportePagosReceptadosResumen","alumnoListaPDF","ingresoList"
						  ,"reporteRepresentanteFactura","asistenciaHorarioLista","empleadoEgresoUpdate"
						  ,"asistencia","asistenciaAlumno","ingresoList","egresoList","balanceResultados"
						  ,"reporteAsistencia", "buscarAsistencia", "horarioListaPDF","representanteFLPD"
						  ,"formularioLPPDF","empleadoEntrada", "userMenu", "permisoList", "permisoNew"
						  ,"empleadoAsistencias","agenda","empleadoAsistenciasDetalle","cobranzaPensionInactivos"
						  ,"dashboardAlumnos","reportePensionAlumno","reportePagosReceptados"];

			if(in_array($vista, $listaBlanca)){
				if(is_file("./app/views/content/".$vista."-view.php")){
					$contenido="./app/views/content/".$vista."-view.php";
				}else{
					$contenido="404";
				}
			}elseif($vista=="login" || $vista=="index"){
				$contenido="login";
			}else{
				$contenido="404";
			}
			return $contenido;
		}
	}