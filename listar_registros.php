<?php
require 'conexion.php';   // ya contiene la conexión y charset
require 'auth.php';       // verifica que el usuario esté autenticado

// Verificar si se envió una solicitud de eliminación múltiple
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $delete_ids = $_POST['delete_ids'];
    $ids = implode(',', array_map('intval', $delete_ids));
    
    // Eliminar registros seleccionados
    $delete_sql = "DELETE FROM pazysalvos WHERE id IN ($ids)";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<div class='alert alert-success'>Registros eliminados correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error al eliminar los registros: " . $conn->error . "</div>";
    }
}

// Verificar si se envió una solicitud de eliminación individual
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Eliminar el registro con el ID especificado
    $delete_sql = "DELETE FROM pazysalvos WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<div class='alert alert-success'>Registro eliminado correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error al eliminar el registro: " . $conn->error . "</div>";
    }
}

// Obtener letras iniciales únicas de los nombres
$letras_result = $conn->query("SELECT DISTINCT LEFT(nombre, 1) AS letra FROM pazysalvos ORDER BY letra ASC");
$letras = [];
if ($letras_result->num_rows > 0) {
    while ($row = $letras_result->fetch_assoc()) {
        $letras[] = strtoupper($row['letra']);
    }
}

// Variables de búsqueda
$search_term = '';
$filtro_letra = '';

// Construir consulta
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Paz y Salvos</title>
    <!-- Bootstrap CSS mínimo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body class="bg-light">
  <div class="container py-4">
    <nav class="nav mb-3">
      <a class="nav-link" href="index.php">Generar Paz y Salvo</a>
      <a class="nav-link" href="historia.php">Historia</a>
      <a class="nav-link active" href="listar_registros.php">Listar Registros</a>
      <a class="nav-link ms-auto" href="logout.php">Cerrar sesión</a>
    </nav>

    <h2 class="mb-4">Lista de Paz y Salvos</h2>

    <!-- Filtro por inicial -->
    <div class="mb-3">
      <span class="fw-bold">Filtrar por inicial:</span>
      <?php foreach ($letras as $letra): ?>
        <a href="listar_registros.php?letra=<?php echo $letra; ?>"
           class="badge bg-primary text-decoration-none mx-1"><?php echo $letra; ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Búsqueda -->
    <form method="GET" action="listar_registros.php" class="input-group mb-4">
      <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o documento"
             value="<?php echo htmlspecialchars($search_term); ?>">
      <button class="btn btn-outline-secondary" type="submit">Buscar</button>
      <button class="btn btn-outline-secondary" type="button"
              onclick="window.location.href='listar_registros.php'">Limpiar</button>
    </form>

    <!-- Tabla de registros -->
    <form method="POST" action="listar_registros.php">
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th scope="col"><input type="checkbox" id="select_all" title="Seleccionar todos"></th>
              <th scope="col">ID</th>
              <th scope="col">Nombre</th>
              <th scope="col">Tipo Documento</th>
              <th scope="col">Número</th>
              <th scope="col">Fecha</th>
              <th scope="col">Acciones</th>
              <th scope="col">Ver</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><input type="checkbox" name="delete_ids[]" value="<?php echo $row['id']; ?>"></td>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['nombre']; ?></td>
                <td><?php echo $row['tipo_documento']; ?></td>
                <td><?php echo $row['numero_documento']; ?></td>
                <td><?php echo $row['fecha_generacion']; ?></td>
                <td>
                  <a href="listar_registros.php?delete_id=<?php echo $row['id']; ?>"
                     class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este registro?')">Eliminar</a>
                </td>
                <td>
                  <a href="ver_pazysalvo.php?numero_documento=<?php echo $row['numero_documento']; ?>" class="btn btn-sm btn-primary" target="_blank">Ver</a>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center">No hay registros disponibles.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('¿Eliminar registros seleccionados?')">Eliminar seleccionados</button>
    </form>
  </div>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
          crossorigin="anonymous"></script>
</body>
</html>