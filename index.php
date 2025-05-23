<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Carta</title>
</head>
<body>

<a href="index.php">Generar Paz y Salvo</a>
<a href="historia.php">Ver Historia de paz y salvos</a>
<a href="listar_registros.php">Listar Registros</a>


    <h2>Generar Carta</h2>
    <form action="generar_carta.php" method="POST">
        <label for="nombre">Nombre Completo:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="tipo_documento">Tipo de Documento:</label>
        <select id="tipo_documento" name="tipo_documento" required>
            <option value="CC">Cédula de Ciudadanía</option>
            <option value="RC">Registro Civil</option>
            <option value="TI">Tarjeta de Identidad</option>
            <option value="PAS">Pasaporte</option>
        </select><br><br>

        <label for="numero_documento">Número de Documento:</label>
        <input type="text" id="numero_documento" name="numero_documento" required><br><br>

        <button type="submit">Generar Carta</button>
    </form>
</body>
</html>
