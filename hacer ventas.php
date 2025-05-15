<?php
include 'conexion.php';
session_start();
$_SESSION['carrito'] = $_SESSION['carrito'] ?? [];
$errores = [];
$mensaje = '';
$productoEncontrado = null;

function generarFolioUnico($conexion) {
    do {
        $folio = mt_rand(100000000, 999999999);
        $resultado = $conexion->query("SELECT COUNT(*) as total FROM ventas WHERE folio = $folio");
        $existe = $resultado->fetch_assoc()['total'] > 0;
    } while ($existe);
    return $folio;
}

function generarTicketPDF($folio, $carrito, $total, $metodoPago, $recibido = null, $cambio = null) {
    require('fpdf186/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'TIENDA EL RECIO',0,1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,7,"Folio: $folio",0,1);
    $pdf->Cell(0,7,'Fecha: '.date('d/m/Y H:i:s'),0,1);
    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(70,7,'Producto',0,0);
    $pdf->Cell(30,7,'Precio',0,0,'R');
    $pdf->Cell(20,7,'Cant.',0,0,'R');
    $pdf->Cell(30,7,'Subtotal',0,1,'R');
    $pdf->SetFont('Arial','',9);
    foreach($carrito as $p) {
        $pdf->Cell(70,7,substr($p['nombre'],0,25),0,0);
        $pdf->Cell(30,7,'$'.number_format($p['precio'],2),0,0,'R');
        $pdf->Cell(20,7,$p['cantidad'],0,0,'R');
        $pdf->Cell(30,7,'$'.number_format($p['subtotal'],2),0,1,'R');
    }
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(120,10,'TOTAL:',0,0,'R');
    $pdf->Cell(30,10,'$'.number_format($total,2),0,1,'R');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,7,"Pago: ".strtoupper($metodoPago),0,1);
    if($metodoPago == 'efectivo') {
        $pdf->Cell(0,7,'Recibido: $'.number_format($recibido,2),0,1);
        $pdf->Cell(0,7,'Cambio: $'.number_format($cambio,2),0,1);
    }
    return $pdf;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['buscar_producto'])) {
        $codigo = trim($_POST['codigo_barras']);
        $productoEncontrado = $conexion->query("SELECT * FROM productos WHERE codigo_barras = '$codigo'")->fetch_assoc();
        if (!$productoEncontrado) {
            $errores[] = "Producto no encontrado";
        }
    }
    elseif (isset($_POST['agregar_producto'])) {
        $codigo = trim($_POST['codigo_barras']);
        $cantidad = max(1, (int)$_POST['cantidad']);
        $producto = $conexion->query("SELECT * FROM productos WHERE codigo_barras = '$codigo'")->fetch_assoc();
        if ($producto) {
            $cantidad_en_carrito = $_SESSION['carrito'][$codigo]['cantidad'] ?? 0;
            $stock_disponible = $producto['stock'] - $cantidad_en_carrito;
            if ($stock_disponible >= $cantidad) {
                if (isset($_SESSION['carrito'][$codigo])) {
                    $_SESSION['carrito'][$codigo]['cantidad'] += $cantidad;
                    $_SESSION['carrito'][$codigo]['subtotal'] = $_SESSION['carrito'][$codigo]['precio'] * $_SESSION['carrito'][$codigo]['cantidad'];
                } else {
                    $_SESSION['carrito'][$codigo] = [
                        'id' => $producto['id_producto'],
                        'nombre' => $producto['nombre'],
                        'precio' => $producto['precio'],
                        'cantidad' => $cantidad,
                        'subtotal' => $producto['precio'] * $cantidad
                    ];
                }
                $mensaje = "{$producto['nombre']} agregado";
                $productoEncontrado = null; // Limpiar después de agregar
            } else {
                $errores[] = "Stock insuficiente de {$producto['nombre']}";
            }
        } else {
            $errores[] = "Producto no encontrado";
        }
    }
    elseif (isset($_POST['actualizar_cantidad'])) {
        $codigo = $_POST['codigo'];
        $nuevaCantidad = max(1, (int)$_POST['nueva_cantidad']);
        if (isset($_SESSION['carrito'][$codigo])) {
            $producto = $conexion->query("SELECT stock FROM productos WHERE codigo_barras = '$codigo'")->fetch_assoc();
            if ($producto && $producto['stock'] >= $nuevaCantidad) {
                $_SESSION['carrito'][$codigo]['cantidad'] = $nuevaCantidad;
                $_SESSION['carrito'][$codigo]['subtotal'] = $_SESSION['carrito'][$codigo]['precio'] * $nuevaCantidad;
            } else {
                $errores[] = "Stock insuficiente";
            }
        }
    }
    elseif (isset($_POST['finalizar_venta'])) {
        if (empty($_SESSION['carrito'])) {
            $errores[] = "No hay productos en el carrito";
        } else {
            $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));
            $recibido = (float)($_POST['monto_recibido'] ?? 0);
            if ($_POST['metodo_pago'] == 'efectivo' && $recibido < $total) {
                $errores[] = "Monto insuficiente";
            } else {
                $conexion->begin_transaction();
                try {
                    $cambio = $_POST['metodo_pago'] == 'efectivo' ? $recibido - $total : 0;
                    $folio = generarFolioUnico($conexion);
                    $conexion->query("INSERT INTO ventas (folio, fecha, total, metodo_pago, monto_recibido, cambio) 
                        VALUES ($folio, NOW(), $total, '{$_POST['metodo_pago']}', $recibido, $cambio)");
                    foreach ($_SESSION['carrito'] as $p) {
                        $conexion->query("INSERT INTO detalle_ventas (folio, id_producto, cantidad, precio_unitario, subtotal) 
                            VALUES ($folio, {$p['id']}, {$p['cantidad']}, {$p['precio']}, {$p['subtotal']})");
                        $conexion->query("UPDATE productos SET stock = stock - {$p['cantidad']} WHERE id_producto = {$p['id']}");
                    }
                    $conexion->commit();
                    $pdf = generarTicketPDF($folio, $_SESSION['carrito'], $total, $_POST['metodo_pago'], $recibido, $cambio);
                    if(!file_exists('tickets')) mkdir('tickets', 0777, true);
                    $pdfFilePath = 'tickets/ticket_'.$folio.'.pdf';
                    $pdf->Output('F', $pdfFilePath);
                    unset($_SESSION['carrito']);
                    $mensaje = "Venta completada - Folio: $folio - <a href='$pdfFilePath' target='_blank'>Descargar Ticket</a>";
                } catch (Exception $e) {
                    $conexion->rollback();
                    $errores[] = "Error: ".$e->getMessage();
                }
            }
        }
    }
} elseif (isset($_GET['eliminar']) && isset($_SESSION['carrito'][$_GET['eliminar']])) {
    unset($_SESSION['carrito'][$_GET['eliminar']]);
    if (empty($_SESSION['carrito'])) unset($_SESSION['carrito']);
} elseif (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
}

