<?php
// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'pazysalvo');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si se envió una solicitud de eliminación múltiple
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $delete_ids = $_POST['delete_ids'];
    $ids = implode(',', array_map('intval', $delete_ids));
    
    // Eliminar registros seleccionados
    $delete_sql = "DELETE FROM pazysalvos WHERE id IN ($ids)";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Registros eliminados correctamente.<br><br>";
    } else {
        echo "Error al eliminar los registros: " . $conn->error;
    }
}

// Verificar si se envió una solicitud de eliminación individual
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Eliminar el registro con el ID especificado
    $delete_sql = "DELETE FROM pazysalvos WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Registro eliminado correctamente.<br><br>";
    } else {
        echo "Error al eliminar el registro: " . $conn->error;
    }
}

// Obtener letras iniciales únicas de los nombres en la base de datos
$letras_result = $conn->query("SELECT DISTINCT LEFT(nombre, 1) AS letra FROM pazysalvos ORDER BY letra ASC");
$letras = [];
if ($letras_result->num_rows > 0) {
    while ($row = $letras_result->fetch_assoc()) {
        $letras[] = strtoupper($row['letra']); // Convertir a mayúsculas para mostrar como opciones
    }
}

// Inicializar variables de búsqueda
$search_term = '';
$filtro_letra = '';

// Construir la consulta en función del filtro de búsqueda o de la letra seleccionada
if (isset($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT id, nombre, tipo_documento, numero_documento, fecha_generacion 
            FROM pazysalvos 
            WHERE nombre LIKE '%$search_term%' 
            OR numero_documento LIKE '%$search_term%'";
} elseif (isset($_GET['letra'])) {
    $filtro_letra = $conn->real_escape_string($_GET['letra']);
    $sql = "SELECT id, nombre, tipo_documento, numero_documento, fecha_generacion 
            FROM pazysalvos 
            WHERE nombre LIKE '$filtro_letra%'";
} else {
    $sql = "SELECT id, nombre, tipo_documento, numero_documento, fecha_generacion FROM pazysalvos";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Paz y Salvos</title>
</head>
<body>

<a href="index.php">Generar Paz y Salvo</a>
<a href="historia.php">Ver Historia de paz y salvos</a>
<a href="listar_registros.php">Listar Registros</a>

<h2>Lista de Paz y Salvos</h2>

<!-- Opciones de filtro por letra inicial -->
<div>
    <strong>Filtrar por letra inicial:</strong>
    <?php foreach ($letras as $letra): ?>
        <a href="listar_registros.php?letra=<?php echo $letra; ?>"><?php echo $letra; ?></a>
    <?php endforeach; ?>
</div>
<br>

<!-- Formulario de búsqueda -->
<form method="GET" action="listar_registros.php">
    <label for="search">Buscar por Nombre o Número de Documento:</label>
    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
    <button type="submit">Buscar</button>
    <button type="button" onclick="window.location.href='listar_registros.php'">Limpiar</button>
</form>
<br>

<!-- Tabla de registros -->
<form method="POST" action="listar_registros.php">
    <table border="1">
        <tr>
            <th>Seleccionar</th>
            <th>ID</th>
            <th>Nombre</th>
            <th>Tipo Documento</th>
            <th>Número Documento</th>
            <th>Fecha Generación</th>
            <th>Acciones</th>
            <th>Ver Paz y Salvo</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><input type='checkbox' name='delete_ids[]' value='" . $row['id'] . "'></td>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['nombre'] . "</td>";
                echo "<td>" . $row['tipo_documento'] . "</td>";
                echo "<td>" . $row['numero_documento'] . "</td>";
                echo "<td>" . $row['fecha_generacion'] . "</td>";
                echo "<td><a href='listar_registros.php?delete_id=" . $row['id'] . "' onclick='return confirm(\"¿Estás seguro de que deseas eliminar este registro?\")'>Eliminar</a></td>";
                echo "<td><a href='ver_pazysalvo.php?numero_documento=" . $row['numero_documento'] . "' target='_blank'>Ver Paz y Salvo</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No hay registros disponibles.</td></tr>";
        }
        ?>
    </table>
    <br>
    <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar los registros seleccionados?')">Eliminar seleccionados</button>
</form>
</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$conn->close();
?>
