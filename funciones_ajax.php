<?php
include_once 'funciones.php';
include_once 'funciones_html.php';
include_once 'funciones_jugadores.php';

header('Content-Type: application/json');

try {
  $requested_func = is_get() ? $_GET['req_func'] : $_POST['req_func'];

  $retorno = array();
  if ($requested_func === "obtener_jugador_por_nombre") { // No es escalable, TODO refactorizar para usar get_defined_functions(); (http://php.net/manual/en/function.get-defined-functions.php)
    $retorno = ajax_obtener_id_jugador_por_nombre();
  } elseif ($requested_func === "ajax_buscar_jugador_nombre_autocomplete") {
    $retorno = ajax_buscar_jugador_nombre_autocomplete();
  }
    
  
  echo json_encode($retorno);
} catch (Exception $e) {
  log_message("Error al agregar un puntaje: " . $e->getMessage(), "PUNTAJES", "ERROR");
  echo json_encode(array(
      'error' => array(
          'msg' => $e->getMessage(),
          'code' => $e->getCode(),
      ),
  ));
}

function ajax_obtener_id_jugador_por_nombre() {
  $id = obtener_id_usuario_por_nombre($_GET['nombre_jugador']);
  return $id != null ? array('id' => $id) : null;
}

function ajax_buscar_jugador_nombre_autocomplete() {
  $conn = crearConnection();
  $nombre_jugador = mysqli_escape_string($conn, $_GET['nombre_jugador']);
  $sql_select = "SELECT ID, NOMBRE FROM USUARIOS WHERE NOMBRE LIKE '%$nombre_jugador%'";
  
  $retorno = array();
  $result = mysqli_query($conn, $sql_select);
  while($row = mysqli_fetch_array($result)) {
    array_push($retorno, array('id' => $row['ID'], 'nombre' => $row['NOMBRE']));
  }
  
  return $retorno;
}

?>
