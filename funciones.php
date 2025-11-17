<?php
// Archivo con las funciones hasheo y existe cliente que usaremos en el login del index.php

// Hacemos el requerimiento del archivo de conexión a la base de datos
require_once "config/conexion.php";

// Función para hashear (md5) en minúscula
function hasheo($password)
{

  // Retorna la contraseña en minúsculas y hasheada por md5
  return md5(strtolower($password));
}


/*
 Función para comprobar si existe ese usuario con esa contraseña.
 Retorna true o false dependiendo si existe o no existe el usuario
 con ese id respectivamente
*/
function existeCliente($conexion, $id, $password)
{

  // Consulta preparada
  $stmt = $conexion->prepare("SELECT cli_id, cli_nombre FROM clientes WHERE cli_id=? AND cli_password=?");

  // En caso de error
  if (!$stmt) {
    die("Error en prepare: " . $conexion->error);
  }

  // Preparamos los parámetros
  $stmt->bind_param("is", $id, $password);

  // Ejecutamos la consulta
  $stmt->execute();


  // Retornamos el resultado
  return $stmt->get_result();
}
