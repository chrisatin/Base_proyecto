<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'Autor') {
    header("location: ../login.php");
    exit();
}

// Obtener información del autor
$autor_id = $_SESSION['user_id'];
$sql_autor = "SELECT e.nombre_empleado, e.apellido_empleado, s.id_sucursal, s.ciudad_sucursal, s.direccion_sucursal 
              FROM empleados e 
              JOIN sucursales s ON e.id_sucursal = s.id_sucursal 
              WHERE e.id_empleado = (SELECT id_empleado FROM usuarios WHERE id_usuario = $autor_id)";
$result_autor = $mysqli->query($sql_autor);
$autor_info = $result_autor->fetch_assoc();

// Obtener reservas de la sucursal
$sql_reservas = "SELECT r.*, c.nombre_cliente, c.apellido_cliente 
                 FROM reservas r 
                 JOIN clientes c ON r.id_reserva = c.id_reserva 
                 WHERE c.id_cliente IN (SELECT id_cliente FROM pedidos WHERE id_sucursal = {$autor_info['id_sucursal']}) 
                 ORDER BY r.fecha_reserva DESC, r.hora_reserva DESC";
$result_reservas = $mysqli->query($sql_reservas);

// Obtener pedidos de la sucursal
$sql_pedidos = "SELECT p.*, c.nombre_cliente, c.apellido_cliente 
                FROM pedidos p 
                JOIN clientes c ON p.id_cliente = c.id_cliente 
                WHERE p.id_sucursal = {$autor_info['id_sucursal']} 
                ORDER BY p.fecha_pedido DESC, p.hora_pedido DESC";
$result_pedidos = $mysqli->query($sql_pedidos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Autor - Bocados' Express</title>
    <link rel="stylesheet" href="../css/autor_style.css">
</head>
<body>
    <header>
        <h1>Bocados' Express</h1>
    </header>

    <main>
        <section class="autor-info">
            <h2>Cajero: <?php echo $autor_info['nombre_empleado'] . ' ' . $autor_info['apellido_empleado']; ?></h2>
            <p>Sucursal: <?php echo $autor_info['ciudad_sucursal'] . ' - ' . $autor_info['direccion_sucursal']; ?></p>
        </section>

        <section class="reservas">
            <h3>Reservas Recientes</h3>
            <table>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Personas</th>
                </tr>
                <?php while($reserva = $result_reservas->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $reserva['fecha_reserva']; ?></td>
                    <td><?php echo $reserva['hora_reserva']; ?></td>
                    <td><?php echo $reserva['nombre_cliente'] . ' ' . $reserva['apellido_cliente']; ?></td>
                    <td><?php echo $reserva['numero_personas']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>

        <section class="pedidos">
            <h3>Pedidos Recientes</h3>
            <table>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Total</th>
                </tr>
                <?php while($pedido = $result_pedidos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $pedido['fecha_pedido']; ?></td>
                    <td><?php echo $pedido['hora_pedido']; ?></td>
                    <td><?php echo $pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']; ?></td>
                    <td>$<?php echo $pedido['precio_pedido']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>

        <section class="actions">
            <a href="reservar.php" class="btn">Reservar</a>
            <a href="ordenar.php" class="btn">Ordenar Pedido</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Bocados' Express. Todos los derechos reservados.</p>
        <a href="../logout.php">Cerrar sesión</a>
    </footer>
</body>
</html>