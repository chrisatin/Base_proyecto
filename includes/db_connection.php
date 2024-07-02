<?php
$mysqli = new mysqli("localhost", "root", "123456789", "restaurante_proyecto");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}
?>