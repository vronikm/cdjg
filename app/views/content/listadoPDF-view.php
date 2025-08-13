<?php	

    use app\controllers\listadoController;

    include 'app/lib/barcode.php';
    include 'app/lib/fpdf.php';

    $insLista    = new listadoController();

    $cantonid 	    = ($url[1] != "") ? $url[1] : 0;
	$parroquiaid 	= ($url[2] != "") ? $url[2] : 0;

    $generator = new barcode_generator();
    $symbology="qr";
    $optionsQR=array('sx'=>4,'sy'=>4,'p'=>-12);
    $filename = "app/views/dist/img/temp/";

    $partido=$insLista->informacionPartido( 1);
	if($partido->rowCount()==1){
		$partido=$partido->fetch(); 
	}

    $canton=$insLista->listarCantonPDF($cantonid,$parroquiaid);
	if($canton->rowCount()==1){
		$canton=$canton->fetch(); 
	}
 
    $pdf = new FPDF( 'L', 'mm', 'A4' );	
    // on sup les 2 cm en bas
    $pdf->SetAutoPagebreak(False);
    $pdf->SetMargins(0,0,0);	    
 	   
    $pdf->AddPage();
    $pdf->Image(APP_URL.'app/views/dist/img/Logos/logo_adn.jpeg', 15, 10, 57, 36);
    $pdf->SetLineWidth(0.1); $pdf->Rect(10, 10, 280, 35, "D"); $x=8; $y=10;  
    $pdf->SetXY( $x, $y ); $pdf->SetFont( "Arial", "B", 14 ); $pdf->Cell( 310, 10, mb_convert_encoding($partido["partido_nombre"]." - CONTROL ELECTORAL PROVINCIA DE LOJA", 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); $y+=8; 
    $pdf->SetXY( $x, $y); $pdf->SetFont( "Arial", "", 11 ); $pdf->Cell(310, 7, mb_convert_encoding("Dirección Sede Loja: ".$partido["partido_direccion"], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); $y+=6;
    $pdf->SetXY( $x, $y); $pdf->SetFont( "Arial", "", 12 ); $pdf->Cell(310, 10, mb_convert_encoding("Coordinadora General: María Torres  Celular: ".$partido["partido_telefono"], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    $pdf->SetXY( $x, $y ); $pdf->SetFont( "Arial", "B", 14 ); $pdf->Cell( 310, 23, mb_convert_encoding("COORDINADORES Y VEEDORES DEL CANTÓN ".$canton["canton_nombre"], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); $y+=3; 
    $pdf->SetXY( $x, $y ); $pdf->SetFont( "Arial", "B", 12 ); $pdf->Cell( 310, 30, mb_convert_encoding("Subcoordinador: ".$canton["usuario_nombre"], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); $y+=3; 

    $tabla="";
    $x=10; $y=60;    
    $pdf->SetMargins(5, 10, 10);
 
    // Función para crear los encabezados de la tabla
    function crearEncabezados($pdf) {
        $pdf->SetFont( "Arial", "B", 8 );
        $pdf->Ln(20);
        $pdf->Cell( 6, 8, "No.", 1, 0, 'C'); //alineación: 'L' alineación a la izquierda (predeterminado), 'C' para centrar o 'R' alineación a la derecha.
        $pdf->Cell( 35, 8, "PARROQUIA", 1, 0, 'C');
        $pdf->Cell( 50, 8, "RECINTO", 1, 0, 'C');
        $pdf->Cell( 23, 8, "TIPO", 1, 0, 'C');
        $pdf->Cell( 12, 8, "JUNTA", 1, 0, 'C');
        $pdf->Cell( 24, 8, mb_convert_encoding("IDENTIFICACIÓN", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell( 50, 8, "NOMBRES Y APELLIDOS", 1, 0, 'C'); //$pdf->Cell(ancho, alto, 'texto', borde, salto, 'alineacion');
        $pdf->Cell( 17, 8, "CELULAR", 1, 0, 'C');
        $pdf->Cell( 34, 8, "CORREO", 1, 0, 'C');
        $pdf->Cell( 36, 8, "FIRMA", 1, 0, 'C');
        $pdf->Ln(2);
    }

    // Crear los encabezados de la primera página
    crearEncabezados($pdf);

    $lista=$insLista->listarAfiliadosPDF($cantonid,$parroquiaid);

    if($lista->rowCount()>0){
		$lista=$lista->fetchAll(); 
        $pdf->SetFont( "Arial", "", 8);
        $lineNumber = 1; // Inicializa el contador de líneas
        foreach($lista as $rows){
            // Verificar si hay suficiente espacio en la página
            if ($pdf->GetY() > 193) { // Aproximadamente el final de la página para continuar la secuencia de líneas
                $pdf->AddPage(); // Agregar una nueva página
                $pdf->SetY(0); // Ajustar la posición inicial del contenido (disminuir margen superior)
                crearEncabezados($pdf); // Crear los encabezados en la nueva página

                // Después de los encabezados, volver a configurar la fuente normal
                $pdf->SetFont('Arial', '', 8);
            }

            $pdf->Ln(6);
            $pdf->Cell(6, 8, $lineNumber, 1, 0, 'C'); // Número de línea

            $pdf->Cell(35, 8, '', 1, 0); // Celda vacía como contenedor de MultiCell
            $x = $pdf->GetX(); 
            $y = $pdf->GetY();
            $pdf->SetXY($x - 35, $y); // Retrocede para escribir en la celda vacía
            $pdf->MultiCell(35, 4, mb_convert_encoding($rows['parroquia_nombre'], 'ISO-8859-1', 'UTF-8'), 0, 'L');
            $pdf->SetXY($x, $y); // Restablece la posición para continuar con las siguientes celdas

            $pdf->Cell(50, 8, '', 1, 0); // Celda vacía como contenedor de MultiCell
            $x = $pdf->GetX(); 
            $y = $pdf->GetY();
            $pdf->SetXY($x - 50, $y); // Retrocede para escribir en la celda vacía
            $pdf->MultiCell(50, 4, mb_convert_encoding($rows['recinto_nombre'], 'ISO-8859-1', 'UTF-8'), 0, 'L');
            $pdf->SetXY($x, $y); // Restablece la posición para continuar con las siguientes celdas
          
            //$pdf->Cell( 30, 8, mb_convert_encoding($rows['parroquia_nombre'], 'ISO-8859-1', 'UTF-8'), 1);
            //$pdf->MultiCell( 65, 8, mb_convert_encoding($rows['recinto_nombre'], 'ISO-8859-1', 'UTF-8'), 1);
            $pdf->Cell( 23, 8, mb_convert_encoding($rows['afiliado_tipo'], 'ISO-8859-1', 'UTF-8'), 1);            
            $pdf->Cell( 12, 8, $rows['afiliado_junta'], 1, 0, 'C');
            $pdf->Cell( 24, 8, $rows['afiliado_identificacion'], 1, 0, 'C');
            
            //$pdf->Cell( 65, 8, mb_convert_encoding($rows['afiliado_primernombre'].' '.$rows['afiliado_segundonombre'].' '.$rows['afiliado_apellidopaterno'].' '.$rows['afiliado_apellidomaterno'], 'ISO-8859-1', 'UTF-8'), 1);
            
            $pdf->Cell(50, 8, '', 1, 0); // Celda vacía como contenedor de MultiCell
            $x = $pdf->GetX(); 
            $y = $pdf->GetY();
            $pdf->SetXY($x - 50, $y); // Retrocede para escribir en la celda vacía
            $pdf->MultiCell(50, 4, mb_convert_encoding($rows['afiliado_primernombre'].' '.$rows['afiliado_segundonombre'].' '.$rows['afiliado_apellidopaterno'].' '.$rows['afiliado_apellidomaterno'], 'ISO-8859-1', 'UTF-8'), 0, 'L');
            $pdf->SetXY($x, $y); // Restablece la posición para continuar con las siguientes celdas
            $pdf->Cell( 17, 8, $rows['afiliado_celular'], 1, 0, 'C');  
            
            $pdf->Cell(34, 8, '', 1, 0); // Celda vacía como contenedor de MultiCell
            $x = $pdf->GetX(); 
            $y = $pdf->GetY();
            $pdf->SetXY($x - 34, $y); // Retrocede para escribir en la celda vacía 
            $pdf->MultiCell(34, 4, mb_convert_encoding($rows['afiliado_correo'], 'ISO-8859-1', 'UTF-8'), 0, 'L');
            $pdf->SetXY($x, $y); // Restablece la posición para continuar con las siguientes celdas
                    
            $pdf->Cell( 36, 8, '  ', 1, 0, 'C');
            $lineNumber++; // Incrementa el número de línea
            $pdf->Ln(2);
        }   
    }    
    // Salto de línea antes de las firmas
    $pdf->Ln(15);
   
    $pdf->Output("ADN-LOJA_CoordVeedores.pdf","I","T");

    