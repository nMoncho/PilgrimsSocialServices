<?php
  include ('funciones_html.php');
  include ('funciones_jugadores.php');

  $is_post = $_SERVER['REQUEST_METHOD'] === 'POST';

  if ($is_post && es_usuario_valido()) {
    cargar_usuario_sesion($_POST['username'], $_POST['password']);
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
    $password = $_POST['password'];

    return es_usuario_password_valido($nombre_usuario, $password);
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
