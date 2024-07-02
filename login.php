<?php
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/db_connection.php';

    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $mysqli->real_escape_string($_POST['password']);

    $sql = "SELECT id_usuario, nivel_usuario FROM usuarios WHERE nombre_usuario = '$username' AND contraseña_usuario = '$password'";
    $result = $mysqli->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id_usuario'];
        $_SESSION['user_level'] = $row['nivel_usuario'];

        if ($row['nivel_usuario'] == 'Gestor') {
            header("location: pages/gestor.php");
        } elseif ($row['nivel_usuario'] == 'Autor') {
            header("location: pages/autor.php");
        }
    } else {
        $error = "Usuario o contraseña incorrectos";
    }

    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/Login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="submit" value="Iniciar sesión">
        </form>
        <?php
        if (!empty($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>
        <div class="back-to-home">
    <a href="index.php">Volver a la página principal</a>
</div>
    </div>
</body>
</html>