<?php
// Conexión con la base de datos
include 'conexion.php';

/**
 * Función para procesar un reembolso completo de una venta
 * @param int $folio El folio de la venta a reembolsar
 * @return array Resultado de la operación (éxito/error y mensaje)
 */
function procesarReembolso($folio) {
    global $conexion;
    
    try {
        // Inicia una transacción para asegurar la integridad de los datos
        $conexion->begin_transaction();
        
        // 1. Verificar que la venta existe
        $venta = $conexion->query("SELECT * FROM ventas WHERE folio = $folio")->fetch_assoc();
        if (!$venta) throw new Exception("Venta no encontrada");
        
        // 2. Obtener todos los detalles (productos) de la venta
        $detalles = $conexion->query("SELECT * FROM detalle_ventas WHERE folio = $folio")->fetch_all(MYSQLI_ASSOC);
        if (empty($detalles)) throw new Exception("No hay productos para reembolsar");
        
        // 3. Actualizar el inventario (devolver stock)
        foreach ($detalles as $d) {
            $conexion->query("UPDATE productos SET stock = stock + {$d['cantidad']} WHERE id_producto = {$d['id_producto']}");
        }
        
        // 4. Calcular el monto total a reembolsar
        $monto = array_sum(array_column($detalles, 'subtotal'));
        
        // 5. Eliminar primero los detalles de la venta (por restricciones de clave foránea)
        $conexion->query("DELETE FROM detalle_ventas WHERE folio = $folio");
        
        // 6. Finalmente eliminar la venta principal
        $conexion->query("DELETE FROM ventas WHERE folio = $folio");
        
        // Confirma todas las operaciones si todo salió bien
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => "Reembolso exitoso y venta eliminada", 
            'monto' => $monto, 
            'folio' => $folio
        ];
        
    } catch (Exception $e) {
        // Si algo falla, revierte todas las operaciones
        $conexion->rollback();
        return [
            'success' => false, 
            'message' => "Error: " . $e->getMessage()
        ];
    }
}

// Manejo de la solicitud POST desde AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folio = $_POST['folio'] ?? 0;
    // Devuelve la respuesta en formato JSON
    echo json_encode(procesarReembolso($folio));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reembolsos</title>
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }

        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            margin-bottom: 20px;
            text-align: center;
        }

        input[type="number"] {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            padding: 10px;
            width: 200px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Procesar Reembolso</h1>
    
    <!-- Formulario para ingresar el folio de venta -->
    <form id="formReembolso">
        <input type="number" id="folio" placeholder="Folio de Venta" required>
        <button type="submit">Procesar</button>
    </form>
    
    <!-- Div para mostrar resultados -->
    <div id="resultado"></div>

    <script>
        // Manejo del formulario con AJAX
        document.getElementById('formReembolso').onsubmit = async e => {
            e.preventDefault(); // Evita el envío tradicional del formulario
            
            const folio = document.getElementById('folio').value;
            
            // Envía la solicitud POST al servidor
            const res = await fetch('', {
                method: 'POST', 
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 
                body: `folio=${folio}`
            });
            
            // Procesa la respuesta JSON
            const data = await res.json();
            
            // Muestra el resultado al usuario
            const div = document.getElementById('resultado');
            div.className = data.success ? 'success' : 'error';
            div.innerHTML = data.success 
                ? `Reembolso exitoso y venta eliminada<br>Folio: ${data.folio}<br>Monto: $${data.monto.toFixed(2)}` 
                : `Error: ${data.message}`;
        };
    </script>
</body>
</html>