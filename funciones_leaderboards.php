<?php

include_once 'funciones.php';
include_once 'funciones_juegos.php';

static $limite_default = 100;

function listar_leaderboard($id_juego, $incluir_puntajes = false) {
  $resultado = array();
  $conn = crearConnection();
  $id_juego = intval(mysqli_real_escape_string($conn, $id_juego));
  $result_ldb = mysqli_query($conn, "SELECT * FROM LEADERBOARDS WHERE ID_JUEGO = $id_juego");
  while ($row_ldb = mysqli_fetch_array($result_ldb)) {
    $id_leaderboard = $row_ldb['ID'];
    $nombre_leaderboard = $row_ldb['NOMBRE'];
    $es_default = $row_ldb['ES_DEFAULT'] == 1;
    $puntajes = array();
    if ($incluir_puntajes) {
      $puntajes = obtener_leaderboard($id_leaderboard);
    }

    array_push($resultado
            , array('id' => $id_leaderboard, 'nombre' => $nombre_leaderboard
                , 'es_default' => $es_default, 'puntajes' => $puntajes));
  }
  
  return $resultado;
}

/**
 * Crear un nuevo leaderboard para el juego especificado. Si el nuevo leaderboard
 * va a ser el por defecto, y ya existe uno para el juego, se anulara el mas antiguo.
 * @param String $nombre_leaderboard nombre del nuevo leaderboard
 * @param int $limite limite de puntajes que puede tener el leaderboard (p ej, TOP 100)
 * @param bool $default si el nuevo leaderboard deber ser default
 * @param int $id_juego id del juego al que pertenece el leaderboard
 */
function crear_leaderboard($nombre_leaderboard, $limite, $default, $id_juego) {
  $conn = crearConnection();
  $nombre_leaderboard = mysqli_escape_string($conn, $nombre_leaderboard);
  $limite = intval(mysqli_escape_string($conn, $limite));
  $limite = $limite > 0 ? $limite : $limite_default;
  $default = is_bool($default) ? $default : ($default == 1 || $default === "true" || $default === "TRUE");
  $id_juego = intval(mysqli_escape_string($conn, $id_juego));
  
  if (!existe_juego($id_juego)) {
    throw new Exception("No existe el juego $id_juego");
  }
  
  if ($default && tiene_leaderboard_defecto($id_juego)) {
    $leaderboard_defecto = obtener_leaderboard_defecto($id_juego);
    actualizar_leaderboard($leaderboard_defecto['id'], $leaderboard_defecto['nombre']
            , $leaderboard_defecto['limite'], false);
  }
  
  $sql_default = $default ? 1 : 0;
  $sql_insert = "INSERT INTO LEADERBOARDS (NOMBRE, LIMITE, ES_DEFAULT, ID_JUEGO) 
    VALUES ('$nombre_leaderboard', $limite, $sql_default, $id_juego)";
  if(mysqli_query($conn, $sql_insert)) {
    $id = mysqli_insert_id($conn);
    $leaderboard = array('id' => $id, 'nombre' => $nombre_leaderboard, 'limite' => $limite
            , 'es_default' => $default);
    mysqli_commit($conn);
    mysqli_close($conn);
    
    return $leaderboard;
  } else {
    mysqli_close($conn);
    throw new Exception("Error al tratar de crear leaderboard (SQL)");
  }
}

function obtener_leaderboard($id_leaderboard, $incluir_puntajes = false, $conn = null) {
  $leaderboard = null;
  $close_conn = false;
  if ($conn == null) {
    $conn = crearConnection();
    $close_conn = true;
  }
  $id_leaderboard = intval(mysqli_escape_string($conn, $id_leaderboard));
  $result = mysqli_query($conn, "SELECT * FROM LEADERBOARDS WHERE ID = $id_leaderboard");
  while ($row = mysqli_fetch_array($result)) {
    $leaderboard = array('id' => $row['ID'], 'nombre' => $row['NOMBRE']
        , 'limite' => $row['LIMITE'], 'es_default' => $row['ES_DEFAULT'] == 1
        , 'puntajes' => array());
    if ($incluir_puntajes) {
      $puntajes = obtener_puntajes($id_leaderboard, $conn);
      $leaderboard['puntajes'] = $puntajes;
    }
  }
  
  if ($close_conn) {
    mysqli_close($conn);
  }
  
  return $leaderboard;
}

