<?php
require 'conexion.php';   // ya contiene la conexión y charset
require 'auth.php';       // verifica que el usuario esté autenticado
?>


<?php
require('fpdf/fpdf.php');


// Función para convertir texto a ISO-8859-1
function convertirTexto($texto) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

// Verificar si se enviaron los datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = $_POST['numero_documento'];

    // Insertar el paz y salvo en la base de datos
    $sql = "INSERT INTO pazysalvos (nombre, tipo_documento, numero_documento)
            VALUES ('$nombre', '$tipo_documento', '$numero_documento')";

    if (!$conn->query($sql)) {
        die("Error: " . $sql . "<br>" . $conn->error);
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

// Crear el PDF
class PDF extends FPDF {
    function Header() {
        $this->Image('images/membrete.jpg', 0, 0, 215, 280);
        $this->SetY(50);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Convertir todas las variables a ISO-8859-1
    $nombre = convertirTexto($_POST['nombre']);
    $tipo_documento = convertirTexto($_POST['tipo_documento']);
    $numero_documento = convertirTexto($_POST['numero_documento']);

    // Configurar fecha
    $dia = date('d');
    $mes = date('F');
    $año = date('Y');
    $meses_es = [
        'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo',
        'April' => 'abril', 'May' => 'mayo', 'June' => 'junio',
        'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre',
        'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
    ];
    $mes = convertirTexto($meses_es[$mes]);

    $pdf = new PDF('P', 'mm', 'Letter');
    $pdf->SetLeftMargin(20);
    $pdf->SetRightMargin(20);
    $pdf->SetTopMargin(20);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Títulos y subtítulos
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, convertirTexto('PAZ Y SALVO'), 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirTexto('A PETICIÓN DEL INTERESADO CERTIFICO'), 0, 1, 'C');
    $pdf->Ln(10);

    // Contenido del certificado
    $contenido = "Que $nombre, identificado(a) con $tipo_documento No. $numero_documento, ya no pertenece a nuestro CLUB DEPORTIVO ATLÉTICO AMERICANO REAL TARRAGONA, por lo tanto, se encuentra a PAZ Y SALVO con nuestro Club, quedando en total libertad para jugar en cualquier club que le pretenda.\n\nPara constancia de lo anterior se firma en Tarragona, Florida, Valle del Cauca, a los ($dia) días del mes de $mes de $año.";
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(170, 10, convertirTexto($contenido), 0, 'J');

    // Firma
    $pdf->Ln(20);
    $pdf->Image('images/magaly.png', 20, 190, 50);

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirTexto('Vivian Magaly Torres Lopez'), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Presidente', 0, 1, 'L');

    // Generar PDF
    $pdf->Output();
    exit;
}
?>
