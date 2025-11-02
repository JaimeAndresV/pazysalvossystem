<?php
require 'conexion.php';   // ya contiene la conexión y charset
require 'auth.php';       // verifica que el usuario esté autenticado

// Contar total de paz y salvos generados
$countResult = $conn->query("SELECT COUNT(*) AS total FROM pazysalvos");
if ($countResult) {
    $countRow = $countResult->fetch_assoc();
    $total = $countRow['total'];
} else {
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Paz y Salvo - Generar Carta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body class="bg-light">
  <div class="container py-5">
    <nav class="nav mb-4">
      <a class="nav-link" href="index.php">Generar Paz y Salvo</a>
      <a class="nav-link" href="historia.php">Historia</a>
      <a class="nav-link" href="listar_registros.php">Listar Registros</a>
      <a class="nav-link ms-auto" href="logout.php">Cerrar Sesión</a>
    </nav>

    <div class="card mx-auto" style="max-width:600px;">
      <div class="card-body">
        <h2 class="card-title text-center mb-4">Generar Paz y Salvo</h2>
        <div class="mb-3">
  <center><span class="badge bg-info text-center">Total Paz y Salvos: <?= $total; ?></span></center>
</div>
        <form action="generar_carta.php" method="POST" target="_blank">
          
          <div class="mb-3">
            <label for="numero_documento" class="form-label">Número de Documento</label>
            <input type="text" id="numero_documento" name="numero_documento" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
            <select id="tipo_documento" name="tipo_documento" class="form-select" required readonly>
              <option value="CC">Cédula de Ciudadanía</option>
              <option value="RC">Registro Civil</option>
              <option value="TI">Tarjeta de Identidad</option>
              <option value="PAS">Pasaporte</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required readonly>
          </div>
          <div class="mb-4">
          <label for="doc_info" class="form-label">Fecha de Nacimiento</label>
          <input id="doc_info" class="form-control" rows="4" readonly disabled>
        </div>
          <div id="mensaje_registro" class="alert d-none" role="alert"></div>
          <button type="submit" class="btn btn-primary w-100">Generar Carta</button>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

<script>
  const inputNum = document.getElementById('numero_documento');
  const docInfo = document.getElementById('doc_info');
  const selectTipo = document.getElementById('tipo_documento');
  const alerta = document.getElementById('mensaje_registro');

  inputNum.addEventListener('blur', async function() {
    const numero = this.value.trim();
    alerta.classList.add('d-none');
    alerta.textContent = '';
    if (!numero) return;

    try {
      const res = await fetch(`validar_documento.php?numero=${encodeURIComponent(numero)}`);
      const data = await res.json();

      if (!data.found) {
        alerta.className = 'alert alert-danger';
        alerta.textContent = 'Documento no autorizado.';
        alerta.classList.remove('d-none');
        docInfo.value = '';
        return;
      }

      // Calcular edad
      const parts = data.fecha_nacimiento.split('.');
      const nac = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
      const hoy = new Date();
      let edad = hoy.getFullYear() - nac.getFullYear();
      const m = hoy.getMonth() - nac.getMonth();
      if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;

      // Ajustar select: eliminar PAS
      const pasOpt = selectTipo.querySelector('option[value="PAS"]');
      if (pasOpt) pasOpt.remove();

      // Seleccionar tipo según edad
      if (edad >= 18) selectTipo.value = 'CC';
      else if (edad >= 8) selectTipo.value = 'TI';
      else selectTipo.value = 'RC';

      // Rellenar campos de nombre
      document.getElementById('nombre').value = data.nombre;

      // Mostrar edad y detalles en textarea
      docInfo.value = 
//Número: ${numero}
//Nombre: ${data.nombre}
`Fecha de nacimiento: ${data.fecha_nacimiento}
 - Edad: ${edad} años`;

      // Si ya existe registro, mostrar mensaje en alerta
      if (data.already_generated) {
        alerta.className = 'alert alert-warning';
        alerta.innerHTML = `⚠️ Ya existe un Paz y Salvo generado anteriormente para este documento. <a href="ver_pazysalvo.php?numero_documento=${numero}" target="_blank" class="alert-link">Ver PDF</a>`;
        alerta.classList.remove('d-none');
      }
} catch (err) {
      console.error(err);
      alerta.className = 'alert alert-danger';
      alerta.textContent = 'Error al validar el documento.';
      alerta.classList.remove('d-none');
    }
  });
</script>
</body>
</html>