<?php
// Incluye el archivo de conexión a la base de datos
include 'conexion.php';

// Inicialización de variables
$errores = []; // Array para almacenar errores
$mensaje = ''; // Variable para mensajes de éxito
$producto = null; // Variable para almacenar datos del producto encontrado
$codigo_busqueda = $_GET['codigo'] ?? ''; // Obtiene código de barras de la URL o cadena vacía

// Valores por defecto para formulario de nuevo producto
$valores_nuevo = [
    'codigo' => '',
    'nombre' => '',
    'descripcion' => '',
    'precio' => '',
    'stock' => '0'
];

// Búsqueda de producto si se proporcionó un código
if ($codigo_busqueda) {
    // Prepara consulta SQL con parámetros para prevenir inyección SQL
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE codigo_barras = ?");
    $stmt->bind_param("s", $codigo_busqueda); // "s" indica que es un string
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc(); // Obtiene el resultado como array asociativo
    if (!$producto) $errores[] = "Producto no encontrado"; // Agrega error si no se encuentra
}

// Procesamiento del formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sección para actualizar producto existente
    if (isset($_POST['actualizar'])) {
        // Recoge y sanitiza datos del formulario
        $datos = [
            'id' => $_POST['id'] ?? '',
            'codigo' => $_POST['codigo'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'precio' => (float)($_POST['precio'] ?? 0), // Convierte a float
            'stock' => (int)($_POST['stock'] ?? 0) // Convierte a entero
        ];

        // Validaciones
        if (empty($datos['nombre'])) $errores[] = "Nombre es obligatorio";
        if ($datos['precio'] <= 0) $errores[] = "Precio debe ser positivo";
        
        // Si no hay errores, actualiza en la base de datos
        if (empty($errores)) {
            $stmt = $conexion->prepare("UPDATE productos SET codigo_barras=?, nombre=?, descripcion=?, precio=?, stock=? WHERE id_producto=?");
            // "sssdii" indica tipos de parámetros: string, string, string, double, int, int
            $stmt->bind_param("sssdii", $datos['codigo'], $datos['nombre'], $datos['descripcion'], $datos['precio'], $datos['stock'], $datos['id']);
            if ($stmt->execute()) {
                $mensaje = "Producto actualizado!";
                $producto = $datos; // Actualiza datos mostrados con los nuevos valores
            }
        }
    }

    // Sección para agregar nuevo producto
    if (isset($_POST['agregar'])) {
        // Recoge y sanitiza datos del formulario
        $valores_nuevo = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => trim($_POST['precio'] ?? '0'),
            'stock' => trim($_POST['stock'] ?? '0')
        ];

        // Validaciones
        if (empty($valores_nuevo['nombre'])) $errores[] = "Nombre es obligatorio";
        if (!is_numeric($valores_nuevo['precio']) || $valores_nuevo['precio'] <= 0) $errores[] = "Precio inválido";

        // Verifica si el código ya existe (solo si se proporciono un codigo)
        if (!empty($valores_nuevo['codigo'])) {
            $stmt = $conexion->prepare("SELECT id_producto FROM productos WHERE codigo_barras = ?");
            $stmt->bind_param("s", $valores_nuevo['codigo']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) $errores[] = "El código ya existe";
        }

        // Si no hay errores, inserta en la base de datos
        if (empty($errores)) {
            $stmt = $conexion->prepare("INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock, fecha_ingreso) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssdi", $valores_nuevo['codigo'], $valores_nuevo['nombre'], $valores_nuevo['descripcion'], $valores_nuevo['precio'], $valores_nuevo['stock']);
            if ($stmt->execute()) {
                $mensaje = "Producto agregado!";
                // Limpia el formulario despues de agregar
                $valores_nuevo = array_fill_keys(array_keys($valores_nuevo), ''); 
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Inventario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
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
        }
        .error {
            color: red;
        }
        .exito {
            color: green;
        }
    </style>
</head>
<body>
    <h1>Modificar Productos</h1>

    <!-- Muestra errores si existen -->
    <?php if ($errores): ?>
        <div class="error">
            <?= implode('<br>', $errores) ?> 
        </div>
    <?php endif; ?>
    
    <!-- Muestra mensaje de éxito si existe -->
    <?php if ($mensaje): ?>
        <div class="exito"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Seccion de búsqueda -->
    <section>
        <h2>Buscar Producto</h2>
        <form method="GET">
            <!-- Campo para buscar por codigo de barras -->
            <input type="text" name="codigo" placeholder="Código de barras" value="<?= htmlspecialchars($codigo_busqueda) ?>" required>
            <button type="submit">Buscar</button>
        </form>

        <!-- Formulario de actualización (solo visible si se encontró un producto) -->
        <?php if (isset($producto) && $producto): ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $producto['id_producto'] ?? '' ?>">
                <input type="text" name="codigo" value="<?= htmlspecialchars($producto['codigo_barras'] ?? '') ?>" placeholder="Código" required>
                <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" placeholder="Nombre" required>
                <textarea name="descripcion" placeholder="Descripción"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                <input type="number" name="precio" step="0.01" min="0" value="<?= $producto['precio'] ?? '' ?>" placeholder="Precio" required>
                <input type="number" name="stock" min="0" value="<?= $producto['stock'] ?? '' ?>" placeholder="Stock" required>
                <button type="submit" name="actualizar">Actualizar</button>
            </form>
        <?php endif; ?>
    </section>

    <!-- Seccion para agregar nuevo producto -->
    <section>
        <h2>Agregar Producto</h2>
        <form method="POST">
            <input type="text" name="codigo" value="<?= htmlspecialchars($valores_nuevo['codigo']) ?>" placeholder="Código">
            <input type="text" name="nombre" value="<?= htmlspecialchars($valores_nuevo['nombre']) ?>" placeholder="Nombre" required>
            <textarea name="descripcion" placeholder="Descripción"><?= htmlspecialchars($valores_nuevo['descripcion']) ?></textarea>
            <input type="number" name="precio" step="0.01" min="0.01" value="<?= htmlspecialchars($valores_nuevo['precio']) ?>" placeholder="Precio" required>
            <input type="number" name="stock" min="0" value="<?= htmlspecialchars($valores_nuevo['stock']) ?>" placeholder="Stock" required>
            <button type="submit" name="agregar">Agregar</button>
        </form>
    </section>
</body>
</html>