function obtener_puntajes($id_leaderboard, $conn = null) {
  $puntajes = array();
  $close_conn = false;
  if ($conn == null) {
    $conn = crearConnection();
    $close_conn = true;
  }
  
  $result = mysqli_query($conn, "SELECT * FROM LEADERBOARD_PUNTAJES 
    WHERE ID_LEADERBOARD = $id_leaderboard ORDER BY PUNTAJE DESC");
  while ($row = mysqli_fetch_array($result)) {
    array_push($puntajes, array('id' => $row['ID'], 'puntaje' => $row['PUNTAJE']
            , 'fecha' => $row['FECHA'], 'id_jugador' => $row['ID_USUARIO']));
  }
  
  if ($close_conn) {
    mysqli_close($conn);
  }
  
  return $puntajes;
}

function obtener_leaderboard_defecto($id_juego, $incluir_puntajes = false) {
  $conn = crearConnection();
  $id_juego = intval(mysqli_escape_string($conn, $id_juego));
  $result = mysqli_query($conn, "SELECT ID FROM LEADERBOARDS 
    WHERE ID_JUEGO = $id_juego AND ES_DEFAULT = 1");
  while ($row = mysqli_fetch_array($result)) {
    $leaderboard = obtener_leaderboard($row['ID'], $incluir_puntajes, $conn);
    mysqli_close($conn);
    return $leaderboard;
  }
  
  return null;
}

/**
 * Verifica si un juego tiene un leaderboard por defecto
 * @param int $id_juego
 * @return boolean true si tiene leaderboard por defecto
 */
function tiene_leaderboard_defecto($id_juego) {
  return obtener_leaderboard_defecto($id_juego) != null;
}

function actualizar_leaderboard($id_leaderboard, $nombre_leaderboard, $limite, $default) {
  $conn = crearConnection();
  $id_leaderboard = intval(mysqli_escape_string($conn, $id_leaderboard));
  $nombre_leaderboard = mysqli_escape_string($conn, $nombre_leaderboard);
  $limite = intval(mysqli_escape_string($conn, $limite));
  $limite = $limite > 0 ? $limite : $limite_default;
  $default = is_bool($default) ? $default : ($default == 1 || $default === "true" || $default === "TRUE");
  
  $sql_default = $default ? 1 : 0;
  $sql_update = "UPDATE LEADERBOARDS SET NOMBRE = '$nombre_leaderboard'
    , LIMITE = $limite, ES_DEFAULT = $sql_default WHERE ID = $id_leaderboard";
  $exito = mysqli_query($conn, $sql_update);

  mysqli_commit($conn);
  mysqli_close($conn);
  
  return $exito;
}

function eliminar_leaderboard($id_leaderboard) {
  $conn = crearConnection();
  $id_leaderboard = intval(mysqli_escape_string($conn, $id_leaderboard));
  $sql_delete = "DELETE FROM LEADERBOARDS WHERE ID = $id_leaderboard";
  $exito = mysqli_query($conn, $sql_delete);

  mysqli_commit($conn);
  mysqli_close($conn);
  
  return $exito;
}

function crear_puntaje($id_leaderboard, $id_jugador, $puntaje) {
  $conn = crearConnection();
  $id_leaderboard = intval(mysqli_escape_string($conn, $id_leaderboard));
  $id_jugador = intval(mysqli_escape_string($conn, $id_jugador));
  $puntaje = intval(mysqli_escape_string($conn, $puntaje));
  
  $sql_insert = "INSERT INTO LEADERBOARD_PUNTAJES(PUNTAJE, ID_LEADERBOARD, FECHA, ID_USUARIO)
    VALUES ($puntaje, $id_leaderboard, SYSDATE(), $id_jugador)";
  $exito = mysqli_query($conn, $sql_insert);
  
  mysqli_commit($conn);
  mysqli_close($conn);
  
  return $exito;
}

?>
