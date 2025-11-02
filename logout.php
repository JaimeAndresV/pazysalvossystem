<?php
session_start();
// Vaciar todas las variables de sesión.
$_SESSION = array();
// Destruir la sesión.
session_destroy();
// Redirigir al formulario de login.
header('Location: login.php');
exit;
?>