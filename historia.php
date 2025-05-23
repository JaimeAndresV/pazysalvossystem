<a href="index.php">Generar Paz y Salvo</a>
<a href="historia.php">Ver Historia de paz y salvos</a>
<a href="listar_registros.php">Listar Registros</a>
<h2>Historial</h2>

<?php
// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'pazysalvo');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
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

// Ajustar la consulta en función del filtro de búsqueda o de la letra seleccionada
if (isset($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT nombre, tipo_documento, numero_documento, COUNT(*) as veces_generado, MAX(fecha_generacion) as ultima_fecha
            FROM pazysalvos
            WHERE nombre LIKE '%$search_term%' 
            OR numero_documento LIKE '%$search_term%'
            GROUP BY numero_documento
            ORDER BY veces_generado DESC";
} elseif (isset($_GET['letra'])) {
    $filtro_letra = $conn->real_escape_string($_GET['letra']);
    $sql = "SELECT nombre, tipo_documento, numero_documento, COUNT(*) as veces_generado, MAX(fecha_generacion) as ultima_fecha
            FROM pazysalvos
            WHERE nombre LIKE '$filtro_letra%'
            GROUP BY numero_documento
            ORDER BY veces_generado DESC";
} else {
    $sql = "SELECT nombre, tipo_documento, numero_documento, COUNT(*) as veces_generado, MAX(fecha_generacion) as ultima_fecha
            FROM pazysalvos
            GROUP BY numero_documento
            ORDER BY veces_generado DESC";
}

$result = $conn->query($sql);
?>

<!-- Opciones de filtro por letra inicial -->
<div>
    <strong>Filtrar por letra inicial:</strong>
    <?php foreach ($letras as $letra): ?>
        <a href="historia.php?letra=<?php echo $letra; ?>"><?php echo $letra; ?></a>
    <?php endforeach; ?>
</div>
<br>

<!-- Formulario de búsqueda -->
<form method="GET" action="historia.php">
    <label for="search">Buscar por Nombre o Número de Documento:</label>
    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
    <button type="submit">Buscar</button>
    <button type="button" onclick="window.location.href='historia.php'">Limpiar</button>
</form>
<br>

<?php
// Mostrar resultados de la tabla
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Nombre</th><th>Tipo Documento</th><th>Número Documento</th><th>Veces Generado</th><th>Última Fecha</th><th>Ver Paz y Salvo</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['tipo_documento'] . "</td>";
        echo "<td>" . $row['numero_documento'] . "</td>";
        echo "<td>" . $row['veces_generado'] . "</td>";
        echo "<td>" . $row['ultima_fecha'] . "</td>";
        echo "<td><a href='ver_pazysalvo.php?numero_documento=" . $row['numero_documento'] . "' target='_blank'>Ver</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No hay registros.";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
