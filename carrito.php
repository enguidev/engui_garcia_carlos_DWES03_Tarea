<?php

// Archivo donde el usuario gestiona el carrito y ve sus compras

// Iniciamos la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION["idCliente"])) {
  header("Location: index.php");
  exit;
}

// Hacemos el requerimiento del archivo de conexión a la base de datos
require_once "config/conexion.php";

// Abrimos la conexión y se lo asignamos a la variable $conexion
$conexion = abrirConexionConBDD();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Carrito</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/carritoStyle.css">
</head>

<body>

  <!-- Mostramos el cliente que ha iniciado sesión -->
  <div id="contenedor_carrito">
    <h2>Cliente: <?= $_SESSION["idCliente"] . " - " . $_SESSION["nameCliente"]; ?></h2>

    <!-- Tabla de los artículos -->
    <table>
      <tr>
        <!-- Encabezados -->
        <th>ID</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Aumentar</th>
        <th>Disminuir</th>
      </tr>
      <?php

      // Variable para contar el total de artículos en el carrito
      $totalArticulos = 0;

      // Consulta que obtiene todos los artículos y su cantidad en el carrito del cliente
      /*
         Seleccionamos las columnas de id, nombre y precio de venta de la tabla articulos
         COALESCE(c.car_cantidad, 0) --> función que devuelve el primer valor no nulo. Si 
         car_cantidad no existe (porque el cliente no tiene ese artículo en su carrito)
         devuelve 0.
         Lo renombramos a cantidad (AS cantidad) por legibilidad
         Hacemos un LEFT JOIN con la tabla carritos_compra para que devuelva todos los 
         artículos de la tabla articulos que haya coincidencia en los ids de articulo y 
         carrito artículo y car_cliente con el id del cliente en session.
      */
      $sql = "SELECT a.art_id, a.art_nombre, a.art_precio_venta, 
                 COALESCE(c.car_cantidad, 0) AS cantidad
          FROM articulos a
          LEFT JOIN carritos_compra c 
            ON a.art_id = c.car_articulo 
           AND c.car_cliente = ?";

      $stmt = $conexion->prepare($sql);
      $stmt->bind_param("i", $_SESSION["idCliente"]);
      $stmt->execute();
      $result = $stmt->get_result();

      // Recorremos el resultado
      /*
       Mostramos la id, el nombre, el precio de venta del artículo y la cantidad
       Creamos un enlace para la suma y otro para la resta, que te lleve a 
       actualizar_carrito.php con el id por parámetro en la URL (GET) 
       Además, acumulamos el total de artículos para saber si el carrito está vacío
      */
      while ($fila = $result->fetch_assoc()) {
        $totalArticulos += $fila['cantidad'];
        echo "<tr>
            <td>{$fila['art_id']}</td>
            <td>{$fila['art_nombre']}</td>
            <td>{$fila['art_precio_venta']} €</td>
            <td>{$fila['cantidad']}</td>
            <td><a href='actualizar_carrito.php?accion=sumar&id={$fila['art_id']}'>+1</a></td>
            <td><a href='actualizar_carrito.php?accion=restar&id={$fila['art_id']}'>-1</a></td>
          </tr>";
      }
      ?>
    </table>

    <!-- Botón para comprar que te lleva a compra.php -->
    <?php
    /*
     Si el total de artículos es mayor que 0, mostramos el botón activo
     Si no hay artículos en el carrito, mostramos el botón deshabilitado
     El estilo visual se aplica desde el CSS (button:disabled)
    */
    if ($totalArticulos > 0) {
      echo '<form action="compra.php" method="POST">
              <button type="submit">Comprar</button>
            </form>';
    } else {
      echo '<button type="button" disabled>Comprar</button>';
    }
    ?>

    <hr>

    <!-- tabla de cabeceras de ventas -->
    <table>
      <tr>
        <!-- Encabezados -->
        <th>ID Cabecera</th>
        <th>Fecha</th>
        <th>Número de líneas</th>
        <th>Detalles</th>
      </tr>
      <?php
      // Consulta para obtener las cabeceras de venta del cliente con el número de líneas
      /*
       Seleccionamos el id y fecha de compra de cabeceras_ventas
       Cuantos artículos distintos se compraron en esa cabecera
       Donde el cliente es el id en session
       Lo agrupamos por cabecera
       Ordenamos las compras de más reciente a más antigua
      */
      $sqlCabeceras = "SELECT cv.cab_id, cv.cab_fecha, COUNT(lv.lin_id) as num_lineas
                       FROM cabeceras_ventas cv
                       LEFT JOIN lineas_ventas lv ON cv.cab_id = lv.lin_cabecera
                       WHERE cv.cab_cliente = ?
                       GROUP BY cv.cab_id
                       ORDER BY cv.cab_fecha DESC";

      $stmtCabeceras = $conexion->prepare($sqlCabeceras);
      $stmtCabeceras->bind_param("i", $_SESSION["idCliente"]);
      $stmtCabeceras->execute();
      $resultCabeceras = $stmtCabeceras->get_result();

      // Si hay resultados...
      if ($resultCabeceras->num_rows > 0) {

        // recorremos esos resultados 
        while ($cabecera = $resultCabeceras->fetch_assoc()) {

          /*
          Mostramos el id y fecha de las cabeceras y el número de líneas
          Creamos un enlace (ver) que lleve a compra.php con el id de cabecera 
          por parámetro en la URL (GET)
          */
          echo "<tr>
              <td>{$cabecera['cab_id']}</td>
              <td>{$cabecera['cab_fecha']}</td>
              <td>{$cabecera['num_lineas']}</td>
              <td><a href='compra.php?id={$cabecera['cab_id']}'>Ver</a></td>
            </tr>";
        }

        // Si no hay resultados...
      } else {

        // Mostramos que no hay compras
        echo "<tr><td colspan='4'>No tienes compras anteriores</td></tr>";
      }
      ?>
    </table>

    <!-- Botón para volver al index.php -->
    <form action="index.php" method="GET">
      <button type="submit">Volver</button>
    </form>

  </div>

  <?php
  // Cerramos la conexión
  cerrarConexionConBDD($conexion);
  ?>

</body>

</html>