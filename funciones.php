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

function crear_pilgrim_uuid() {
  return uniqid("pilgrims_");
}

function agregar_puntaje($id_juego, $id_usuario, $uuid_usuario, $puntaje, $id_leaderboard) {
    $arr_usuario = null;
    if ($id_juego == null) {
        throw new Exception("El usuario $id_usuario/$uuid_usuario esta tratando de registrar un puntaje para un juego con id nulo.");
    } elseif ($id_usuario == null) {
        log_message("Un usuario esta tratando de agregar un puntaje sin registrarse antes.", "PUNTAJES");
        $nombre_usuario = isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : null; 
        $arr_usuario = crear_jugador($nombre_usuario, $uuid_usuario);
        $id_usuario = intval($arr_usuario["id"]);
    } elseif (!es_jugador_valido($id_usuario, $uuid_usuario)) {
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
