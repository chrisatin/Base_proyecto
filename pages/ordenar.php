<?php
session_start();
require_once '../includes/db_connection.php';


$orden_categorias = ['Hamburguesas', 'Papas', 'Sodas', 'Malteadas', 'Cocteles', 'Cervezas', 'Bebidas'];

// Verificar si el usuario está logueado y es un Autor
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'Autor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener información del cajero y la sucursal
$query = "SELECT e.nombre_empleado, e.apellido_empleado, s.id_sucursal, s.ciudad_sucursal, s.direccion_sucursal 
          FROM empleados e 
          JOIN sucursales s ON e.id_sucursal = s.id_sucursal 
          JOIN usuarios u ON e.id_empleado = u.id_empleado
          WHERE u.id_usuario = $user_id";
$result = $mysqli->query($query);
$empleado_info = $result->fetch_assoc();

// Obtener menú de la sucursal
$id_sucursal = $empleado_info['id_sucursal'];
$query = "SELECT * FROM menus WHERE id_sucursal = $id_sucursal ORDER BY FIELD(categoria_plato, '" . implode("','", $orden_categorias) . "'), nombre_plato";
$menu_result = $mysqli->query($query);

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar los datos del cliente
    $nombre_cliente = $mysqli->real_escape_string($_POST['nombre_cliente']);
    $apellido_cliente = $mysqli->real_escape_string($_POST['apellido_cliente']);
    $telefono_cliente = $mysqli->real_escape_string($_POST['telefono_cliente']);

    // Insertar nuevo cliente
    $sql_cliente = "INSERT INTO clientes (nombre_cliente, apellido_cliente, telefono_cliente) VALUES ('$nombre_cliente', '$apellido_cliente', '$telefono_cliente')";
    if ($mysqli->query($sql_cliente) === TRUE) {
        $id_cliente = $mysqli->insert_id;
        
        // Crear nuevo pedido
        date_default_timezone_set('America/Bogota');
        $fecha_pedido = date("Y-m-d");
        $hora_pedido = date("H:i:s");
        $lista_platos = "";
        $precio_total = 0;

        // Procesar los platos seleccionados
        if (isset($_POST['platos']) && is_array($_POST['platos'])) {
            foreach ($_POST['platos'] as $id_plato) {
                $cantidad = isset($_POST['cantidad'][$id_plato]) ? intval($_POST['cantidad'][$id_plato]) : 1;
                
                // Obtener información del plato
                $sql_plato = "SELECT nombre_plato, precio_plato FROM menus WHERE id_plato = $id_plato";
                $result_plato = $mysqli->query($sql_plato);
                if ($row_plato = $result_plato->fetch_assoc()) {
                    $lista_platos .= $row_plato['nombre_plato'] . " x" . $cantidad . ", ";
                    $precio_total += $row_plato['precio_plato'] * $cantidad;
                }
            }
            $lista_platos = rtrim($lista_platos, ", ");
        }

        // Insertar el pedido
        $sql_pedido = "INSERT INTO pedidos (id_sucursal, id_cliente, fecha_pedido, hora_pedido, Lista_Platos, precio_pedido) VALUES ($id_sucursal, $id_cliente, '$fecha_pedido', '$hora_pedido', '$lista_platos', $precio_total)";
        
        if ($mysqli->query($sql_pedido) === TRUE) {
            echo "<p class='success'>Pedido realizado con éxito. Número de pedido: " . $mysqli->insert_id . "</p>";
        } else {
            echo "<p class='error'>Error al crear el pedido: " . $mysqli->error . "</p>";
        }
    } else {
        echo "<p class='error'>Error al registrar el cliente: " . $mysqli->error . "</p>";
    }
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="../css/autor_style.css">
    <style>
        .user-data {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .user-data div {
            display: flex;
            flex-direction: column;
        }
        .user-data label {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .user-data input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Bocados' Express</h1>
    </header>

    <main>
        <div class="autor-info">
            <h2>Realizar Nuevo Pedido</h2>
            <p>Usted está generando un nuevo usuario y pedido en la sucursal <?php echo $empleado_info['ciudad_sucursal']; ?> (<?php echo $empleado_info['direccion_sucursal']; ?>) en caja recibe <?php echo $empleado_info['nombre_empleado'] . ' ' . $empleado_info['apellido_empleado']; ?>.</p>
        </div>

        <form id="orderForm" method="POST" class="pedidos">
            <h3>Datos del Cliente:</h3>
            <div class="user-data">
                <div>
                    <label for="nombre_cliente">Nombre:</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" required>
                </div>
                <div>
                    <label for="apellido_cliente">Apellido:</label>
                    <input type="text" id="apellido_cliente" name="apellido_cliente" required>
                </div>
                <div>
                    <label for="telefono_cliente">Teléfono:</label>
                    <input type="tel" id="telefono_cliente" name="telefono_cliente" required>
                </div>
            </div>

            <h3>Añadir Pedido:</h3>
            <?php
            $current_category = '';
            while ($row = $menu_result->fetch_assoc()) {
                if ($current_category != $row['categoria_plato']) {
                    if ($current_category != '') echo '</table>';
                    $current_category = $row['categoria_plato'];
                    echo "<h3>$current_category</h3><table>";
                    echo "<tr><th>Plato</th><th>Precio</th><th>Cantidad</th></tr>";
                }
                ?>
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="platos[]" value="<?php echo $row['id_plato']; ?>" data-price="<?php echo $row['precio_plato']; ?>">
                            <?php echo $row['nombre_plato']; ?>
                        </label>
                    </td>
                    <td>$<?php echo $row['precio_plato']; ?></td>
                    <td>
                        <input type="number" name="cantidad[<?php echo $row['id_plato']; ?>]" min="1" value="1" disabled>
                    </td>
                </tr>
                <?php
            }
            if ($current_category != '') echo '</table>';
            ?>

            <h3>Total del Pedido: $<span id="totalAmount">0</span></h3>


            <div class="actions">
                <button type="submit" class="btn">Realizar Pedido</button>
                <a href="autor.php" class="btn">Volver a la Página Anterior</a>
            </div>
        </form>
    </main>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 Bocados' Express. Todos los derechos reservados.</p>
        <a href="../logout.php">Cerrar sesión</a>
    </footer>

    <script>
        function calculateTotal() {
            let total = 0;
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const price = parseFloat(checkbox.dataset.price);
                    const quantity = parseInt(checkbox.closest('tr').querySelector('input[type="number"]').value);
                    total += price * quantity;
                }
            });
            document.getElementById('totalAmount').textContent = total.toFixed(0);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('orderForm');
            form.addEventListener('change', (event) => {
                if (event.target.type === 'checkbox') {
                    event.target.closest('tr').querySelector('input[type="number"]').disabled = !event.target.checked;
                }
                calculateTotal();
            });
        });
    </script>
</body>
</html>