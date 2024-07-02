<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'Gestor') {
    header("location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestor</title>
</head>
<body>
    <h1>Bienvenido al Panel de Gestor</h1>
    <p>Aquí irá el contenido para el gestor.</p>
    <a href="../logout.php">Cerrar sesión</a>
</body>
</html>