<?php
require('fpdf/fpdf.php');

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'pazysalvo');
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar que se recibió el número de documento
if (isset($_GET['numero_documento'])) {
    $numero_documento = $_GET['numero_documento'];

    // Consultar los datos del paz y salvo
    $sql = "SELECT nombre, tipo_documento, numero_documento, MAX(fecha_generacion) AS fecha_generacion
            FROM pazysalvos
            WHERE numero_documento = '$numero_documento'
            GROUP BY numero_documento, nombre, tipo_documento";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];
        $tipo_documento = $row['tipo_documento'];
        $fecha_generacion = $row['fecha_generacion'];

        // Configurar la fecha del paz y salvo
        $dia = date('d', strtotime($fecha_generacion));
        $mes = date('F', strtotime($fecha_generacion));
        $año = date('Y', strtotime($fecha_generacion));
        $meses_es = array(
            'January' => 'enero',
            'February' => 'febrero',
            'March' => 'marzo',
            'April' => 'abril',
            'May' => 'mayo',
            'June' => 'junio',
            'July' => 'julio',
            'August' => 'agosto',
            'September' => 'septiembre',
            'October' => 'octubre',
            'November' => 'noviembre',
            'December' => 'diciembre'
        );
        $mes = $meses_es[$mes];

        // Crear el PDF
        class PDF extends FPDF {
            function Header() {
                $this->Image('images/membrete.jpg', 0, 0, 215, 280);
                $this->SetY(50);
            }
        }

        $pdf = new PDF('P', 'mm', 'Letter');
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        $pdf->SetTopMargin(20);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        // Título "Paz y Salvo"
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, mb_convert_encoding('PAZ Y SALVO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(10);

        // Título "Certifico que"
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, mb_convert_encoding('A PETICIÓN DEL INTERESADO CERTIFICO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(10);

        // Contenido del paz y salvo
        $pdf->SetFont('Arial', '', 12);
        $contenido = "Que $nombre, identificado(a) con $tipo_documento No. $numero_documento, ya no pertenece al CLUB DEPORTIVO ATLÉTICO AMERICANO REAL TARRAGONA, por lo tanto, se encuentra a PAZ Y SALVO con nuestro Club, quedando en total libertad para jugar en cualquier club que le pretenda.\n\nPara constancia de lo anterior se firma en Tarragona, Florida, Valle del Cauca, a los ($dia) días del mes de $mes de $año.";
        $pdf->MultiCell(170, 10, mb_convert_encoding($contenido, 'ISO-8859-1', 'UTF-8'), 0, 'J');

        // Espacio para la firma
        $pdf->Ln(20);
        $ruta_firma = 'images/magaly.png';
        if (file_exists($ruta_firma)) {
            $pdf->Image($ruta_firma, 20, 171, 50);
        } else {
            die("Error: No se encontró la imagen de la firma.");
        }

        // Continuación con el texto de la firma
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, mb_convert_encoding('Vivian Magaly Torres Lopez', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Presidente', 0, 1, 'L');

        // Mostrar el PDF en el navegador
        $pdf->Output();
        exit;
    } else {
        die("No se encontraron datos para este número de documento.");
    }
} else {
    die("Número de documento no especificado.");
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
