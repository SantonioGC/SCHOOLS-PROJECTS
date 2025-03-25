<!-- Sergio Antonio Gomez Cazares -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro de Alumno</title>
</head>
<body>
    <h1>Registro de Alumno</h1>
    <form method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="carrera">Carrera:</label>
        <input type="text" id="carrera" name="carrera" required><br><br>

        <label for="semestre">Semestre:</label>
        <input type="text" id="semestre" name="semestre" required><br><br>

        <label for="cuenta">Cuenta:</label>
        <input type="text" id="cuenta" name="cuenta" required><br><br>

        <button type="submit" name="enviar">Enviar</button>
    </form>
</body>
</html>

<?php
 
 include "conexion-SAGC.php";

  if(isset($_POST['enviar'])){
      
      $nombre = $_POST['nombre'];
      $carrera = $_POST['carrera'];
      $semestre = $_POST['semestre'];
      $cuenta = $_POST['cuenta'];
      
      $insertar = "INSERT INTO alumnos_SAGC (nombre,carrera,semestre,cuenta)
                 VALUES ('$nombre','$carrera','$semestre','$cuenta')";

      
      $conexion = mysqli_query($conexion,$insertar);
  }
?>