$total = 0;
if (!empty($_SESSION['carrito'])) {
    $total = array_sum(array_column($_SESSION['carrito'], 'subtotal'));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Ventas</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 20px; background-color: #f4f4f4; }
        h1 { text-align: center; }
        .container { display: flex; justify-content: space-between; }
        .panel { width: 48%; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
        .btn { padding: 10px 15px; color: white; text-decoration: none; border-radius: 5px; }
        .btn-primary { background: #007bff; border: none; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; border: none; margin-top: 10px; }
        .success { color: green; }
        .error { color: red; }
        .producto-info { 
            background: #f8f9fa; 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 5px; 
            border-left: 4px solid #007bff;
        }
        .hidden { display: none; }
    </style>
</head>
<body>
<h1>Sistema de Ventas</h1>
<?= !empty($errores) ? "<div class='error'>".implode('<br>', $errores)."</div>" : '' ?>
<?= !empty($mensaje) ? "<div class='success'>$mensaje</div>" : '' ?>
<div class="container">
    <div class="panel">
        <form method="POST" id="buscarForm">
            <h2>Buscar Producto</h2>
            <input type="text" name="codigo_barras" id="codigo_barras" placeholder="Código de barras" required autofocus>
            <input type="hidden" name="buscar_producto" value="1">
        </form>

        <?php if ($productoEncontrado): ?>
        <div class="producto-info">
            <h3><?= htmlspecialchars($productoEncontrado['nombre']) ?></h3>
            <p><strong>Descripción:</strong> <?= htmlspecialchars($productoEncontrado['descripcion']) ?></p>
            <p><strong>Precio:</strong> $<?= number_format($productoEncontrado['precio'], 2) ?></p>
            <p><strong>Stock disponible:</strong> <?= $productoEncontrado['stock'] ?></p>
            
            <form method="POST">
                <input type="hidden" name="codigo_barras" value="<?= htmlspecialchars($productoEncontrado['codigo_barras']) ?>">
                <input type="number" name="cantidad" value="1" min="1" max="<?= $productoEncontrado['stock'] ?>" required>
                <button type="submit" name="agregar_producto" class="btn btn-primary">Agregar al carrito</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="panel">
        <h2>Carrito</h2>
        <?php if (!empty($_SESSION['carrito'])): ?>
            <table>
                <tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th>Acción</th></tr>
                <?php foreach ($_SESSION['carrito'] as $codigo => $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td>$<?= number_format($p['precio'], 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo) ?>">
                                <input type="number" name="nueva_cantidad" value="<?= $p['cantidad'] ?>" min="1" style="width:60px;" onkeydown="if(event.key==='Enter'){this.form.submit();return false;}">
                                <input type="hidden" name="actualizar_cantidad" value="1">
                            </form>
                        </td>
                        <td>$<?= number_format($p['subtotal'], 2) ?></td>
                        <td><a href="?eliminar=<?= urlencode($codigo) ?>" class="btn btn-danger">Eliminar</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <h3>Total: $<?= number_format($total, 2) ?></h3>
            <a href="?vaciar=1" class="btn btn-danger">Vaciar</a>
            <form method="POST">
                <h3>Pago</h3>
                <select name="metodo_pago" required onchange="document.getElementById('efectivo_fields').style.display = this.value == 'efectivo' ? 'block' : 'none'">
                    <option value="">-- Seleccione --</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                </select>
                <div id="efectivo_fields" style="display:none;">
                    <input type="number" name="monto_recibido" placeholder="Monto" step="0.01" min="<?= $total ?>" value="<?= $total ?>">
                    <div id="cambio"></div>
                </div>
                <button type="submit" name="finalizar_venta" class="btn btn-success">Finalizar</button>
            </form>
            <script>
                document.querySelector('[name="monto_recibido"]')?.addEventListener('input', function() {
                    const cambio = this.value - <?= $total ?>;
                    document.getElementById('cambio').innerHTML = cambio >= 0 
                        ? `Cambio: $${cambio.toFixed(2)}` 
                        : `<span style="color:red;">Faltan: $${Math.abs(cambio).toFixed(2)}</span>`;
                });
            </script>
        <?php else: ?>
            <p>Carrito vacío</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // Enviar formulario al presionar Enter
    document.getElementById('codigo_barras').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('buscarForm').submit();
        }
    });
    
    // Poner foco en el campo de código al cargar la página
    window.onload = function() {
        document.getElementById('codigo_barras').focus();
    };
</script>
</body>
</html>