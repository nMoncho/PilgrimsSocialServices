<?php
    include('funciones_html.php');
    $is_get = $_SERVER['REQUEST_METHOD'] === 'GET';
    $is_post = $_SERVER['REQUEST_METHOD'] === 'POST';
?>
<html>
    <head>
    <title>Mostrar leaderboards</title>
    </head>
    <body>
        <div>Seleccionar juego: 
            <form id="buscarLeaderboard" action="mostrar_leaderboards.php" method="post">
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
                <input type="submit" value="Buscar" />
            </form>
        </div>
        <div>
            <?php 
                if ($is_post && isset($_POST['id_juego'])) {
                    $leaderboard = listar_leaderboard($_POST['id_juego']);
                    $scores = $leaderboard['puntajes'];
                    echo "Juego: " . $leaderboard['nombre'] . "(" . $leaderboard['id'] . ")";
                }
            ?>
            <table id="resultado" style="border: 1px solid black;<?php if ($is_get) {echo "display: none;";}?>" >
                <thead>
                    <tr>
                        <th>Puntaje</th>
                        <th>Fecha</th>
                        <th>Jugador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        if ($is_post && $leaderboard) {
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
    </body>
    
</html>
