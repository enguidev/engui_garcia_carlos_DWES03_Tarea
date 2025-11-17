<?php

// Archivo de inicio (muestra el login)

// Iniciamos la sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Hacemos el requerimiento del archivo de conexión a la base de datos
require_once "config/conexion.php";

// Hacemos el requerimiento del archivo donde tenemos las funciones
require_once "funciones.php";

// Abrimos la conexión y se lo asignamos a la variable $conexion
$conexion = abrirConexionConBDD();

// Variable para el mensaje de error
$mensajeError = "";

// Si hemos recibido el POST del formulario...
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Resultado de comprobar el usuario y contraseña hasheada
  $resultado = existeCliente($conexion, $_POST["cliente"], hasheo($_POST["password"]));

  // Si no hay resultados...
  if ($resultado->num_rows === 0) {

    // Asignamos el literal Contraseña incorrecta a la variable $mensajeError
    $mensajeError = "Contraseña incorrecta";

    // Si hay resultados...
  } else {

    // Recogemos el valor de las columnas
    $fila = $resultado->fetch_assoc();

    // Guardamos el id y el nombre en la session
    $_SESSION["idCliente"] = $fila["cli_id"];
    $_SESSION["nameCliente"] = $fila["cli_nombre"];

    // Nos dirigimos a carrito.php
    header("Location: carrito.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TIENDA</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/loginStyle.css">
  <link rel="stylesheet" href="css/carritoStyle.css">
</head>

<body>
  <h1>TIENDA</h1>

  <form method="POST" id="formulario">
    <div class="desplegable">
      <!-- Mensaje si la contraseña es incorrecta -->
      <?php
      if (!empty($mensajeError)) echo "<p id= 'mensajeError'>$mensajeError</p>" ?>
      <label for="cliente">Cliente:</label>
      <select name="cliente" id="cliente">
        <?php
        // Consulta del id el nombre del cliente en la base de datos
        $sql = "SELECT cli_id, cli_nombre FROM clientes";

        // Recogemos el valor de las columnas
        $result = $conexion->query($sql);

        // Si hay resultados...
        if ($result->num_rows > 0) {

          // Recorremos el array asociativo con las columnas como claves 
          while ($fila = $result->fetch_assoc()) {

            // Creamos una opción del desplegable por cada resultado (con el while)
            echo "<option value='{$fila['cli_id']}'>{$fila['cli_nombre']}</option>";
          }
        }
        ?>

      </select>

    </div>
    <br>
    <label for="password">Contraseña:</label>
    <input type="password" name="password" required>
    <br>
    <button type="submit">Aceptar</button>
  </form>
</body>

</html>

<?php

// Cerramos la conexión con la base de datos
cerrarConexionConBDD($conexion);
?>