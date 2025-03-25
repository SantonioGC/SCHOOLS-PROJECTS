<!-- Sergio Antonio Gomez Cazares -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CONSULTAR DATOS DEL ALUMNO</title>
</head>
<body>
    <h1>Consultar datos</h1>
    <form method="post">
        <label for="cuenta">Numero de Cuenta:</label>
        <input type="number" name="cuenta">
        <br>
        <input type="submit" name="consultar" value="Consultar">
    </form>

    <?php
    include "conexion-SAGC.php";

    if (isset($_POST['consultar'])) {
        $cuenta = $_POST['cuenta'];

        $consulta_alumno = "SELECT * FROM alumnos_SAGC WHERE cuenta = $cuenta";
        $resultado_alumno = mysqli_query($conexion, $consulta_alumno);
        $fila_alumno = mysqli_fetch_assoc($resultado_alumno);

        echo "<p>--------------------------------------------------</p>";
        echo "<h3>Datos del Alumno:</h3>";
        echo "<p>Carrera: " . $fila_alumno['carrera'] . "</p>";
        echo "<p>Cuenta: " . $fila_alumno['cuenta'] . "</p>";
        echo "<p>Nombre: " . $fila_alumno['nombre'] . "</p>";
    }

?>
</body>
</html>