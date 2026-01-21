<?php
use app\controllers\carnetController;
include 'app/lib/barcode.php';
include 'app/lib/alphapdf.php';
$insCarnet = new carnetController();

// Generador de código QR
$generator = new barcode_generator();
$symbology="qr";
$optionsQR=array('sx'=>4,'sy'=>4,'p'=>-12);
$tempDir = "app/views/dist/img/temp/";

// Obtener información de la primera sede disponible (por defecto)
$sede = $insCarnet->informacionSede(1);
if($sede->rowCount() == 1) {
    $sede = $sede->fetch();
} else {
    echo "<script>
        alert('Error: No se encontró información de la escuela');
        window.close();
    </script>";
    exit;
}
// Obtener carnets del mes actual (todos, sin filtro de sede)
$carnetsData = $insCarnet->obtenerCarnetsMesActual();
if(empty($carnetsData)) {
    echo "<script>
        alert('No hay carnets para imprimir este mes');
        window.close();
    </script>";
    exit;
}
// Configuración PDF
$pdf = new AlphaPDF('P', 'mm', 'A4');
$pdf->SetAutoPagebreak(false);
$pdf->SetMargins(0, 0, 0);
// Dimensiones del carnet (tamaño tarjeta de crédito estándar)
$carnetWidth = 85.6;  // 85.6mm = ancho tarjeta estándar
$carnetHeight = 53.98; // 53.98mm = alto tarjeta estándar
$margenX = 10;
$margenY = 10;
$espacioX = 0;
$espacioY = 0;
$carnetsPerRow = 2;
$carnetsPerCol = 5;
$carnetsPerPage = 10;
$totalCarnets = count($carnetsData);
$carnetCounter = 0;
// Nombres de meses
$nombresMeses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
foreach($carnetsData as $carnet) {
    // Nueva página cada 10 carnets
    if($carnetCounter % $carnetsPerPage == 0) {
        $pdf->AddPage();
    }

    // Calcular posición del carnet
    $posEnPagina = $carnetCounter % $carnetsPerPage;
    $fila = floor($posEnPagina / $carnetsPerRow);
    $columna = $posEnPagina % $carnetsPerRow;
    
    $x = $margenX + ($columna * ($carnetWidth + $espacioX));
    $y = $margenY + ($fila * ($carnetHeight + $espacioY));
    
    // Color del mes
    $colorHex = $carnet['color_hex'];
    list($r, $g, $b) = sscanf($colorHex, "#%02x%02x%02x");
    
    // ====================
    // FONDO DEL CARNET
    // ====================
    
    // Fondo blanco
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x, $y, $carnetWidth, $carnetHeight, 'F');
    
    // Borde del carnet
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect($x, $y, $carnetWidth, $carnetHeight);
    
    // ====================
    // IMÁGENES DECORATIVAS
    // ====================

    // Luego poner la silueta encima (debe tener transparencia PNG)
    $imgFondo = "./app/views/imagenes/carnet/" . $sede['escuela_verticalfondo'];
    if(file_exists($imgFondo)) {
        $pdf->Image($imgFondo, $x, $y, 20, $carnetHeight);
    }

    // CAPA DE COLOR DEL MES (rectángulo sólido)
    $pdf->SetAlpha(0.5);
    $pdf->SetFillColor($r, $g, $b);
    $pdf->Rect($x, $y, 20, $carnetHeight, 'F');
    $pdf->SetAlpha(1);

    // Imagen decorativa derecha
    $imgDerecha = "./app/views/imagenes/carnet/" . $sede['escuela_verticalprincipal'];
    if(file_exists($imgDerecha)) {
        $pdf->Image($imgDerecha, $x + $carnetWidth - 65, $y, 1, $carnetHeight);
    }

    // ====================
    // HEADER: LOGO Y QR
    // ====================
    
    // Logo de la sede
    $logoPath = "./app/views/imagenes/fotos/sedes/" . $sede['sede_foto'];
    if(file_exists($logoPath)) {
        $pdf->Image($logoPath, $x + 40, $y + 2, 13, 18);
    }
    
    // Código QR
    $estadoAlumno = $insCarnet->EstadoAlumno($carnet['alumno_id']);
    if($estadoAlumno->rowCount() == 1) {
        $estadoAlumno = $estadoAlumno->fetch();
        $condicion = $estadoAlumno['Condicion'];
        $fechaUltPension = $estadoAlumno['FechaUltPension'];
    } else {
        $condicion = 'Pendiente';
        $fechaUltPension = date('Y-m-d');
    }
    
    $qrData = "Estado pension: " . $condicion . "\n" .
              "Fecha ultimo pago: " . $fechaUltPension . "\n" .
              "Sede: " . $sede['sede_nombre'] . "\n" .
              $sede['sede_telefono'];
    
    // Generar archivo QR temporal
    $qrFile = $tempDir . "qr_" . $carnet['alumno_id'] . ".jpeg";

    // Generar imagen QR
    $image = $generator->render_image($symbology, $qrData, $optionsQR);
    imagejpeg($image, $qrFile);
    imagedestroy($image);
    
    // Insertar QR en el PDF
    if(file_exists($qrFile)) {
        $pdf->Image($qrFile, $x + $carnetWidth - 15, $y + 2, 12, 12);
        // Eliminar archivo temporal después de usarlo
        @unlink($qrFile);
    }
    
    // Número de carnet debajo del QR
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($x + $carnetWidth - 15, $y + 14);
    $pdf->Cell(12, 3, $carnet['alumno_carnet'], 0, 0, 'C');
    
    // ====================
    // CONTENIDO: INFO Y FOTO
    // ====================
    
    // Foto del alumno
    $fotoPath = "./app/views/imagenes/fotos/alumno/" . $carnet['alumno_imagen'];
    if(!file_exists($fotoPath) || empty($carnet['alumno_imagen'])) {
        $fotoPath = "./app/views/imagenes/fotos/alumno/koki.jpg";
    }
    
    $fotoX = $x + $carnetWidth - 23;
    $fotoY = $y + 20;
    $fotoWidth = 20;
    $fotoHeight = 25;
    
    if(file_exists($fotoPath)) {
        $pdf->Image($fotoPath, $fotoX, $fotoY, $fotoWidth, $fotoHeight);
    }
    
    // Marco de la foto
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect($fotoX, $fotoY, $fotoWidth, $fotoHeight);
    
    // ====================
    // INFORMACIÓN DEL ALUMNO
    // ====================
    
    $infoX = $x + 22;
    $infoY = $y + 17;

    // TÍTULO "DEPORTISTA"
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(40, 3, 'DEPORTISTA', 0, 0, 'L');
    
    // Nombre completo
    $infoY += 4;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($infoX, $infoY);
    $nombreCompleto = mb_convert_encoding(
        strtoupper($carnet['alumno_nombre']), 
        'ISO-8859-1', 'UTF-8'
    );

    // Si el nombre es muy largo, dividirlo en 2 líneas
    if(strlen($carnet['alumno_nombre']) > 21) {
        $pdf->MultiCell(40, 3, $nombreCompleto, 0, 'L');
        $infoY = $pdf->GetY(); //Para separar el multicell
    } else {
        $pdf->Cell(40, 3, $nombreCompleto, 0, 0, 'L');
        $infoY += 3;
    }
    
    // C.I.
    $infoY += 1;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'C.I.:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($infoX + 8, $infoY);
    $pdf->Cell(32, 2.5, $carnet['alumno_identificacion'], 0, 0, 'L');
    
    // Horario
    $infoY += 3;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Horario:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($infoX + 12, $infoY);
    $pdf->Cell(28, 2.5, 
        mb_convert_encoding($carnet['horario_nombre'], 'ISO-8859-1', 'UTF-8'), 
        0, 0, 'L');
    
    // Edad (calcular desde fecha de nacimiento)
    $infoAlumno = $insCarnet->infoAlumnoCarnet($carnet['alumno_id']);
    if($infoAlumno->rowCount() == 1) {
        $infoAlumno = $infoAlumno->fetch();
        $edad = date_diff(
            date_create($infoAlumno['alumno_fechanacimiento']), 
            date_create('today')
        )->y;
    } else {
        $edad = 0;
    }
    
    $infoY += 3;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Edad:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->SetXY($infoX + 10, $infoY);
    $pdf->Cell(30, 2.5, $edad . ' ' . mb_convert_encoding('años', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    
    // Mes vigencia con badge de color
    $infoY += 3;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Mes vigencia:', 0, 0, 'L');
    
    // Badge con color del mes
    $pdf->SetFillColor($r, $g, $b);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY($infoX + 18, $infoY - 0.5);
    $mesNombre = $nombresMeses[$carnet['carnet_mes']] ?? 'N/A';
    $pdf->Cell(20, 3, 
        mb_convert_encoding($mesNombre, 'ISO-8859-1', 'UTF-8'), 
        0, 0, 'C', true);
    
    // Sede
    $infoY += 3;
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Sede:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($infoX + 10, $infoY);
    $pdf->Cell(30, 2.5, 
        mb_convert_encoding($sede['sede_nombre'], 'ISO-8859-1', 'UTF-8'), 
        0, 0, 'L');
    
    // Contacto
    $infoY += 3.5;
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(40, 2, 'clubjorgeguzman@gmail.com', 0, 0, 'L');
    $infoY += 3;
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(40, 2, '0983779393', 0, 0, 'L');
    
    // ====================
    // MARCA DE REIMPRESIÓN
    // ====================
    
    if(!empty($carnet['es_reimpresion']) && $carnet['es_reimpresion'] == 1) {
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetXY($x + 22, $y + $carnetHeight - 5);
        $pdf->Cell(30, 3, mb_convert_encoding('REIMPRESIÓN', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    }
    
    // ====================
    // FOOTER
    // ====================
    
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->Line($x + 22, $y + $carnetHeight - 2, $x + $carnetWidth - 3, $y + $carnetHeight - 2);    

     // Fecha de impresión en esquina inferior derecha
    $pdf->SetFont('Arial', '', 5);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY($x + $carnetWidth - 25, $y + $carnetHeight - 4);
    $pdf->Cell(22, 2, 'Impreso: ' . date('d/m/Y'), 0, 0, 'R');

    $carnetCounter++;
}
// Registrar impresión de todos los carnets
$carnet_ids = array_column($carnetsData, 'carnet_id');
$insCarnet->registrarImpresion($carnet_ids);
// Salida del PDF
$pdf->Output("Carnets_" . date('Y-m') . ".pdf", "I", "T");
?>