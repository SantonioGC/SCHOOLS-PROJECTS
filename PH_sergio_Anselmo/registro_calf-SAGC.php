<!-- Sergio Antonio Gomez Cazares -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>INGRESO CALIFICACIONES</title>
</head>
<body>
    <h1>Registro Calificaciones</h1>
    <form method="post">
        <label for="español">Español:</label>
        <input type="number" name="español" required><br>
        <br>
        <label for="ingles">Inglés:</label>
        <input type="number" name="ingles" required><br>
        <br>
        <label for="algebra">Algebra:</label>
        <input type="number " name="algebra" required><br>
        <br>
        <input type="submit" name="enviar" value="Enviar">
    </form>

    <?php
    include "conexion-SAGC.php";

    if (isset($_POST['enviar'])) {
        $español = $_POST['español'];
        $ingles = $_POST['ingles'];
        $algebra = $_POST['algebra'];

        echo "<p>--------------------------------------------------</p>";
        echo "<h2>Calificaciones ingresadas:</h2>";
        echo "<p>Español: $español</p>";
        echo "<p>Inglés: $ingles</p>";
        echo "<p>Algebra: $algebra</p>";

        $insertar = "INSERT INTO datos_materias_SAGC (español, ingles, algebra) VALUES ('$español', '$ingles', '$algebra')";
        $conexion = mysqli_query($conexion, $insertar);

        if ($conexion) {
            echo "Datos insertados correctamente";
        } else {
            echo "Error al insertar los datos";
        }
    }
    ?>
</body>
</html>