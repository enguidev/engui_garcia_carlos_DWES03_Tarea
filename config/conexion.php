<?php

// Archivo de configuración y conexión a la base de datos

// Definimos las constantes para la conexión con la BDD
define('DB_HOST', 'localhost'); // Host
define('DB_NAME', 'tienda-tarea'); // Nombre de la BDD
// define('DB_USER', 'alumno'); // Nombre de usuario
define('DB_USER', 'root'); // Nombre de usuario
// define('DB_PASSWORD', 'alumno'); // Password
define('DB_PASSWORD', ''); // Password


// Función para abrir la conexión
function abrirConexionConBDD()
{
  // Creamos la conexión conexión
  $conexion = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  // Verificamos la conexión
  if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
  }

  // Establecer charset UTF-8 para evitar problemas con caracteres especiales
  $conexion->set_charset("utf8");
  // $conexion->set_charset("utf8mb4"); // Para español básico + emojis y caracteres raros

  return $conexion;
}

/* 
Función para cerrar la conexión
Podríamos usar $conexion->close(); en el código, pero lo uso por buenas 
prácticas, consistencia con el código y verificación si existe la BDD
*/
function cerrarConexionConBDD($conexion)
{
  if ($conexion) $conexion->close();
}
