<?php
// Incluye el archivo de conexión a la base de datos
include 'conexion.php';

// Realiza una consulta SQL para obtener todos los registros de la tabla detalle_ventas
// Usando folio en lugar de id_venta
$result = $conexion->query("SELECT dv.*, v.fecha 
                           FROM detalle_ventas dv
                           JOIN ventas v ON dv.folio = v.folio
                           ORDER BY dv.folio, dv.id_detalle");

// Si no hay registros, muestra un mensaje y termina la ejecución del script
if ($result->num_rows === 0) die('<div>No se encontraron registros en detalle_ventas</div>');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de Ventas</title>
    <style>
        /* Estilos CSS para la tabla */
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }  /* Fondo gris claro para los encabezados */
    </style>
</head>
<body>
    <h1>Detalles de Ventas</h1>
    
    <div>
        <!-- Muestra el total de registros obtenidos de la consulta -->
        <p>Total de registros: <strong><?=$result->num_rows?></strong></p>
        <!-- Muestra la fecha y hora actual -->
        <p>Última actualización: <strong><?=date('d/m/Y H:i:s')?></strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <!-- Encabezados de la tabla -->
                <th>ID Detalle</th>
                <th>Folio Venta</th>
                <th>Fecha</th>
                <th>ID Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <!-- Bucle que recorre cada fila de resultados -->
            <tr>
                <!-- Muestra cada campo de la fila actual -->
                <td><?=htmlspecialchars($row['id_detalle'])?></td>
                <td><?=htmlspecialchars($row['folio'])?></td>
                <td><?=htmlspecialchars($row['fecha'])?></td>
                <td><?=htmlspecialchars($row['id_producto'])?></td>
                <td><?=htmlspecialchars($row['cantidad'])?></td>
                <!-- number_format() da formato de moneda con 2 decimales -->
                <td>$<?=number_format($row['precio_unitario'], 2)?></td>
                <td>$<?=number_format($row['subtotal'], 2)?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>