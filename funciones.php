<?php
define('DB_NAME', 'pligrims');
define('DB_USER', 'pilgrims');
define('DB_PASS', 'hunter');

function crearConnection() {
    $conn = mysqli_connect('localhost', constant('DB_USER'), constant('DB_PASS'), constant('DB_NAME'));
    if (mysqli_connect_errno()) {
        throw new Exception('DB Connection error ' . mysqli_error($conn));
    }
    mysqli_autocommit($conn, FALSE);
    
    return $conn;
}

function crearUsuario($nombre_usuario, $uuid_usuario, $device_id) {
    if ($uuid_usuario == null) {
        $uuid_usuario = uniqid("pilgrims_");
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

function loginJugadorManual($nombre_usuario, $password_usuario, $device_id, $id_juego) {
    if ($id_juego == null) {
        throw new Exception("El usuario $nombre_usuario esta tratando de ingresar a un juego con id nulo.");
    } elseif ($nombre_usuario == null || $password_usuario) {
        throw new Exception("Un usuario esta loguearse en el juego $id_juego sin especificar su nombre o password");
    } elseif (!checkUsuarioPassword($nombre_usuario, $password_usuario)) {
        throw new Exception("No coincide el usuario $nombre_usuario con su password $password_usuario");
    }

}

function loginJugadorAutomatico($id_usuario, $uuid_usuario, $device_id, $id_juego) {
	if ($id_juego == null) {
        throw new Exception("El usuario $id_usuario/$uuid_usuario esta tratando ingresar a un juego con id nulo.");
    } elseif ($id_usuario == null) {
        throw new Exception("Un usuario esta loguearse en el juego $id_juego sin especificar su ID");
    } elseif (!checkUsuario($id_usuario, $uuid_usuario)) {
        throw new Exception("No coincide el usuario $id_usuario con su UUID $uuid_usuario");
    }

	$conn = null;
	try{
		$conn = crearConnection();
		$mysqltime = date ("Y-m-d H:i:s", new DateTime('NOW'));
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

function checkUsuario($id_usuario, $uuid_usuario) {
    $es_valido = false;
    if($id_usuario != null && $uuid_usuario != null) {
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

function obtenerIdUsuarioPorNombre($nombre_usuario, $conn) {
	$cerrar_conn = false;
	if ($conn == null) {
		$conn = crearConnection();
		$cerrar_conn = true;
	}
	
	
}

function checkUsuarioPassword($nombre_usuario, $password_usuario) {
    $es_valido = false;
    if($nombre_usuario != null && $password_usuario != null) {
        $conn = crearConnection();
		$sql_nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
        $sql_password_usuario = mysqli_real_escape_string($conn, $password_usuario);

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

function agregar_puntaje($id_juego, $id_usuario, $uuid_usuario, $puntaje, $id_leaderboard) {
    $arr_usuario = null;
    if ($id_juego == null) {
        throw new Exception("El usuario $id_usuario/$uuid_usuario esta tratando de registrar un puntaje para un juego con id nulo.");
    } elseif ($id_usuario == null) {
        log_message("Un usuario esta tratando de agregar un puntaje sin registrarse antes.", "PUNTAJES");
        $nombre_usuario = isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : null; 
        $arr_usuario = crearUsuario($nombre_usuario, $uuid_usuario);
        $id_usuario = intval($arr_usuario["id"]);
    } elseif (!checkUsuario($id_usuario, $uuid_usuario)) {
        throw new Exception("No coincide el usuario $id_usuario con su UUID $uuid_usuario");
    }
        
    $conn = null;
    try {
        $conn = crearConnection();
        $id_juego = intval($id_juego);
        $id_usuario = intval($id_usuario);
        $puntaje = intval($puntaje);

        if ($id_leaderboard == null) {
            $id_leaderboard = buscar_leaderboard_defecto($id_juego, $conn);
        }
        $id_leaderboard = intval($id_leaderboard);
        
        $sql_insert = "INSERT INTO LEADERBOARD_PUNTAJES (PUNTAJE, ID_LEADERBOARD, FECHA, ID_USUARIO) 
            VALUES ($puntaje, $id_leaderboard, SYSDATE(), $id_usuario)";
        
        if (!mysqli_query($conn, $sql_insert)) {
            throw new Exception(mysqli_error($conn));
        }
        
        $id_puntaje = mysqli_insert_id($conn);
        if ($arr_usuario == null) {
            return array('id' => $id_puntaje, 'puntaje' => $puntaje);
        } else {
            return array('id' => $id_puntaje, 'puntaje' => $puntaje, 'usuario' => $arr_usuario);
        }
        mysqli_commit($conn);
    } catch (Exception $e) {
        if ($conn != null) {
            mysqli_rollback($conn);
        }
        throw $e;
    }
}

function buscar_leaderboard_defecto($id_juego, $conn) {
    if ($conn == null) {
        $conn = crearConnection();
    }
    
    $result = mysqli_query($conn, "SELECT ID AS ID_LEADERBOARD FROM LEADERBOARDS 
            WHERE ID_JUEGO = $id_juego AND ES_DEFAULT = 1");
    
    $id_leaderboard = null;
    while($row = mysqli_fetch_array($result)) {
        $id_leaderboard = $row['ID_LEADERBOARD'];
    }
    if ($id_leaderboard == null) {
        throw new Exception("No existe leaderboard default para juego $id_juego");
    }
    
    return $id_leaderboard;
}

function log_message($message, $log_name = 'SYSTEM', $log_level = 'INFO') {
    $conn = null;
    try {
        $conn = crearConnection();
        
        if (($remote_address = $_SERVER['REMOTE_ADDR']) == '') {
            $remote_address = "REMOTE_ADDR UNKNONW";
        }
        
        if (($requet_uri = $_SERVER['REQUEST_URI']) == '') {
            $requet_uri = "REQUEST_URI UNKNONW";
        }
        
        $message = mysqli_real_escape_string($conn, $message);
        $remote_address = mysqli_real_escape_string($conn, $remote_address);
        $requet_uri = mysqli_real_escape_string($conn, $requet_uri);
        $log_name = mysqli_real_escape_string($conn, $log_name);
        $log_level = mysqli_real_escape_string($conn, $log_level);
        
        $insert_sql = "INSERT INTO LOG_TABLE(REMOTE_ADDRESS, REQUEST_URI, MESSAGE, LOG_NAME, LOG_LEVEL) 
            VALUES ('$remote_address', '$requet_uri', '$message', '$log_name', '$log_level')";
        
        if (!mysqli_query($conn, $insert_sql)) {
            throw new Exception(mysqli_error($conn));
        }
        
        mysqli_commit($conn);
        mysqli_close($conn);
    } catch (Exception $e) {
        if ($conn != null) {
            mysqli_rollback($conn);
        }
    }
}
?>
