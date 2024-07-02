<?php
require_once 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $apellido = $mysqli->real_escape_string($_POST['apellido']);
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    $telefono = $mysqli->real_escape_string($_POST['telefono']);
    $fecha = $mysqli->real_escape_string($_POST['fecha']);
    $hora = $mysqli->real_escape_string($_POST['hora']);
    $num_personas = intval($_POST['num_personas']);

    // Validar que la fecha y hora sean futuras
    $fecha_hora_reserva = new DateTime($fecha . ' ' . $hora);
    $ahora = new DateTime();
    if ($fecha_hora_reserva <= $ahora) {
        echo "<p class='error'>La fecha y hora de la reserva deben ser futuras.</p>";
        exit;
    }

    // Iniciar transacción
    $mysqli->begin_transaction();

    try {
        // Verificar si el cliente ya existe
        $sql_check_cliente = "SELECT id_cliente FROM clientes WHERE cedula_cliente = ?";
        $stmt_check_cliente = $mysqli->prepare($sql_check_cliente);
        $stmt_check_cliente->bind_param("s", $cedula);
        $stmt_check_cliente->execute();
        $result_cliente = $stmt_check_cliente->get_result();

        if ($result_cliente->num_rows > 0) {
            $row_cliente = $result_cliente->fetch_assoc();
            $id_cliente = $row_cliente['id_cliente'];
        } else {
            // Insertar nuevo cliente
            $sql_cliente = "INSERT INTO clientes (nombre_cliente, apellido_cliente, telefono_cliente, cedula_cliente) VALUES (?, ?, ?, ?)";
            $stmt_cliente = $mysqli->prepare($sql_cliente);
            $stmt_cliente->bind_param("ssss", $nombre, $apellido, $telefono, $cedula);
            $stmt_cliente->execute();
            $id_cliente = $mysqli->insert_id;
        }

        // Insertar la reserva
        $sql_reserva = "INSERT INTO reservas (fecha_reserva, hora_reserva, numero_personas) VALUES (?, ?, ?)";
        $stmt_reserva = $mysqli->prepare($sql_reserva);
        $stmt_reserva->bind_param("ssi", $fecha, $hora, $num_personas);
        $stmt_reserva->execute();
        $id_reserva = $mysqli->insert_id;

        // Actualizar el id_reserva en la tabla clientes
        $sql_update_cliente = "UPDATE clientes SET id_reserva = ? WHERE id_cliente = ?";
        $stmt_update_cliente = $mysqli->prepare($sql_update_cliente);
        $stmt_update_cliente->bind_param("ii", $id_reserva, $id_cliente);
        $stmt_update_cliente->execute();

        // Commit la transacción
        $mysqli->commit();

        echo "<p class='success'>Reserva registrada con éxito. ID de reserva: $id_reserva, ID de cliente: $id_cliente</p>";
    } catch (Exception $e) {
        // Rollback en caso de error
        $mysqli->rollback();
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Mesa</title>
    <link rel="stylesheet" href="css/main_style.css">
    <link rel="stylesheet" href="css/reserva_style.css">
</head>
<body>
    <header>
        <h1>Reserva de Mesa</h1>
    </header>

    <main>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>

            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" required>

            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required>

            <label for="fecha">Fecha de reserva:</label>
            <input type="date" id="fecha" name="fecha" required>

            <label for="hora">Hora de reserva:</label>
            <input type="time" id="hora" name="hora" required>

            <label for="num_personas">Número de personas:</label>
            <select id="num_personas" name="num_personas" required>
                <?php
                for ($i = 1; $i <= 10; $i++) {
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select>

            <input type="submit" value="Reservar">
        </form>
    </main>

    <footer>
        <p>&copy; 2024 Bocados' Express. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
