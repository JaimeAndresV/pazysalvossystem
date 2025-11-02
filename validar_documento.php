<?php
header('Content-Type: application/json');

// Conexión a la base de datos para verificar registros
require 'conexion.php';

// Recoger número de documento de la petición
$numero = $_GET['numero'] ?? '';

// Verificar si ya existe un paz y salvo en la base de datos
$stmtExist = $conn->prepare("SELECT COUNT(*) AS cnt FROM pazysalvos WHERE numero_documento = ?");
$stmtExist->bind_param('s', $numero);
$stmtExist->execute();
$existeCount = $stmtExist->get_result()->fetch_assoc()['cnt'];

// Ruta al CSV UTF-8
$archivo = __DIR__ . '/datos.csv';
if (!file_exists($archivo)) {
    echo json_encode(['found' => false, 'already_generated' => ($existeCount > 0)]);
    exit;
}

if (($f = fopen($archivo, 'r')) !== false) {
    // Si el CSV tiene cabecera, descartarla
    fgetcsv($f);
    while (($fila = fgetcsv($f, 0, ',')) !== false) {
        // Ajuste de columnas: número en columna 1, nombre en columna 2, fecha de nacimiento en columna 3
        if (trim($fila[1]) === $numero) {
            echo json_encode([
                'found'             => true,
                'nombre'            => $fila[2],
                'fecha_nacimiento'  => $fila[3],
                'already_generated' => ($existeCount > 0)
            ]);
            fclose($f);
            exit;
        }
    }
    fclose($f);
}

// No encontrado en CSV
echo json_encode(['found' => false, 'already_generated' => ($existeCount > 0)]);