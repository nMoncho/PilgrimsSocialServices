<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once 'funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        if(!isset($_POST['device_id'])) {
            throw new Exception("Existen campos faltantes.");
        }
        
        $nombre_usuario = isset($_POST['name']) ? $_POST['name'] : null;
        $user_action = isset($_POST['user_action']) ? 
            $_POST['user_action'] === "true" : false;
        $device_id = $_POST['device_id'];
        $uuid_usuario = null;
        
        $usuario_arr = crear_jugador($nombre_usuario, $uuid_usuario, $device_id);
        //$usuario_arr = array('id' => 100, 'nombre' => 'Guest 100'
        //, 'uuid' => 'foo bar');
        
        echo json_encode($usuario_arr);    
    } catch (Exception $e) {
        log_message("Error al crear el usuario: " . $e->getMessage(), "USUARIOS", "ERROR");
        echo json_encode(array(
            'error' => array(
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
            ),
        ));
    }
}
?>
