<?php
  include ('funciones_html.php');

  $is_post = $_SERVER['REQUEST_METHOD'] === 'POST';

  if ($is_post && es_usuario_valido()) {
    redirect("home.php");
  }

  // TODO should throw exceptions, not false
  function es_usuario_valido() {
    if (!isset($_POST['username'])) {
       return false;
    } elseif (!isset($_POST['password'])) {
       return false;
    }

    $nombre_usuario = $_POST['username'];
    $password = $_POST['password']);

    return es_usuario_valido_db($nombre_usuario, $password);
  }

  // TODO refactor, move to funciones_html.php file
  function es_usuario_valido_db($nombre_usuario, $password) {
    $conn = crearConnection();
    $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
    $password = mysqli_real_escape_string($conn, $password);

    $result = mssql_query('SELECT COUNT(*) AS CANT FROM USUARIOS WHERE NOMBRE = $nombre_usuario AND PASSWORD = $password');
    $row = mssql_fetch_array($result);
    return row['CANT'] >= 1;
  }

  function obtener_usuario($nombre_usuario) {
    $conn = crearConnection();
    $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);

    $result = mssql_query("SELECT * FROM USUARIOS WHERE NOMBRE = '$nombre_usuario'");
    while ($row = mssql_fetch_array($result)) {
      return array('id' => $row['ID'], 'nombre' => $row['NOMBRE'], 'password' => $row['PASSWORD']);
    }

    return null;
  }

  function crear_usuario($nombre_usuario, $password) {
    $conn = crearConnection();
    $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
    $password = mysqli_real_escape_string($conn, $password);

    $insert = "INSERT INTO USUARIOS(NOMBRE, PASSWORD) VALUES ('$nombre_usuario', '$password')";
    if (mysqli_query($conn, $insert)) {
      $id = mysql_insert_id();
      return array('id' => $id, 'nombre' => $nombre_usuario, 'password' => $password);
    } else {
      return array();
    }
  }

  function actualizar_usuario($id, $nombre_usuario, $password) {
    $conn = crearConnection();
    $id = intval(mysqli_real_escape_string($conn, $id));
    $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
    $password = mysqli_real_escape_string($conn, $password);

    $delete = "UPDATE USUARIOS SET NOMBRE = '$nombre_usuario', PASSWORD = '$password' WHERE ID = $id";
    if (!mysqli_query($conn, $update)) {
      return false;// TODO throw error
    }
  }

  function eliminar_usuario($id) {
    $conn = crearConnection();
    $id = intval(mysqli_real_escape_string($conn, $id));
    $nombre_usuario = mysqli_real_escape_string($conn, $nombre_usuario);
    $password = mysqli_real_escape_string($conn, $password);

    $delete = "DELETE FROM USUARIOS WHERE ID = $id";
    if (!mysqli_query($conn, $delete)) {
      return false;// TODO throw error
    }
  }
?>
<html>
<head>
<title>Pilgrim's Social Services Login</title>
</head>
  <form action="login_admin.php" method="POST" >
    <input type="text" name="username" />
    <input type="password" name="password" />
    <input type="submit" value="Ingresar" />
  </form>
</body>
</html>
