<?php

include('helpers.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        header('Content-Type: application/json');
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : null; 
                
        $arr_juegos = obtener_juegos($id);

        echo json_encode($arr_juegos);    
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
