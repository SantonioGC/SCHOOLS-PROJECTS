<!-- Sergio Antonio Gomez Cazares -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CONSULTAR KARDEX</title>
</head>
<body>
    <h1>Consultar kardex</h1>
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

        $consulta_calificaciones = "SELECT * FROM datos_materias_SAGC WHERE cuenta = $cuenta";
        $resultado_calificaciones = mysqli_query($conexion, $consulta_calificaciones);
        $fila_calificaciones = mysqli_fetch_assoc($resultado_calificaciones);

        echo "<h3>Datos del Alumno:</h3>";
        echo "<p>Carrera: " . $fila_alumno['carrera'] . "</p>";
        echo "<p>Cuenta: " . $fila_alumno['cuenta'] . "</p>";
        echo "<p>Nombre: " . $fila_alumno['nombre'] . "</p>";
        echo "<p>Semestre: " . $fila_alumno['semestre'] . "</p>";
        
        echo "<p>--------------------------------------------------</p>";

        echo "<h3>Calificaciones:</h3>";
        echo "<p>Español: " . $fila_calificaciones['español'] . "</p>";
        echo "<p>Inglés: " . $fila_calificaciones['ingles'] . "</p>";
        echo "<p>Algebra: " . $fila_calificaciones['algebra'] . "</p>";

        $español = $fila_calificaciones['español'];
        $ingles = $fila_calificaciones['ingles'];
        $algebra = $fila_calificaciones['algebra'];
    
        $promedio = ($español + $ingles + $algebra) / 3;
    
        echo "<p>--------------------------------------------------</p>";
        echo "<h3>Promedio:</h3>";
        echo "<p>El promedio del alumno es: " . number_format($promedio, 2) . "</p>";
    }
    ?>
</body>
</html>