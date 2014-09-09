<?php
include('funciones_html.php');

if (is_post() && isset($_POST['crear_leaderboard'])) {
  $id_juego = intval($_POST['id_juego']);
  $nombre_leaderboard = $_POST['nombre_leaderboard'];
  $limite_leaderboard = intval($_POST['limite_leaderboard']);
  $default = isset($_POST['es_default']);
  
  $leaderboard = crear_leaderboard($nombre_leaderboard, $limite_leaderboard, $default, $id_juego);
  redirect("gestionar_leaderboards.php?id_juego=$id_juego&id_leaderboard=" . $leaderboard['id']);
}

if (is_post() && isset($_POST['guardar_puntaje'])) {
  $id_juego = inval($_POST['id_juego']);
  $id_leaderboard = intval($_POST['id_leaderboard']);
  $id_jugador = intval($_POST['id_jugador']);
  $puntaje = intval($_POST['puntaje']);
  
  crear_puntaje($id_leaderboard, $id_jugador, $puntaje);
  redirect("gestionar_leaderboards.php?id_juego=$id_juego&id_leaderboard=" . $leaderboard['id']);
}
?>
<html>
  <head>
    <title>Mostrar leaderboards</title>
  </head>
  <body>
    <div>Seleccionar juego: 
      <form id="buscarLeaderboard" action="mostrar_leaderboards.php" method="get">
        <label>Juego: </label>
        <select id="juego" name="id_juego">
          <option>Seleccionar opcion</option>
          <?php
          $juegos = listar_juegos();
          foreach ($juegos as &$juego) {
            echo "<option value='" . $juego['id'] . "' >"
            . $juego['nombre'] . "</option>\n";
          }
          ?>
        </select>
        <?php
        echo "Cant. Juegos: " . count($juegos);
        ?>
        <br/>
        <label>Leaderboard: </label>
        <select id="leaderboard" name="id_leaderboard">
          <option>Seleccionar opcion</option>
          <?php
          if (isset($_GET['id_juego'])) {
            $id_juego = intval($_GET['id_juego']);
            $leaderboards = listar_leaderboard($id_juego);
            foreach ($leaderboard as &$leaderboards) {
              echo "<option value='" . $leaderboard['id'] . "' >"
              . $leaderboard['nombre'] . "</option>\n";
            }
          }
          ?>
        </select>
        <input type="submit" value="Buscar" />
      </form>
    </div>
    <div>
      <?php
      if (is_get() && isset($_GET['id_juego']) && isset($_GET['id_leaderboard'])) {
        $leaderboard = listar_leaderboard(intval($_GET['id_juego']), intval($_GET['id_leaderboard']));
        $scores = $leaderboard['puntajes'];
        echo "Juego: " . $leaderboard['nombre'] . "(" . $leaderboard['id'] . ")";
      }
      ?>
      <table id="resultado" style="border: 1px solid black;<?php if (!$leaderboard) {
        echo "display: none;";
      } ?>" >
        <thead>
          <tr>
            <th>Puntaje</th>
            <th>Fecha</th>
            <th>Jugador</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($leaderboard) {
            foreach ($scores as &$score) {
              echo "<tr>\n";
              echo "<td>" . $score['PUNTAJE'] . "</td>";
              echo "<td></td>";
              echo "<td></td>";
              echo "</tr>\n";
              echo "<option value='" . $juego['id'] . "' >"
              . $juego['nombre'] . "</option>\n";
            }
          }
          ?>
        </tbody>
      </table>
    </div>
    <div>
      <form id="crearLeaderboard" action="gestionar_leaderboards.php" method="post"
            style="<?php if (!isset($_GET['id_juego'])) {echo "display: none;";} ?>">
        <input type="hidden" name="id_juego" value="<?php echo $id_juego ?>" />
        <label>Nombre del leaderboard: </label>
        <input type="text" name="nombre_leaderboard" />
        <label>Limite del leaderboard: </label>
        <input type="text" name="limite_leaderboard" value="100"/>
        <label>Es default: </label>
        <input type="checkbox" name="es_default" />
        <input type="submit" name="crear_leaderboard" value="Crear Leaderboard" />
      </form>
    </div>
  </body>

</html>
