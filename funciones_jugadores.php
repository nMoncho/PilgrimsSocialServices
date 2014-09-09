<?php

include ('funciones.php');

function listar_jugadores($count = 0, $offset = 0) {
  $resultado = array();
  if ($count < 0) {
    $count = 0;
  }
  if ($offset < 0) {
    $offset = 0;
  }
  $sql_query = "SELECT * FROM USUARIOS WHERE ES_JUGADOR = 1";
  if ($count > 0) {
    $sql_query = $sql_query . " LIMIT $offset, $count";
  }
  $conn = crearConnection();
  $result = mysqli_query($conn, $sql_query);
  while ($row = mysqli_fetch_array($result)) {
    array_push($resultado
            , array('id' => $row['ID'], 'nombre' => $row['NOMBRE'])); // TODO agregar todos los campos
  }

  return $result;
}

function crear_usuario($nombre_usuario, $password) {
  $conn = crearConnection();
  $result = array();
  $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
  $password = mysqli_real_escape_string($conn, $password);
  $uuid_usuario = crear_pilgrim_uuid();
  
  $insert = "INSERT INTO USUARIOS(NOMBRE, PASSWORD, UUID, ES_JUGADOR) 
    VALUES ('$nombre_usuario', '$password', '$uuid_usuario', 0)";
  if (mysqli_query($conn, $insert)) {
    $id = mysql_insert_id();
    $result = array('id' => $id, 'nombre' => $nombre_usuario, 'password' => $password);
  }

  mysqli_commit($conn);
  mysqli_close($conn);

  return $result;
}

function crear_jugador($nombre_usuario, $uuid_usuario, $device_id) {
  if ($uuid_usuario == null) {
    $uuid_usuario = crear_pilgrim_uuid();
  }

  $conn = crearConnection();

  $registro_completo = false;
  if ($nombre_usuario != null) {
    $nombre_usuario = mysqli_real_escape_string($conn, trim($nombre_usuario));
    $registro_completo = true;
  } else {
    $result = mysqli_query($conn, "SELECT NEXTVAL('GUEST_SEQ') AS SEQ");
    $row = mysqli_fetch_array($result);

    $nombre_usuario = 'Guest ' . $row['SEQ'];
    mysqli_free_result($result);
  }

  log_message("Creando usuario $nombre_usuario/$uuid_usuario", "USUARIOS", "DEBUG");
  if ($registro_completo) {
    $sql_insert = "INSERT INTO USUARIOS (NOMBRE, UUID, FECHA_ALTA, FECHA_REGISTRO, DEVICE_ID) 
            VALUES ('$nombre_usuario', '$uuid_usuario', SYSDATE(), SYSDATE(), '$device_id')";
  } else {
    $sql_insert = "INSERT INTO USUARIOS (NOMBRE, UUID, FECHA_ALTA, DEVICE_ID) 
            VALUES ('$nombre_usuario', '$uuid_usuario', SYSDATE(), '$device_id')";
  }

  if (!mysqli_query($conn, $sql_insert)) {
    throw new Exception(mysqli_error($conn));
  }
  $id_usuario = mysqli_insert_id($conn);
  mysqli_commit($conn);
  mysqli_close($conn);

  return array('id' => $id_usuario, 'nombre' => $nombre_usuario
      , 'uuid' => $uuid_usuario);
}

function login_jugador_manual($nombre_usuario, $password_usuario, $device_id, $id_juego) {
  if ($id_juego == null) {
    throw new Exception("El usuario $nombre_usuario esta tratando de ingresar a un juego con id nulo.");
  } elseif ($nombre_usuario == null || $password_usuario) {
    throw new Exception("Un usuario esta loguearse en el juego $id_juego sin especificar su nombre o password");
  } elseif (!es_usuario_password_valido($nombre_usuario, $password_usuario)) {
    throw new Exception("No coincide el usuario $nombre_usuario con su password $password_usuario");
  }
  $id_jugador = obtener_id_usuario_por_nombre($nombre_usuario);
  return crear_sesion_juego($id_jugador, $device_id, $id_juego);
}

function login_jugador_automatico($id_jugador ,$uuid_jugador, $device_id, $id_juego) {
  if ($id_juego == null) {
    throw new Exception("El jugador $id_jugador/$uuid_jugador esta tratando ingresar a un juego con id nulo.");
  } elseif ($id_jugador == null) {
    throw new Exception("Un jugador esta loguearse en el juego $id_juego sin especificar su ID");
  } elseif (!es_jugador_valido($id_jugador, $uuid_jugador)) {
    throw new Exception("No coincide el usuario $id_jugador con su UUID $uuid_jugador");
  }
  
  return crear_sesion_juego($id_jugador, $device_id, $id_juego);
}

/**
 * Crea un sesion de juego para un jugador en un juego en un dispositivo
 * @param type $id_jugador id del jugador que inicia la sesion
 * @param type $device_id id del dispositivo donde se inicia la sesion
 * @param type $id_juego id del juego
 * @return type objecto de sesion de juego
 * @throws Exception en caso de error sql
 */
