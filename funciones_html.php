<?php

include('funciones.php');

function listar_juegos() {
    $retorno = array();
    $conn = crearConnection();
    $result = mysqli_query($conn, "SELECT * FROM JUEGOS");
    while ($row = mysqli_fetch_array($result)) {
        array_push($retorno, array('id' => $row['ID'], 'nombre' => $row['NOMBRE']));
    }
    
    return $retorno;
}

function listar_leaderboard($id_juego) {
    $conn = crearConnection();
    $id_juego = intval(mysqli_real_escape_string($conn, $id_juego));
    $result_ldb = mysqli_query($conn, "SELECT * FROM LEADERBOARDS WHERE ID_JUEGO = $id_juego");
    while ($row_ldb = mysqli_fetch_array($result_ldb)) {
        $id_leaderboard = $row_ldb['ID'];
        $nombre_leaderboard = $row_ldb['NOMBRE'];
        $result_scs = mysqli_query($conn, "SELECT * FROM LEADERBOARDS_PUNTAJES WHERE ID_LEADERBOARD = $id_leaderboard AND DEFAULT = 1 ORDER BY PUNTAJE DESC");
        $score_arr = array();
        while ($row_scs = mysqli_fetch_array($result_scs)) {
            array_push($score_arr, array('id' => $row_scs['ID'], 'puntaje' => $row_scs['PUNTAJE']));
        }
        
        return array('id' => $id_leaderboard, 'nombre' => $nombre_leaderboard, 'puntajes' => $score_arr);
    }
}

static $http_codes = array (
  100 => "HTTP/1.1 100 Continue",
  101 => "HTTP/1.1 101 Switching Protocols",
  200 => "HTTP/1.1 200 OK",
  201 => "HTTP/1.1 201 Created",
  202 => "HTTP/1.1 202 Accepted",
  203 => "HTTP/1.1 203 Non-Authoritative Information",
  204 => "HTTP/1.1 204 No Content",
  205 => "HTTP/1.1 205 Reset Content",
  206 => "HTTP/1.1 206 Partial Content",
  300 => "HTTP/1.1 300 Multiple Choices",
  301 => "HTTP/1.1 301 Moved Permanently",
  302 => "HTTP/1.1 302 Found",
  303 => "HTTP/1.1 303 See Other",
  304 => "HTTP/1.1 304 Not Modified",
  305 => "HTTP/1.1 305 Use Proxy",
  307 => "HTTP/1.1 307 Temporary Redirect",
  400 => "HTTP/1.1 400 Bad Request",
  401 => "HTTP/1.1 401 Unauthorized",
  402 => "HTTP/1.1 402 Payment Required",
  403 => "HTTP/1.1 403 Forbidden",
  404 => "HTTP/1.1 404 Not Found",
  405 => "HTTP/1.1 405 Method Not Allowed",
  406 => "HTTP/1.1 406 Not Acceptable",
  407 => "HTTP/1.1 407 Proxy Authentication Required",
  408 => "HTTP/1.1 408 Request Time-out",
  409 => "HTTP/1.1 409 Conflict",
  410 => "HTTP/1.1 410 Gone",
  411 => "HTTP/1.1 411 Length Required",
  412 => "HTTP/1.1 412 Precondition Failed",
  413 => "HTTP/1.1 413 Request Entity Too Large",
  414 => "HTTP/1.1 414 Request-URI Too Large",
  415 => "HTTP/1.1 415 Unsupported Media Type",
  416 => "HTTP/1.1 416 Requested range not satisfiable",
  417 => "HTTP/1.1 417 Expectation Failed",
  500 => "HTTP/1.1 500 Internal Server Error",
  501 => "HTTP/1.1 501 Not Implemented",
  502 => "HTTP/1.1 502 Bad Gateway",
  503 => "HTTP/1.1 503 Service Unavailable",
  504 => "HTTP/1.1 504 Gateway Time-out");

function redirect($path, $code = 200) {
  header($http_codes[$code]);
  header("Location: $path");
}

function cargar_usuario_sesion($nombre_usuario, $password) {
  session_start();
  $_SESSION['principal'] = array("username" => $nombre_usuario, "credentials" => $password, "login_date" => time());
}
?>
