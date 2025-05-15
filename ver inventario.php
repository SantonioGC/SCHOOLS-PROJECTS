<?php
//Conexion con la BD
include 'conexion.php';

//Parametros 
$busqueda = $_GET['busqueda'] ?? '';
$orden = in_array($_GET['orden'] ?? '', ['nombre', 'precio', 'stock', 'fecha_ingreso']) ? $_GET['orden'] : 'nombre';
$direccion = in_array($_GET['dir'] ?? '', ['ASC', 'DESC']) ? $_GET['dir'] : 'ASC';

//Consulta SQL
$stmt = $conexion->prepare("
    SELECT codigo_barras, nombre, descripcion, precio, stock, 
           DATE_FORMAT(fecha_ingreso, '%d/%m/%Y %H:%i') AS fecha_ingreso_formateada
    FROM productos
    WHERE nombre LIKE ? OR codigo_barras LIKE ? OR descripcion LIKE ?
    ORDER BY $orden $direccion
");
$param = "%$busqueda%";
$stmt->bind_param("sss", $param, $param, $param);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

//Totales
$total_productos = count($productos);
$total_stock = array_sum(array_column($productos, 'stock'));
$total_valor = array_sum(array_map(fn($p) => $p['precio'] * $p['stock'], $productos));

//Funcion de flechas de orden
function arrow($col, $orden, $dir) {
    return $orden == $col ? ($dir == 'ASC' ? '↑' : '↓') : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - La Tiendita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1.tituloprincipal {
            text-align: center;
            color: #333;
        }
        .busqueda {
            text-align: center;
            margin-bottom: 20px;
        }
        .busqueda input[type="text"] {
            padding: 10px;
            width: 300px;
        }
        .busqueda button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .busqueda button:hover {
            background-color: #218838;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th a {
            text-decoration: none;
            color: #333;
        }
        th a:hover {
            text-decoration: underline;
        }
        .bajo-stock {
            color: red;
        }
        </style>
</head>
<body>
    <h1 class="tituloprincipal">Inventario de Productos</h1>
    
    <div class="busqueda">
        <form method="GET">
            <input type="text" name="busqueda" placeholder="Buscar productos..." value="<?=htmlspecialchars($busqueda)?>">
            <button type="submit">Buscar</button>
            <a href="inventario.php"></a>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th><a href="?orden=codigo_barras&dir=<?=$orden=='codigo_barras'&&$direccion=='ASC'?'DESC':'ASC'?>&busqueda=<?=urlencode($busqueda)?>">
                    Código <?=arrow('codigo_barras', $orden, $direccion)?></a></th>
                <th><a href="?orden=nombre&dir=<?=$orden=='nombre'&&$direccion=='ASC'?'DESC':'ASC'?>&busqueda=<?=urlencode($busqueda)?>">
                    Nombre <?=arrow('nombre', $orden, $direccion)?></a></th>
                <th>Descripción</th>
                <th><a href="?orden=precio&dir=<?=$orden=='precio'&&$direccion=='ASC'?'DESC':'ASC'?>&busqueda=<?=urlencode($busqueda)?>">
                    Precio <?=arrow('precio', $orden, $direccion)?></a></th>
                <th><a href="?orden=stock&dir=<?=$orden=='stock'&&$direccion=='ASC'?'DESC':'ASC'?>&busqueda=<?=urlencode($busqueda)?>">
                    Stock <?=arrow('stock', $orden, $direccion)?></a></th>
                <th><a href="?orden=fecha_ingreso&dir=<?=$orden=='fecha_ingreso'&&$direccion=='ASC'?'DESC':'ASC'?>&busqueda=<?=urlencode($busqueda)?>">
                    Fecha Ingreso <?=arrow('fecha_ingreso', $orden, $direccion)?></a></th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($productos)): ?>
                <tr><td colspan="6">No se encontraron productos</td></tr>
            <?php else: foreach($productos as $p): ?>
                <tr>
                    <td><?=htmlspecialchars($p['codigo_barras'])?></td>
                    <td><?=htmlspecialchars($p['nombre'])?></td>
                    <td><?=htmlspecialchars($p['descripcion'])?></td>
                    <td>$<?=number_format($p['precio'],2)?></td>
                    <td class="<?=$p['stock']<5?'bajo-stock':''?>"><?=$p['stock']?></td>
                    <td><?=$p['fecha_ingreso_formateada']?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    
    <div class="totales">
        <strong>Total productos:</strong> <?=$total_productos?> |
        <strong>Total stock:</strong> <?=$total_stock?> |
        <strong>Valor total inventario:</strong> $<?=number_format($total_valor,2)?>
    </div>
</body>
</html>