function crear_sesion_juego($id_jugador, $device_id, $id_juego) {
  $conn = null;
  try {
    $conn = crearConnection();
    $mysqltime = date("Y-m-d H:i:s", new DateTime('NOW'));
    $sql_insert = "INSERT INTO SESIONES_JUEGO(ID_JUEGO, ID_JUGADOR, DEVICE_ID, FECHA_INICIO) 
			VALUES ($id_juego, $id_jugador, '$device_id', SYSDATE())";
    if (!mysqli_query($conn, $sql_insert)) {
      throw new Exception(mysqli_error($conn));
    }
    $id_sesion = mysqli_insert_id($conn);
    mysqli_commit($conn);
    mysqli_close($conn);

    return array('id' => $id_sesion, 'fechaInicio' => $mysqltime);
  } catch (Exception $e) {
    if ($conn != null) {
      mysqli_rollback($conn);
    }
    throw $e;
  }
}

/**
 * Obtiene el Id de un usuario dado su nombre.
 * @param type $nombre_usuario nombre del usuario al buscar.
 * @param type $conn parametro opcional, para reutilizar conexion si fuera deseado.
 * @return type id del usuario, null si no encontro nada
 */
function obtener_id_usuario_por_nombre($nombre_usuario, $conn = null) {
  $cerrar_conn = false;
  if ($conn == null) {
    $conn = crearConnection();
    $cerrar_conn = true;
  }
  $nombre_usuario = mysqli_escape_string($conn, $nombre_usuario);
  $result = mysqli_query($conn, "SELECT ID FROM USUARIOS WHERE NOMBRE = '$nombre_usuario'");
  while($row = mysqli_fetch_array($result)) {
    return $row['ID'];
  }
  
  return null;
}

/**
 * Verifica si un jugador es valido dado su ID/UUID.
 * @param type $id_usuario id del jugador
 * @param type $uuid_usuario uuid del jugador
 * @return type true si es valido, false en caso contrario
 */
function es_jugador_valido($id_usuario, $uuid_usuario) {
  $es_valido = false;
  if ($id_usuario != null && $uuid_usuario != null) {
    $conn = crearConnection();
    $sql_uuid_usuario = mysqli_real_escape_string($conn, $uuid_usuario);

    $result = mysqli_query($conn, "SELECT COUNT(*) AS CANT FROM USUARIOS 
            WHERE ID = $id_usuario AND UUID = '$sql_uuid_usuario'");
    $row = mysqli_fetch_array($result);

    $es_valido = $row['CANT'] == 1;
    if ($row['CANT'] > 1) {
      $cant = $row['CANT'];
      log_message("La busqueda del usuario $id_usuario/$sql_uuid_usuario arrojo $cant resultados", "USUARIOS", "WARN");
    }
    mysqli_free_result($result);
  }

  return $es_valido;
}

/**
 * Verifica si un usuario/jugador es valido dado su nombre y password. Se puede
 * usar para ambos tipos de personas
 * @param type $nombre_usuario nombre del usuario/jugador
 * @param type $password password del usuario/jugador
 * @return type true si es valido, false en caso contrario.
 */
function es_usuario_password_valido($nombre_usuario, $password) {
  $es_valido = false;
  if ($nombre_usuario != null && $password != null) {
    $conn = crearConnection();
    $sql_nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
    $sql_password_usuario = mysqli_real_escape_string($conn, $password);

    $result = mysqli_query($conn, "SELECT COUNT(*) AS CANT FROM USUARIOS 
            WHERE NOMBRE = $sql_nombre_usuario AND PASSWORD = '$sql_password_usuario'");
    $row = mysqli_fetch_array($result);

    $es_valido = $row['CANT'] == 1;
    if ($row['CANT'] > 1) {
      $cant = $row['CANT'];
      log_message("La busqueda del usuario $sql_nombre_usuario/$sql_password_usuario arrojo $cant resultados", "USUARIOS", "WARN");
    }
    mysqli_free_result($result);
  }

  return $es_valido;
}

/**
 * Actualiza un usuario/jugador en la base de datos.
 * @param type $id id del jugador/usuario a actualizar,
 * @param type $nombre_usuario nuevo nombre del jugador/usuario,
 * @param type $password nuevo password del jugador/usuario.
 * @return boolean true si pudo actualizar, false en caso contrario.
 */
function actualizar_usuario($id, $nombre_usuario, $password) {
  $conn = crearConnection();
  $id = intval(mysqli_real_escape_string($conn, $id));
  $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
  $password = mysqli_real_escape_string($conn, $password);

  $update = "UPDATE USUARIOS SET NOMBRE = '$nombre_usuario', PASSWORD = '$password' WHERE ID = $id";
  mysqli_commit($conn);
  $retorno = mysqli_query($conn, $update);
  mysqli_close($conn);
  
  return $retorno;
}

/**
 * Elimina un usuario por su id
 * @param type $id_usuario id del usuario/jugador a eliminar
 * @return boolean true si lo pudo eliminar, false en caso contrario.
 */
function eliminar_usuario($id_usuario) {
  $conn = crearConnection();
  $id_usuario = intval(mysqli_real_escape_string($conn, $id_usuario));

  $delete = "DELETE FROM USUARIOS WHERE ID = $id_usuario";
  mysqli_commit($conn);
  $retorno = mysqli_query($conn, $delete);
  mysqli_close($conn);
  
  return $retorno;
}

/**
 * Obtiene un usuario por su nombre
 * @param type $nombre_usuario
 * @return null
 */
function obtener_usuario($nombre_usuario) {
  $conn = crearConnection();
  $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);

  $result = mysqli_query("SELECT * FROM USUARIOS WHERE NOMBRE = '$nombre_usuario'");
  while ($row = mysqli_fetch_array($result)) {
    return array('id' => $row['ID'], 'nombre' => $row['NOMBRE'], 'password' => $row['PASSWORD']);
  }

  return null;
}

?>
