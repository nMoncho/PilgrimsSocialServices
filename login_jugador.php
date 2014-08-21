<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include('funciones.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        if(!isset($_POST['device_id']) || !isset($_POST['game_id']) || !isset($_POST['player_id']) || !isset($_POST['player_uuid'])) {
            throw new Exception("Existen campos faltantes.");
        }

        $id_juego = $_POST['game_id'];
	$id_jugador = $_POST['player_id'];
        $device_id = $_POST['device_id'];
        $uuid_usuario = $_POST['player_uuid'];

        $sesion_arr = loginJugador($id_jugador, $uuid_usuario, $device_id, $id_juego);
        //$sesion_arr = array('id' => 100, 'nombre' => 'Guest 100');

        echo json_encode($sesion_arr);
    } catch (Exception $e) {
        log_message("Error al loguear a un jugador: " . $e->getMessage(), "USUARIOS", "ERROR");
        echo json_encode(array(
            'error' => array(
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
            ),
        ));
    }
}

?>
