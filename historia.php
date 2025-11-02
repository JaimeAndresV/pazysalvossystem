<?php
require 'conexion.php';   // ya contiene la conexión y charset
require 'auth.php';       // verifica que el usuario esté autenticado

// Obtener letras iniciales únicas de los nombres en la base de datos
$letras_result = $conn->query("SELECT DISTINCT LEFT(nombre, 1) AS letra FROM pazysalvos ORDER BY letra ASC");
$letras = [];
if ($letras_result->num_rows > 0) {
    while ($row = $letras_result->fetch_assoc()) {
        $letras[] = strtoupper($row['letra']);
    }
}

// Inicializar variables de búsqueda
$search_term = '';
$filtro_letra = '';

// Ajustar la consulta
if (isset($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT nombre, tipo_documento, numero_documento, COUNT(*) as veces_generado, MAX(fecha_generacion) as ultima_fecha
            FROM pazysalvos
            WHERE nombre LIKE '%$search_term%' OR numero_documento LIKE '%$search_term%'
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial - Paz y Salvo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body class="bg-light">
  <div class="container py-4">
    <nav class="nav mb-3">
      <a class="nav-link" href="index.php">Generar Paz y Salvo</a>
      <a class="nav-link active" href="historia.php">Historia</a>
      <a class="nav-link" href="listar_registros.php">Listar Registros</a>
      <a class="nav-link ms-auto" href="logout.php">Cerrar sesión</a>
    </nav>

    <h2 class="mb-4">Historial de Paz y Salvos</h2>

    <div class="mb-3">
      <span class="fw-bold">Filtrar por inicial:</span>
      <?php foreach ($letras as $letra): ?>
        <a href="historia.php?letra=<?php echo $letra; ?>"
           class="badge bg-primary text-decoration-none mx-1"><?php echo $letra; ?></a>
      <?php endforeach; ?>
    </div>

    <form method="GET" action="historia.php" class="input-group mb-4">
      <input type="text" name="search" class="form-control"
             placeholder="Buscar por nombre o documento"
             value="<?php echo htmlspecialchars($search_term); ?>">
      <button class="btn btn-outline-secondary" type="submit">Buscar</button>
      <button class="btn btn-outline-secondary" type="button"
              onclick="window.location.href='historia.php'">Limpiar</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo Documento</th>
              <th>Número Documento</th>
              <th>Veces Generado</th>
              <th>Última Fecha</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['nombre']; ?></td>
              <td><?php echo $row['tipo_documento']; ?></td>
              <td><?php echo $row['numero_documento']; ?></td>
              <td><?php echo $row['veces_generado']; ?></td>
              <td><?php echo $row['ultima_fecha']; ?></td>
              <td><a href="ver_pazysalvo.php?numero_documento=<?php echo $row['numero_documento']; ?>" class="btn btn-sm btn-primary" target="_blank">Ver</a></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No hay registros.</div>
    <?php endif; ?>

  </div>
  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
          crossorigin="anonymous"></script>
</body>
</html>