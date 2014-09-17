<?php

include_once 'funciones.php';

function existe_juego($id_juego, $conn = null) {
  $cerrar_conn = false;
  if ($conn == null) {
    $conn = crearConnection();
    $cerrar_conn = true;
  }
  
  $result = mysqli_query($conn, "SELECT COUNT(*) as CANT FROM JUEGOS WHERE ID = $id_juego");
  $row = mysqli_fetch_array($result);
  
  $existe = $row['CANT'] == 1;
  
  if ($cerrar_conn) {
    mysqli_close($conn);
  }
  
  return $existe;
}
?>
