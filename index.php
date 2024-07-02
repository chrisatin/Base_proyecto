<?php
require_once 'includes/db_connection.php';

// Obtener todas las sucursales
$sql_sucursales = "SELECT * FROM sucursales";
$result_sucursales = $mysqli->query($sql_sucursales);

// Orden deseado para las categorías
$orden_categorias = ['Hamburguesas', 'Papas', 'Sodas', 'Malteadas', 'Cocteles', 'Cervezas', 'Bebidas',];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bocados' Express</title>
    <link rel="stylesheet" href="css/main_style.css">
</head>
<body>
    <header>
        <h1>Bocados' Express</h1>
        <!-- Se eliminó botón para dejar visualmente solamente el menú de cada sucursal -->
        <!-- <a href="login.php" class="login-btn">Iniciar sesión</a> -->
    </header>

    <main>
        <div></div>
        <?php
        if ($result_sucursales->num_rows > 0) {
            while($sucursal = $result_sucursales->fetch_assoc()) {
                echo "<section class='sucursal'>";
                echo "<h2>{$sucursal['ciudad_sucursal']} - {$sucursal['direccion_sucursal']}</h2>";

                // Obtener el menú para esta sucursal
                $sql_menu = "SELECT * FROM menus WHERE id_sucursal = {$sucursal['id_sucursal']} ORDER BY FIELD(categoria_plato, '" . implode("','", $orden_categorias) . "'), nombre_plato";
                $result_menu = $mysqli->query($sql_menu);

                if ($result_menu->num_rows > 0) {
                    $categoria_actual = '';
                    echo "<table class='menu'>";
                    echo "<tr><th>Plato</th><th>Precio</th></tr>";
                    while($plato = $result_menu->fetch_assoc()) {
                        if ($categoria_actual != $plato['categoria_plato']) {
                            echo "<tr><td colspan='2' class='categoria'>{$plato['categoria_plato']}</td></tr>";
                            $categoria_actual = $plato['categoria_plato'];
                        }
                        echo "<tr><td>{$plato['nombre_plato']}</td><td>\${$plato['precio_plato']}</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No hay menú disponible para esta sucursal.</p>";
                }

                echo "</section>";
            }
        } else {
            echo "<p>No hay sucursales disponibles.</p>";
        }
        ?>
    </main>

    <footer>
        <p>&copy; 2024 Bocados' Express. Todos los derechos reservados.</p>
    </footer>
</body>
</html>