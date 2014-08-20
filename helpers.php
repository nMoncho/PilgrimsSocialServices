<?php
include ('funciones.php');

function obtener_juegos($id_juego = null) {
    $conn = crearConnection();
    if ($id_juego == null) {
        $result = mysqli_query($conn, "SELECT ID, NOMBRE, DESCRIPCION FROM JUEGOS
            ORDER BY NOMBRE");
    } else {
        $result = mysqli_query($conn, "SELECT ID, NOMBRE, DESCRIPCION FROM JUEGOS
            WHERE ID = $id_juego");
    }

    $arr_juegos = [];
    while($row = mysqli_fetch_array($result)) {
        $id = intval($row['ID']);
        $nombre = $row['NOMBRE'];
        $descripcion = $row['DESCRIPCION'];

        array_push($arr_juegos
                , array('id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion));
    }

    mysqli_close($conn);
    return $arr_juegos;
}

function obtener_leaderboard($id_leaderboard = null, $id_juego = null) {
    $conn = crearConnection();
    if ($id_leaderboard == null && $id_juego == null) {
        $result = mysqli_query($conn, "SELECT ID, NOMBRE, LIMITE, ES_DEFAULT
            FROM LEADERBOARDS
            ORDER BY NOMBRE");
    } elseif ($id_leaderboard != null && $id_juego == null) {
        $result = mysqli_query($conn, "SELECT ID, NOMBRE, LIMITE, ES_DEFAULT 
            FROM LEADERBOARDS
            WHERE ID = $id_leaderboard");
    } elseif ($id_leaderboard == null && $id_juego != null) {
        $result = mysqli_query($conn, "SELECT ID, NOMBRE, LIMITE, ES_DEFAULT 
            FROM LEADERBOARDS
            WHERE ID_JUEGO = $id_juego 
            ORDER BY NOMBRE" );
    } else {
        $result = mysqli_query($conn, "SELECT ID, NOMBRE, LIMITE, ES_DEFAULT 
            FROM LEADERBOARDS
            WHERE ID = $id_leaderboard AND ID_JUEGO = $id_juego");
    }

    $arr_juegos = [];
    while($row = mysqli_fetch_array($result)) {
        $id = intval($row['ID']);
        $nombre = $row['NOMBRE'];
        $limite = intval($row['LIMITE']);
        $es_default = intval($row['ES_DEFAULT']) == 1;

        array_push($arr_juegos
                , array('id' => $id, 'nombre' => $nombre, 'limite' => $limite
                    , 'es_default' => $es_default));
    }

    mysqli_close($conn);
    return $arr_juegos;
}

function obtener_puntajes($id_leaderboard) {
    
}

?>
