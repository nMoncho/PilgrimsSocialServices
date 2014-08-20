<?php

include('helpers.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        header('Content-Type: application/json');
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $id_juego = isset($_GET['id_juego']) ? intval($_GET['id_juego']) : null;
                
        $arr_leaderboards = obtener_leaderboard($id, $id_juego);

        echo json_encode($arr_leaderboards);    
    } catch (Exception $e) {
        log_message("Error al consultar los juegos: " . $e->getMessage(), "JUEGOS", "ERROR");
        echo json_encode(array(
            'error' => array(
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
            ),
        ));
    }
}
?>
