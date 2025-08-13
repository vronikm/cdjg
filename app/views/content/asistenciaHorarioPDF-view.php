<?php	

    use app\controllers\asistenciaController;

    include 'app/lib/barcode.php';
    include 'app/lib/fpdf.php';

    $generator = new barcode_generator();
    $symbology="qr";
    $optionsQR=array('sx'=>4,'sy'=>4,'p'=>-12);
    
    $insHorario = new asistenciaController();	
	$horario_id = ($url[1] != "") ? $insHorario->limpiarCadena($url[1]) : 0;
    $filename = "app/views/dist/img/temp/".$horario_id.".jpeg";

	$datoshorario=$insHorario->seleccionarDatos("Unico","asistencia_horario","horario_id",$horario_id);
	if($datoshorario->rowCount()==1){
		$datoshorario=$datoshorario->fetch();
		$lugar_sedeid 		= $datoshorario['horario_sedeid'];
		$horario_nombre 	= $datoshorario['horario_nombre'];
		$horario_detalle	= $datoshorario['horario_detalle'];
		$horario_estado		= $datoshorario['horario_estado'];
         
	}else{
		$lugar_sedeid = isset($_POST['horario_sedeid']) ? $insHorario->limpiarCadena($_POST['horario_sedeid']) : 0;
		$horario_nombre 	= "";
		$horario_detalle	= "";
		$horario_estado		= "";
	}

	$sede=$insHorario->informacionSede($lugar_sedeid);
	if($sede->rowCount()==1){
		$sede=$sede->fetch(); 

        if ($sede['sede_foto']!=""){
            $sede_foto = APP_URL.'app/views/imagenes/fotos/sedes/'.$sede['sede_foto'];
        }else{
            $sede_foto = APP_URL.'app/views/imagenes/fotos/sedes/default_sede.jpg';
        }
    }

    $HorarioPDF = $insHorario->HorarioPDF($horario_id);
    if($HorarioPDF->rowCount()==1){
	    $HorarioPDF=$HorarioPDF->fetch(); 
    }

    $CanchaPDF = $insHorario->CanchaPDF($horario_id);
    if($CanchaPDF->rowCount()==1){
	    $CanchaPDF=$CanchaPDF->fetch(); 
    }

    $ProfesorPDF = $insHorario->ProfesorPDF($horario_id);
    if($ProfesorPDF->rowCount()==1){
	    $ProfesorPDF=$ProfesorPDF->fetch(); 
    }
   											
    $data="Horario ".$horario_nombre. " | "."\nIDV Loja\n".$sede["sede_telefono"]."\n".$sede["sede_email"];

    $image = $generator->render_image($symbology, $data, $optionsQR);
    imagejpeg($image, $filename);
    imagedestroy($image);

    //$pdf = new FPDF( 'P', 'mm', 'A4' );	
    $pdf = new FPDF('L', 'mm', array(130,210));

    // on sup les 2 cm en bas
    $pdf->SetAutoPagebreak(False);
    $pdf->SetMargins(0,0,0);	    
 	   
    $pdf->AddPage();

    // logo : 80 de largo por 55 de alto
    //,,ancho,

    $pdf->Image($sede_foto, 34, 10, 44, 36);
    //$pdf->Image(APP_URL.'app/views/dist/img/Logos/login.jpg', 165, 88, 25, 25);

    $pdf->SetLineWidth(0.1); $pdf->Rect(10, 10, 190, 45, "D"); $x=15; $y=13;
  
    $pdf->SetXY( $x, $y ); $pdf->SetFont( "Arial", "B", 11 ); $pdf->Cell( 270, 15, mb_convert_encoding("ACADEMIA DE FÚTBOL PEDRO LARREA", 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); $y+=5;

    $pdf->SetXY( $x+8, $y+10); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell(16, 35, mb_convert_encoding("Dirección:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    $pdf->SetXY( $x+9, $y+10); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell( 135, 35, mb_convert_encoding($sede["sede_direccion"], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); $y+=5;
    $pdf->SetXY( $x, $y+10); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell(35, 35, mb_convert_encoding("Celular:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    $pdf->SetXY( $x-12, $y+10); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell( 90, 35, mb_convert_encoding($sede["sede_telefono"], 'ISO-8859-1', 'UTF-8'), 0, 0,'C');

    $pdf->SetLineWidth(0.1); $pdf->Rect(120, 30, 60, 10, "D");
    $pdf->Line(120, 35, 180, 35);
    $pdf->SetXY( 120, 32.5); $pdf->SetFont( "Arial", "", 7 ); $pdf->Cell( 19, -10, mb_convert_encoding("Fecha ", 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    $pdf->SetXY( 120, 32.5); $pdf->SetFont( "Arial", "", 5 ); $pdf->Cell( 20, 1, "DIA", 0, 0, 'C');
    $pdf->SetXY( 140, 32.5); $pdf->SetFont( "Arial", "", 5 ); $pdf->Cell( 20, 1, "MES", 0, 0, 'C');
    $pdf->SetXY( 160, 32.5); $pdf->SetFont( "Arial", "", 5 ); $pdf->Cell( 20, 1, mb_convert_encoding("AÑO", 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    //FECHA VARIABLE
    $pdf->SetXY( 120, 38); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell( 20, -1, date('d', strtotime(date('Y-m-d'))), 0, 0, 'C');
    $pdf->SetXY( 140, 38); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell( 20, -1,date('m', strtotime(date('Y-m-d'))), 0, 0, 'C');
    $pdf->SetXY( 160, 38); $pdf->SetFont( "Arial", "", 9 ); $pdf->Cell( 20, -1, date('Y', strtotime(date('Y-m-d'))), 0, 0, 'C');
  
    $pdf->Line(140, 30, 140, 40); //Lineas verticales de la fecha
    $pdf->Line(160, 30, 160, 40);

    $pdf->SetLineWidth(0.1); $pdf->Rect(10, 56, 190, 70, "D");
    //margen, alto izquierdo de separación de línea, ancho de la fila de lado a lado, alto derecho de separación de línea

    $pdf->Line(10, 65, 200, 65);
    $pdf->Line(10, 72, 200, 72);
    $pdf->Line(10, 79, 200, 79);
    $pdf->Line(10, 88, 200, 88);
    $pdf->Line(10, 98, 200, 98);   

    $pdf->SetXY( 15, 55 ); $pdf->SetFont( "Arial", "B", 11 ); $pdf->Cell( 20, 12, mb_convert_encoding("Horario ".$horario_nombre.". ".$horario_detalle, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $C = 27;
    $CC = 35;
    $pdf->Line($C, 65, $C, 98); $C+=$CC;
    $pdf->Line($C, 65, $C, 98); $C+=$CC;
    $pdf->Line($C, 65, $C, 98); $C+=$CC;
    $pdf->Line($C, 65, $C, 98); $C+=$CC;
    $pdf->Line($C, 65, $C, 98);

    $pdf->SetXY( 38, 70 ); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "LUNES", 0, 0, 'L');    
    $pdf->SetXY( 70, 70 ); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "MARTES", 0, 0, 'L');
    $pdf->SetXY( 103,70 ); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "MIERCOLES", 0, 0, 'L');
    $pdf->SetXY( 140, 70 ); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "JUEVES", 0, 0, 'L');
    $pdf->SetXY( 175, 70 ); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "VIERNES", 0, 0, 'L');

    $pdf->SetXY( 10, 76); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "Horario", 0, 0, 'L');
    $pdf->SetXY( 10, 83); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "Cancha", 0, 0, 'L');
    $pdf->SetXY( 10, 92); $pdf->SetFont( "Arial", "B", 10 ); $pdf->Cell( 20, 0, "Profesor", 0, 0, 'L');

    $pdf->SetXY( 30, 76 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, $HorarioPDF['Lunes'], 0, 0, 'L');
    $pdf->SetXY( 65, 76 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0,$HorarioPDF['Martes'], 0, 0, 'L');
    $pdf->SetXY( 101, 76 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, $HorarioPDF['Miercoles'], 0, 0, 'L');
    $pdf->SetXY( 136, 76 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, $HorarioPDF['Jueves'], 0, 0, 'L');
    $pdf->SetXY( 170, 76 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, $HorarioPDF['Viernes'], 0, 0, 'L');

    $pdf->SetXY( 27, 83); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($CanchaPDF['Lunes']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 62, 83); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($CanchaPDF['Martes']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 97, 83); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($CanchaPDF['Miercoles']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 132, 83); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($CanchaPDF['Jueves']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 167, 83); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($CanchaPDF['Viernes']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

    $pdf->SetXY( 27, 93 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($ProfesorPDF['Lunes']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 62, 93 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($ProfesorPDF['Martes']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 97, 93 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($ProfesorPDF['Miercoles']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 132, 93 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($ProfesorPDF['Jueves']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    $pdf->SetXY( 167, 93 ); $pdf->SetFont( "Arial", "", 8 ); $pdf->Cell( 20, 0, mb_convert_encoding($ProfesorPDF['Viernes']?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');


    $pdf->Image(APP_URL.$filename, 171, 100, 23, 23);
      
    unlink($filename);
   
    $pdf->Output("$horario_id.pdf","I","T");
    
  
