<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        header('Content-Type: application/json');
        
        $id_juego = isset($_POST['id_juego']) ? intval($_POST['id_juego']) : null;
        $id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
        $uuid_usuario = isset($_POST['uuid_usuario']) ? $_POST['uuid_usuario'] : null;
        $id_leaderboard = isset($_POST['id_leaderboard']) ? $_POST['id_leaderboard'] : null;
        $puntaje = isset($_POST['puntaje']) ? intval($_POST['puntaje']) : 0;
        
        $arr_result = agregar_puntaje($id_juego, $id_usuario, $uuid_usuario, $puntaje, $id_leaderboard);
        echo json_encode($arr_result);
    }  catch (Exception $e) {
        log_message("Error al agregar un puntaje: " . $e->getMessage(), "PUNTAJES", "ERROR");
        echo json_encode(array(
            'error' => array(
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
            ),
        ));
    }
}
?>
