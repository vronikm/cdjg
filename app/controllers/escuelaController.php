<?php
	namespace app\controllers;
	use app\models\mainModel;

	class escuelaController extends mainModel{

		/*----------  Controlador registrar escuela  ----------*/
		public function registrarEscuelaControlador(){

			# Almacenando datos#
			$escuela_ruc			= $this->limpiarCadena($_POST['escuela_ruc']);
			$escuela_nombre			= $this->limpiarCadena($_POST['escuela_nombre']);
		    $escuela_direccion		= $this->limpiarCadena($_POST['escuela_direccion']);		    		    
		    $escuela_email			= $this->limpiarCadena($_POST['escuela_email']);
			$escuela_telefono			= $this->limpiarCadena($_POST['escuela_telefono']);
		    $escuela_movil			= $this->limpiarCadena($_POST['escuela_movil']);
			$escuela_recibo			= $this->limpiarCadena($_POST['escuela_recibo']);
			$escuela_pension		= $this->limpiarCadena($_POST['escuela_pension']);
			$escuela_inscripcion	= $this->limpiarCadena($_POST['escuela_inscripcion']);

		    # Verificando campos obligatorios #
		    if($escuela_ruc=="" || $escuela_nombre=="" || $escuela_direccion=="" || $escuela_email=="" || 
				$escuela_telefono=="" || $escuela_movil==""|| $escuela_recibo==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No ha completado los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }

		    # Verificando integridad de los datos #
		    if($this->verificarDatos("[0-9]{13,13}",$escuela_ruc)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"El RUC no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }
			$check_ruc=$this->ejecutarConsulta("SELECT escuela_ruc FROM general_escuela WHERE escuela_ruc='$escuela_ruc'");
			if($check_ruc->rowCount()>0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"El RUC que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
					"icono"=>"error"
				];
				return json_encode($alerta);				
			}			
		    
			# Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$escuela_nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"El Nombre no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }

		    # Verificando email #
		    if($escuela_email!=""){
				if(filter_var($escuela_email, FILTER_VALIDATE_EMAIL)){
					$check_email=$this->ejecutarConsulta("SELECT escuela_email FROM general_escuela WHERE escuela_email='$escuela_email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error",
							"texto"=>"El Email que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono"=>"error"
						];
						return json_encode($alerta);
						//exit();
					}
					}else{
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error",
							"texto"=>"Ha ingresado un correo electrónico no válido",
							"icono"=>"error"
						];
						return json_encode($alerta);
						//exit();
				}
            }

		    # Directorio de imagenes #
    		$img_dir="../views/dist/img/Logos/";

    		# Comprobar si se selecciono una imagen #
    		if($_FILES['escuela_logo']['name']!="" && $_FILES['escuela_logo']['size']>0){

    			# Creando directorio #
		        if(!file_exists($img_dir)){
		            if(!mkdir($img_dir,0777)){
		            	$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error, por favor contactarse con el administrador",
							"texto"=>"Error al crear el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
		                //exit();
		            } 
		        }

		        # Verificando formato de imagenes #
		        if(mime_content_type($_FILES['escuela_logo']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['escuela_logo']['tmp_name'])!="image/png"){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		            //exit();
		        }

		        # Verificando peso de imagen #
		        if(($_FILES['escuela_logo']['size']/1024)>5120){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		            //exit();
		        }

		        # Nombre de la foto #
		        $logo=str_ireplace(" ","_",$escuela_ruc);
		        $logo=$logo."_".rand(0,100);

		        # Extension de la imagen #
		        switch(mime_content_type($_FILES['escuela_logo']['tmp_name'])){
		            case 'image/jpeg':
		                $logo=$logo.".jpg";
		            break;
		            case 'image/png':
		                $logo=$logo.".png";
		            break;
		        }

		        chmod($img_dir,0777);

		        # Moviendo imagen al directorio #
		        if(!move_uploaded_file($_FILES['escuela_logo']['tmp_name'],$img_dir.$logo)){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error, por favor contactarse con el administrador",
						"texto"=>"No podemos subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
		            //exit();
		        }

    		}else{
    			$logo="";
    		}


		    $escuela_datos_reg=[
				[
					"campo_nombre"=>"escuela_id",
					"campo_marcador"=>":Id",
					"campo_valor"=>1
				],
				[
					"campo_nombre"=>"escuela_ruc",
					"campo_marcador"=>":RUC",
					"campo_valor"=>$escuela_ruc
				],
				[
					"campo_nombre"=>"escuela_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$escuela_nombre
				],	
				[
					"campo_nombre"=>"escuela_direccion",
					"campo_marcador"=>":Direccion",
					"campo_valor"=>$escuela_direccion
				],			
				[
					"campo_nombre"=>"escuela_email",
					"campo_marcador"=>":Correo",
					"campo_valor"=>$escuela_email
				],				
				[
					"campo_nombre"=>"escuela_telefono",
					"campo_marcador"=>":Telefono",
					"campo_valor"=>$escuela_telefono
				],
				[
					"campo_nombre"=>"escuela_movil",
					"campo_marcador"=>":Celular",
					"campo_valor"=>$escuela_movil
				],
				[
					"campo_nombre"=>"escuela_logo",
					"campo_marcador"=>":Logo",
					"campo_valor"=>$logo
				],
				[
					"campo_nombre"=>"escuela_recibo",
					"campo_marcador"=>":Recibo",
					"campo_valor"=>$escuela_recibo
				],
				[
					"campo_nombre"=>"escuela_pension",
					"campo_marcador"=>":Pension",
					"campo_valor"=>$escuela_pension
				],
				[
					"campo_nombre"=>"escuela_inscripcion",
					"campo_marcador"=>":Inscripcion",
					"campo_valor"=>$escuela_inscripcion
				]
			];

			$registrar_escuela=$this->guardarDatos("general_escuela",$escuela_datos_reg);

			if($registrar_escuela->rowCount()>0){
				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Escuela registrada",
					"texto"=>"La ".$escuela_nombre." se registró correctamente",
					"icono"=>"success"
				];
			}else{
				
				if(is_file($img_dir.$logo)){
		            chmod($img_dir.$logo,0777);
		            unlink($img_dir.$logo);
		        }

				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No se pudo registrar la escuela, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);

		}

		/*----------  Controlador eliminar escuela  ----------*/
		public function eliminarEscuelaControlador(){

			$id=$this->limpiarCadena($_POST['escuela_id']);

			if($id==1){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No es posible eliminar la escuela principal del sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
			}

			# Verificando escuela #
		    $datos=$this->ejecutarConsulta("SELECT * FROM general_escuela WHERE escuela_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No se encuentra la escuela en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    $eliminarEscuela=$this->eliminarRegistro("escuela","escuela_id",$id);

		    if($eliminarEscuela->rowCount()==1){

		    	if(is_file("../views/fotos/".$datos['escuela_logo'])){
		            chmod("../views/fotos/".$datos['escuela_logo'],0777);
		            unlink("../views/fotos/".$datos['escuela_logo']);
		        }

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Escuela eliminada",
					"texto"=>"La ".$datos['escuela_nombre']." ha sido eliminada del sistema correctamente",
					"icono"=>"success"
				];

		    }else{

		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No fue posible eliminar la escuela ".$datos['escuela_nombre']." del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}

		/*----------  Controlador actualizar escuela  ----------*/
		public function actualizarEscuelaControlador(){

			$escuela_id=1;

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM general_escuela WHERE escuela_id='$escuela_id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"No existe una escuela creada, por favor proceda con la creación a continuación",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

			# Almacenando datos#
		    $escuela_ruc=$this->limpiarCadena($_POST['escuela_ruc']);
		    $escuela_nombre=$this->limpiarCadena($_POST['escuela_nombre']);
			$escuela_direccion=$this->limpiarCadena($_POST['escuela_direccion']);
			$escuela_email=$this->limpiarCadena($_POST['escuela_email']);
			$escuela_telefono=$this->limpiarCadena($_POST['escuela_telefono']);
			$escuela_movil=$this->limpiarCadena($_POST['escuela_movil']);
			$escuela_recibo=$this->limpiarCadena($_POST['escuela_recibo']);
			$escuela_pension=$this->limpiarCadena($_POST['escuela_pension']);
			$escuela_inscripcion=$this->limpiarCadena($_POST['escuela_inscripcion']);

		    # Verificando campos obligatorios #
		    if($escuela_ruc=="" || $escuela_nombre=="" || $escuela_direccion=="" || $escuela_email=="" 
				|| $escuela_movil=="" || $escuela_recibo==""){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No ha completado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }

		    # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$escuela_nombre)){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"El Nombre no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		       // exit();
		    }

		    # Verificando email #
		    if($escuela_email!="" && $datos['escuela_email']!=$escuela_email){
				if(filter_var($escuela_email, FILTER_VALIDATE_EMAIL)){
					$check_email=$this->ejecutarConsulta("SELECT escuela_email FROM general_escuela WHERE escuela_email='$escuela_email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error",
							"texto"=>"El email que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono"=>"error"
						];
						return json_encode($alerta);
						//exit();
					}
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"Ha ingresado un correo electrónico no válido",
						"icono"=>"error"
					];
					return json_encode($alerta);
					//exit();
				}
            }


		 	# Directorio de imagenes #
    		$img_dir="../views/dist/img/Logos/";

    		# Comprobar si se selecciono una imagen #
			if($_FILES['escuela_logo']['name']=="" && $_FILES['escuela_logo']['size']<=0){
				$escuela_datos_up=[
					[
						"campo_nombre"=>"escuela_ruc",
						"campo_marcador"=>":RUC",
						"campo_valor"=>$escuela_ruc
					],
					[
						"campo_nombre"=>"escuela_nombre",
						"campo_marcador"=>":Nombre",
						"campo_valor"=>$escuela_nombre
					],
					[
						"campo_nombre"=>"escuela_email",
						"campo_marcador"=>":Correo",
						"campo_valor"=>$escuela_email
					],
					[
						"campo_nombre"=>"escuela_direccion",
						"campo_marcador"=>":Direccion",
						"campo_valor"=>$escuela_direccion
					],
					[
						"campo_nombre"=>"escuela_telefono",
						"campo_marcador"=>":Telefono",
						"campo_valor"=>$escuela_telefono
					],
					[
						"campo_nombre"=>"escuela_movil",
						"campo_marcador"=>":Celular",
						"campo_valor"=>$escuela_movil
					],
					[
						"campo_nombre"=>"escuela_recibo",
						"campo_marcador"=>":Recibo",
						"campo_valor"=>$escuela_recibo
					],
					[
						"campo_nombre"=>"escuela_pension",
						"campo_marcador"=>":Pension",
						"campo_valor"=>$escuela_pension
					],
					[
						"campo_nombre"=>"escuela_inscripcion",
						"campo_marcador"=>":Inscripcion",
						"campo_valor"=>$escuela_inscripcion
					]
				];
    		}ELSE{
				# Creando directorio #
				if(!file_exists($img_dir)){
					if(!mkdir($img_dir,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error, por favor contactarse con el administrador",
							"texto"=>"Error al crear el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
						//exit();
					} 
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['escuela_logo']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['escuela_logo']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
					//exit();
				}
		

				# Verificando peso de imagen #
				if(($_FILES['escuela_logo']['size']/1024)>5120){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
					//exit();
				}

				# Nombre de la foto #
				$foto=str_ireplace(" ","_",$datos['escuela_ruc']);
				$foto=$foto."_".rand(0,100);

				# Extension de la imagen #
				switch(mime_content_type($_FILES['escuela_logo']['tmp_name'])){
					case 'image/jpeg':
						$foto=$foto.".jpg";
					break;
					case 'image/png':
						$foto=$foto.".png";
					break;
				}

				chmod($img_dir,0777);

				# Moviendo imagen al directorio #
				if(!move_uploaded_file($_FILES['escuela_logo']['tmp_name'],$img_dir.$foto)){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error, por favor contactarse con el administrador",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
					//exit();
				}

				# Eliminando imagen anterior #
				if(is_file($img_dir.$datos['escuela_logo']) && $datos['escuela_logo']!=$foto){
					chmod($img_dir.$datos['escuela_logo'], 0777);
					unlink($img_dir.$datos['escuela_logo']);
				}			

            	$escuela_datos_up=[
					[
						"campo_nombre"=>"escuela_ruc",
						"campo_marcador"=>":RUC",
						"campo_valor"=>$escuela_ruc
					],
					[
						"campo_nombre"=>"escuela_nombre",
						"campo_marcador"=>":Nombre",
						"campo_valor"=>$escuela_nombre
					],
					[
						"campo_nombre"=>"escuela_email",
						"campo_marcador"=>":Correo",
						"campo_valor"=>$escuela_email
					],
					[
						"campo_nombre"=>"escuela_direccion",
						"campo_marcador"=>":Direccion",
						"campo_valor"=>$escuela_direccion
					],
					[
						"campo_nombre"=>"escuela_telefono",
						"campo_marcador"=>":Telefono",
						"campo_valor"=>$escuela_telefono
					],
					[
						"campo_nombre"=>"escuela_movil",
						"campo_marcador"=>":Celular",
						"campo_valor"=>$escuela_movil
					],
					[
						"campo_nombre"=>"escuela_logo",
						"campo_marcador"=>":Logo",
						"campo_valor"=>$foto
					],
					[
						"campo_nombre"=>"escuela_recibo",
						"campo_marcador"=>":Recibo",
						"campo_valor"=>$escuela_recibo
					],
					[
						"campo_nombre"=>"escuela_inscripcion",
						"campo_marcador"=>":Inscripcion",
						"campo_valor"=>$escuela_inscripcion
					]
				];
			}
			$condicion=[
				"condicion_campo"=>"escuela_id",
				"condicion_marcador"=>":Escuela",
				"condicion_valor"=>$escuela_id
			];

			if($this->actualizarDatos("general_escuela",$escuela_datos_up,$condicion)){

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Escuela actualizada",
					"texto"=>"Los datos de la ".$datos['escuela_nombre']." se actualizaron correctamente",
					"icono"=>"success"
					];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No fue posible actualizar los datos de la ".$datos['escuela_nombre'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Controlador registrar sede  ----------*/
		public function registrarSedeControlador(){

			# Almacenando datos#
			$sede_nombre		= $this->limpiarCadena($_POST['sede_nombre']);
			$sede_direccion		= $this->limpiarCadena($_POST['sede_direccion']);		    		    
			$sede_email			= $this->limpiarCadena($_POST['sede_email']);
			$sede_telefono		= $this->limpiarCadena($_POST['sede_telefono']);
			$sede_inscripcion	= $this->limpiarCadena($_POST['sede_inscripcion']);
			$sede_pension		= $this->limpiarCadena($_POST['sede_pension']);
			
			# Verificando campos obligatorios #
			if($sede_nombre=="" || $sede_direccion=="" || $sede_email=="" || $sede_telefono==""){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No ha completado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			# Verificando integridad de los datos #
			if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$sede_nombre)){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"El nombre de la sede no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			# Directorio de fotos #
			$img_dir="../views/imagenes/fotos/sedes/";
			$codigo=rand(0,100);

			# Comprobar si se selecciono una imagen #
			if($_FILES['sede_foto']['name']!="" && $_FILES['sede_foto']['size']>0){

				# Creando directorio #
				if(!file_exists($img_dir)){
					if(!mkdir($img_dir,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error",
							"texto"=>"No fue posible crear el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
					} 
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['sede_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['sede_foto']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Verificando peso de imagen #
				if(($_FILES['sede_foto']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Nombre de la foto #
				$foto=str_ireplace(" ","_",$sede_nombre);
				$foto=$foto."_".$codigo;

				# Extension de la imagen #
				switch(mime_content_type($_FILES['sede_foto']['tmp_name'])){
					case 'image/jpeg':
						$foto=$foto.".jpg";
					break;
					case 'image/png':
						$foto=$foto.".png";
					break;
				}
				$maxWidth = 800;
				$maxHeight = 600;

				chmod($img_dir,0777);
				$inputFile = ($_FILES['sede_foto']['tmp_name']);
					$outputFile = $img_dir.$foto;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {
					
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}
			}else{
    			$foto="";
    		}

			$sede_datos_reg=[				
				[
					"campo_nombre"=>"sede_escuelaid",
					"campo_marcador"=>":Id",
					"campo_valor"=>1
				],
				[
					"campo_nombre"=>"sede_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$sede_nombre
				],	
				[
					"campo_nombre"=>"sede_direccion",
					"campo_marcador"=>":Direccion",
					"campo_valor"=>$sede_direccion
				],			
				[
					"campo_nombre"=>"sede_email",
					"campo_marcador"=>":Correo",
					"campo_valor"=>$sede_email
				],				
				[
					"campo_nombre"=>"sede_telefono",
					"campo_marcador"=>":Telefono",
					"campo_valor"=>$sede_telefono
				],				
				[
					"campo_nombre"=>"sede_inscripcion",
					"campo_marcador"=>":Inscripcion",
					"campo_valor"=>$sede_inscripcion
				],				
				[
					"campo_nombre"=>"sede_pension",
					"campo_marcador"=>":Pension",
					"campo_valor"=>$sede_pension
				],				
				[
					"campo_nombre"=>"sede_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>$foto
				]
			];

			$registrar_sede=$this->guardarDatos("general_sede",$sede_datos_reg);

			if($registrar_sede->rowCount()==1){
				$alerta=[
					"tipo"=>"redireccionar",
					"url"=>APP_URL.'sedeList/',
					"titulo"=>"Sede registrada",
					"texto"=>"La sede ".$sede_nombre." se registró correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No fue posible registrar la sede, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);

		}

		/*----------  Controlador obtener id escuela  ----------*/
		public function ObtenerEscuelaControlador(){
			$option="";

			$consulta_datos="SELECT max(escuela_id) escuelaId FROM general_escuela";	
					
			$datos = $this->ejecutarConsulta($consulta_datos);
		
			return $datos;
		}

		/*----------  Controlador listar sede  ----------*/
		public function listarSedes(){
			$tabla="";
			$texto = "";
			$boton = "";
			$consulta_datos="SELECT sede_id, sede_nombre, sede_direccion, sede_email, sede_telefono, sede_inscripcion, sede_pension
							 FROM general_sede
							 ORDER BY sede_id DESC";
							 					
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){				
				$tabla.='
					<tr>						
						<td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['sede_direccion'].'</td>
						<td>'.$rows['sede_email'].'</td>
						<td>'.$rows['sede_telefono'].'</td>
						<td>'.$rows['sede_inscripcion'].'</td>
						<td>'.$rows['sede_pension'].'</td>
						<td>
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/escuelaAjax.php" method="POST" autocomplete="off" >
								<input type="hidden" name="modulo_sede" value="eliminar">
								<input type="hidden" name="sede_id" value="'.$rows['sede_id'].'">						
								<button type="submit" class="btn float-right btn-danger btn-xs" style="margin-right: 5px;">Eliminar</button>
							</form>
							<a href="'.APP_URL.'sedeList/'.$rows['sede_id'].'/" class="btn float-right btn-success btn-xs" style="margin-right: 5px;">Editar</a>
						</td>						
					</tr>';	
			}
			return $tabla;
		}

		/*----------  Controlador ver sede  ----------*/
		public function verSedeControlador($sedeid){
			# Verificando sede #
		    $datos=$this->ejecutarConsulta("SELECT * FROM general_sede WHERE sede_id='$sedeid'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No se encuentra la sede en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	return $datos;
		    }
		}
				/*----------  Controlador actualizar escuela  ----------*/
		public function actualizarSedeControlador(){

			$sede=$this->limpiarCadena($_POST['sede_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM general_sede WHERE sede_id ='$sede'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se encuentra la sede en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#			
			$sede_nombre		= $this->limpiarCadena($_POST['sede_nombre']);
			$sede_direccion		= $this->limpiarCadena($_POST['sede_direccion']);		    		    
			$sede_email			= $this->limpiarCadena($_POST['sede_email']);
			$sede_telefono		= $this->limpiarCadena($_POST['sede_telefono']);
			$sede_inscripcion	= $this->limpiarCadena($_POST['sede_inscripcion']);
			$sede_pension		= $this->limpiarCadena($_POST['sede_pension']);

			# Verificando campos obligatorios #
			if($sede_nombre=="" || $sede_direccion=="" || $sede_email=="" || $sede_telefono==""){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No ha completado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			# Verificando integridad de los datos #
			if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$sede_nombre)){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"El Nombre de la sede no coincide con el formato establecido",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}
			$sede_datos_upd=[
				[
					"campo_nombre"=>"sede_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$sede_nombre
				],	
				[
					"campo_nombre"=>"sede_direccion",
					"campo_marcador"=>":Direccion",
					"campo_valor"=>$sede_direccion
				],			
				[
					"campo_nombre"=>"sede_email",
					"campo_marcador"=>":Correo",
					"campo_valor"=>$sede_email
				],				
				[
					"campo_nombre"=>"sede_telefono",
					"campo_marcador"=>":Telefono",
					"campo_valor"=>$sede_telefono
				],
				[
					"campo_nombre"=>"sede_inscripcion",
					"campo_marcador"=>":Inscripcion",
					"campo_valor"=>$sede_inscripcion
				],	
				[
					"campo_nombre"=>"sede_pension",
					"campo_marcador"=>":Pension",
					"campo_valor"=>$sede_pension
				]
			];

			# Directorio de fotos #
			$img_dir="../views/imagenes/fotos/sedes/";
			$codigo=rand(0,100);

			# Comprobar si se selecciono una imagen #
			if($_FILES['sede_foto']['name']!="" && $_FILES['sede_foto']['size']>0){

				# Creando directorio #
				if(!file_exists($img_dir)){
					if(!mkdir($img_dir,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error",
							"texto"=>"No fue posible crear el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
					} 
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['sede_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['sede_foto']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Verificando peso de imagen #
				if(($_FILES['sede_foto']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Nombre de la foto #
				$foto=str_ireplace(" ","_",$sede_nombre);
				$foto=$foto."_".$codigo;

				# Extension de la imagen #
				switch(mime_content_type($_FILES['sede_foto']['tmp_name'])){
					case 'image/jpeg':
						$foto=$foto.".jpg";
					break;
					case 'image/png':
						$foto=$foto.".png";
					break;
				}
				$maxWidth = 800;
				$maxHeight = 600;

				chmod($img_dir,0777);
				$inputFile = ($_FILES['sede_foto']['tmp_name']);
					$outputFile = $img_dir.$foto;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {
					
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}
				# Eliminando imagen anterior #
				if(is_file($img_dir.$datos['sede_foto']) && $datos['sede_foto']!=$foto){
					chmod($img_dir.$datos['sede_foto'], 0777);
					unlink($img_dir.$datos['sede_foto']);
				}				
				
				$sede_datos_upd[] = [
					"campo_nombre" => "sede_foto",
					"campo_marcador" => ":Foto",
					"campo_valor" => $foto
				];				
			}			
			
			$condicion=[
				"condicion_campo"=>"sede_id",
				"condicion_marcador"=>":Sede",
				"condicion_valor"=>$sede
			];

			if($this->actualizarDatos("general_sede",$sede_datos_upd,$condicion)){

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Sede actualizada",
					"texto"=>"Los datos de la sede ".$datos['sede_nombre']." se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No fue posible actualizar los datos de la sede ".$datos['sede_nombre'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

		
		/*----------  Controlador eliminar usuario  ----------*/
		public function eliminarSedeControlador(){

			$sede=$this->limpiarCadena($_POST['sede_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM general_sede WHERE sede_id ='$sede'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No se encuentra la sede en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    $eliminarSede=$this->eliminarRegistro("general_sede","sede_id",$sede);

		    if($eliminarSede->rowCount()==1){
		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Sede eliminada",
					"texto"=>"La sede ".$datos['sede_nombre']." ha sido eliminada del sistema correctamente",
					"icono"=>"success"
				];
		    }else{

		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No fue posible eliminar la sede ".$datos['sede_nombre']." del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }
		    return json_encode($alerta);
		}
	}