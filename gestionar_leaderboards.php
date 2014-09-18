<?php
include_once 'funciones_html.php';
include_once 'funciones_leaderboards.php';

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
    <link rel="stylesheet" type="text/css" href="js/jquery-ui.css" >
    <script type="text/javascript" src="js/jquery-2.1.1.min.js" ></script>
    <script type="text/javascript" src="js/jquery-ui.js" ></script>
    <script type="text/javascript">
      var autocompleteJugadorTimeoutId;
      var jugadoresNameDatasource = [];
      var jugadoresDatasource = [];
      
      function clickBuscarJugador(e) {
        
      }
      
      $(document).ready(function() {
        $("#nombre_jugador")
        .autocomplete({
          source: jugadoresNameDatasource
        })
        .keypress(function(e) {
          if (autocompleteJugadorTimeoutId) {
            clearTimeout(autocompleteJugadorTimeoutId);
          }
          autocompleteJugadorTimeoutId = setTimeout(onAutocompleteTimeout, 1000);
        })
        .change(function(e) {
          var inputJugador = $(this).val();
          if (jugadoresNameDatasource.indexOf(inputJugador) >= 0) {
            var jugador = jugadoresDatasource[jugadoresNameDatasource.indexOf(inputJugador)];
            $("#id_jugador_puntaje").val(jugador.id);
          } else {
            alert("No seleccionaste ningun jugador valido de la lista");
          }
        });
      });
      
      function onAutocompleteTimeout() {
        var nombre = $("#nombre_jugador").val();
        $.ajax({
          headers: {
                  "Accept": "application/json",
                  "Content-Type": "application/json"
          },
          url: "/funciones_ajax.php",
          dataType: "json",
          type: "GET",
          data: JSON.stringify({"nombre": nombre, "req_func": "ajax_buscar_jugador_nombre_autocomplete"}),
          success: function(data) {
            jugadoresDatasource = data instanceof Array ? data : [data];
            jugadoresNameDatasource = jugadoresDatasource.map(function(val) {
              return val.nombre;
            });
            $("#nombre_jugador").autocomplete({
              source: jugadoresNameDatasource
            });
          },
          error: function(xhr, status, error) {
            console.error("%s %s", status, error);
            alert("Error: " + error);
          }
        });
      }
    </script>
  </head>
  <body>
    <h1>Gestionar Leaderboards</h1>
    <div>
      <h2>Seleccionar juego</h2>
      <form id="buscarLeaderboard" action="gestionar_leaderboards.php" method="get">
        <label>Juego: </label>
        <select id="juego" name="id_juego">
          <option value>Seleccionar opcion</option>
          <?php
          $juegos = listar_juegos();
          foreach ($juegos as &$juego) {
            echo "<option value='" . $juego['id'] . "' " 
                    . (isset($_GET['id_juego']) && $_GET['id_juego'] == $juego['id'] 
                      ? "selected='selected'" : "") ." >"
                    . $juego['nombre'] 
                    . "</option>\n";
          }
          ?>
        </select>
        <?php
        echo "Cant. Juegos: " . count($juegos);
        ?>
        <input type="submit" value="Buscar" />
        <br/>
        <?php if (isset($_GET['id_juego'])):?>
        <?php $id_juego = intval($_GET['id_juego']);
              $leaderboards = listar_leaderboard($id_juego);
        ?>
          <label>Leaderboard: <?php echo "(" . count($leaderboards) . ")" ?></label>
          <select id="leaderboard" name="id_leaderboard">
            <option value>Seleccionar opcion</option>
            <?php
              foreach ($leaderboards as &$leaderboard) {
                echo "<option value='" . $leaderboard['id'] . "'" 
                        . (isset($_GET['id_leaderboard']) && $_GET['id_leaderboard'] == $leaderboard['id']
                          ? "selected='selected'" : "") ." >"
                        . $leaderboard['nombre']
                        . ($leaderboard['es_default'] ? " *" : "")
                        . "</option>\n";
              }
            ?>
          </select>
        <?php endif; ?>
      </form>
    </div>
    <?php
    $mostrar_puntajes = false;
    if (is_get() && isset($_GET['id_juego']) && isset($_GET['id_leaderboard'])) {
      $scores = obtener_puntajes(intval($_GET['id_leaderboard']));
      $mostrar_puntajes = true;
    }
    if ($mostrar_puntajes):
    ?>
    <div >
      <h2>Puntajes del leaderboards:</h2>
      <table id="resultado" style="border: 1px solid black;" >
        <thead>
          <tr>
            <th>Puntaje</th>
            <th>Fecha</th>
            <th>Jugador</th>
            <th>Borrar?</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($leaderboard) {
            foreach ($scores as &$score) {
              echo "<tr>\n";
              echo "<td>" . $score['puntaje'] . "</td>";
              echo "<td>" . $score['fecha'] . "</td>";
              echo "<td><a href='#'>" . $score['id_jugador'] ."</a></td>";
              echo "<td><a href='#'>X</a></td>";
              echo "</tr>\n";
              echo "<option value='" . $juego['id'] . "' >"
              . $juego['nombre'] . "</option>\n";
            }
          }
          ?>
        <div>
          <form id="crearPuntaje" action="gestionar_leaderboards.php" method="post">
              <input type="hidden" name="id_leaderboard" value="<?php echo $_GET['id_leaderboard'] ?>" />
              <label>Puntaje: </label>
              <input type="text" name="puntaje" />
              <label>Usuario: </label>
              <input id="nombre_jugador" type="text" name="jugador_puntaje" value="100"/>
              <input id="id_jugador_puntaje" type="hidden" />
              <button onclick="clickBuscarJugador(event);">Buscar jugador</button>
              <br />
              <input type="submit" name="crear_leaderboard" value="Crear Leaderboard" />
            </form>
          </div>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
     <br />
    <?php if (isset($_GET['id_juego'])) : ?>
      <div>
        <h2>Crear leaderboard</h2>
        <form id="crearLeaderboard" action="gestionar_leaderboards.php" method="post" >
          <input type="hidden" name="id_juego" value="<?php echo $_GET['id_juego'] ?>" />
          <label>Nombre del leaderboard: </label>
          <input type="text" name="nombre_leaderboard" /> <br />
          <label>Limite del leaderboard: </label>
          <input type="text" name="limite_leaderboard" value="100"/> <br />
          <label for="checkEsDefault">Es default: </label>
          <input id="checkEsDefault" type="checkbox" name="es_default" /> <br />
        <br />
        <input type="submit" name="crear_leaderboard" value="Crear Leaderboard" />
      </form>
    </div>
    <?php endif; ?>
  </body>
</html>
