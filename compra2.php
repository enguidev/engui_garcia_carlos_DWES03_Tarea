<?php
// Archivo para confirmar compra y muestra el detalle

// Iniciamos la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION["idCliente"])) {

  // Si no la ha iniciado, volvemos al index
  header("Location: index.php");
  exit;
}

// Verificamos que se haya pasado un ID de cabecera
if (!isset($_GET["id"])) {

  // Si no, nos redirigimos a carrito.php
  header("Location: carrito.php");
  exit;
}

// Hacemos el requerimiento del archivo de conexión a la base de datos
require_once "config/conexion.php";

// Abrimos la conexión
$conexion = abrirConexionConBDD();

// Guardamos en la session la id de la cabecera en una variable
$idCabecera = $_GET["id"];

// Y guardamos la id del cliente en una variable
$idCliente = $_SESSION["idCliente"];

// Obtenemos los datos de la cabecera y verificamos que pertenezca al cliente actual (consulta preparada)
// Buscamos la cabecera cuyo cab_id coincide con el pasado en la URL y que pertenezca al cliente en session
$sql = "SELECT cv.cab_id, cv.cab_fecha, c.cli_id, c.cli_nombre
                FROM cabeceras_ventas cv
                INNER JOIN clientes c ON cv.cab_cliente = c.cli_id
                WHERE cv.cab_id = ? AND cv.cab_cliente = ?";
$stmtCabecera = $conexion->prepare($sql);

// Los parámetros ? serán 2 enteros ("ii") y será $idCabecera y $idCliente
$stmtCabecera->bind_param("ii", $idCabecera, $idCliente);

// Ejecutamos la consulta
$stmtCabecera->execute();

// Asignamos el resultado en al variable $resultCabecera
$resultCabecera = $stmtCabecera->get_result();

// Si no existe la cabecera o no pertenece al cliente, redirigimos a carrito
if ($resultCabecera->num_rows === 0) {
  header("Location: carrito.php");
  exit;
}

// Extraemos la fila encontrada
$cabecera = $resultCabecera->fetch_assoc();

// Obtenemos las líneas de venta de esta cabecera
$sql = "SELECT a.art_nombre, a.art_precio_venta, lv.lin_cantidad
              FROM lineas_ventas lv
              INNER JOIN articulos a ON lv.lin_articulo = a.art_id
              WHERE lv.lin_cabecera = ?";
$stmtLineas = $conexion->prepare($sql);
$stmtLineas->bind_param("i", $idCabecera);
$stmtLineas->execute();
$resultLineas = $stmtLineas->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Detalle de compra</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/carritoStyle.css">
</head>

<body>

  <div id="contenedor_compra">
    <!-- Información del cliente y cabecera -->
    <h2>Detalle de la compra</h2>
    <p><strong>Cliente:</strong> <?= $cabecera['cli_id'] . " - " . $cabecera['cli_nombre'] ?></p>
    <p><strong>ID Cabecera:</strong> <?= $cabecera['cab_id'] ?></p>
    <p><strong>Fecha:</strong> <?= $cabecera['cab_fecha'] ?></p>

    <hr>

    <!-- Tabla con las compras -->
    <h3>Artículos comprados</h3>
    <table>
      <tr>
        <!-- Encabezado -->
        <th>Nombre del artículo</th>
        <th>Precio</th>
        <th>Cantidad</th>
      </tr>
      <?php

      // Variable para el total de la compra
      $total = 0;

      // Recorremos el resultado de las líneas de ventas
      while ($linea = $resultLineas->fetch_assoc()) {

        // Hacemos el producto del precio por la cantidad de cada producto
        //$calculosIndividuales = $linea['art_precio_venta'] * $linea['lin_cantidad'];
        //$total += $calculosIndividuales;
        $total += $linea['art_precio_venta'] * $linea['lin_cantidad'];

        // Lo mostramos en las celdas
        echo "<tr>
            <td>{$linea['art_nombre']}</td>
            <td>{$linea['art_precio_venta']} €</td>
            <td>{$linea['lin_cantidad']}</td>
          </tr>";
      }
      ?>
      <tr>
        <td colspan="2"><strong>TOTAL</strong></td>

        <!-- Mostramos el total con 2 decimales -->
        <td><strong><?= number_format($total, 2) ?> €</strong></td>
      </tr>
    </table>

    <!-- Botón volver -->
    <!-- creamos un botón que cuando le cliquemos vaya a carrito.php -->
    <form action="carrito.php" method="GET">
      <button type="submit">Volver al carrito</button>
    </form>

  </div>

  <?php
  // Cerramos la conexión
  cerrarConexionConBDD($conexion);
  ?>

</body>

</html>