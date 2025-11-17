<?php

/*
 Archivo que comprueba si el artículo ya está en el carrito para actualizarlo 
 y si no, lo inserta (actualiza cantidades (suma o resta artículos) en el carrito)
*/

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

// Abrimos la conexión
$conexion = abrirConexionConBDD();

// Obtenemos el id del cliente desde la sesión
$idCliente = $_SESSION["idCliente"];

// Obtenemos los parámetros de la URL (acción y artículo)
$accion = $_GET["accion"]; // puede ser 'sumar' o 'restar'
$idArticulo = $_GET["id"];

// Consulta para comprobar si el artículo ya existe en el carrito del cliente
$sql = "SELECT car_id, car_cantidad 
        FROM carritos_compra 
        WHERE car_cliente=? AND car_articulo=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idCliente, $idArticulo);
$stmt->execute();
$result = $stmt->get_result();

if ($fila = $result->fetch_assoc()) {
  // Si el artículo ya existe en el carrito, actualizamos la cantidad
  $cantidad = $fila["car_cantidad"];

  // Sumamos o restamos según la acción
  if ($accion == "sumar") {
    $cantidad++;
  }
  if ($accion == "restar" && $cantidad > 0) {
    $cantidad--;
  }

  // Si la cantidad llega a 0, eliminamos el registro.
  /*
   Controlamos que nunca se guarde un número negativo:
     - Si la cantidad es mayor que 0, restamos.
     - Si llega a 0, eliminamos el registro.
     - Así nunca se guarda un valor negativo en la base de datos.
  */
  if ($cantidad == 0) {
    $sqlDelete = "DELETE FROM carritos_compra WHERE car_id=?";
    $stmtDelete = $conexion->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $fila["car_id"]);
    $stmtDelete->execute();
  } else {

    // Actualizamos la cantidad en la tabla carritos_compra
    $sqlUpdate = "UPDATE carritos_compra SET car_cantidad=? WHERE car_id=?";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $cantidad, $fila["car_id"]);
    $stmtUpdate->execute();
  }
} else {

  // Si no existe en el carrito y la acción es sumar, lo insertamos con cantidad 1
  if ($accion == "sumar") {
    $sqlInsert = "INSERT INTO carritos_compra (car_articulo, car_cliente, car_cantidad) VALUES (?,?,1)";
    $stmtInsert = $conexion->prepare($sqlInsert);
    $stmtInsert->bind_param("ii", $idArticulo, $idCliente);
    $stmtInsert->execute();
  }
}

// Cerramos la conexión
cerrarConexionConBDD($conexion);

// Redirigimos de nuevo al carrito
header("Location: carrito.php");
exit;
