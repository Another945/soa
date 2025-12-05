<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? null;
$idProducto = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

$response = ['success' => false, 'cartCount' => count(getCart())];

if (!$idProducto || !$action) {
    echo json_encode($response);
    exit;
}

$cart = getCart();

// 1. CONSULTA UNIFICADA: Traemos datos del producto Y la imagen en una sola llamada
$stmt = $pdo->prepare("
    SELECT p.NombreProductos, p.PrecioProductos, p.StockProductos, i.RutaImagen 
    FROM productos p 
    LEFT JOIN imagenes i ON p.IdProductos = i.IdRelacionado AND i.Tipo = 'producto'
    WHERE p.IdProductos = ? AND p.ActivoProductos = 1
");
$stmt->execute([$idProducto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
    exit;
}

// Definir la ruta de la imagen (o una por defecto)
$rutaImagen = $producto['RutaImagen'] ?? '../assets/images/productos/default.jpg';

switch ($action) {
    case 'add':
        // Validar Stock
        $cantidad_actual = isset($cart[$idProducto]) ? $cart[$idProducto]['cantidad'] : 0;
        $nueva_cantidad = $cantidad_actual + $qty;

        if ($nueva_cantidad > $producto['StockProductos']) {
            echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Solo quedan ' . $producto['StockProductos'] . ' unidades.']);
            exit;
        }

        if (isset($cart[$idProducto])) {
            $cart[$idProducto]['cantidad'] += $qty;
            // Actualizamos la imagen por si cambió en la BD
            $cart[$idProducto]['imagen'] = $rutaImagen; 
        } else {
            $cart[$idProducto] = [
                'nombre' => $producto['NombreProductos'],
                'precio' => $producto['PrecioProductos'],
                'cantidad' => $qty,
                'imagen' => $rutaImagen // <--- AQUÍ GUARDAMOS LA IMAGEN
            ];
        }
        $_SESSION['cart'] = $cart;
        
        $response = [
            'success' => true,
            'message' => '¡Producto añadido!',
            'cartCount' => count($_SESSION['cart'])
        ];
        break;

    case 'update':
        // Validar Stock al actualizar (+ y -)
        if ($qty > $producto['StockProductos']) {
            echo json_encode(['success' => false, 'message' => 'Stock máximo alcanzado.']);
            exit;
        }

        if (isset($cart[$idProducto]) && $qty > 0) {
            $cart[$idProducto]['cantidad'] = $qty;
            $_SESSION['cart'] = $cart;
            
            $newSubtotal = $cart[$idProducto]['precio'] * $qty;
            $newGrandTotal = cartTotal();

            $response = [
                'success'       => true,
                'newSubtotal'   => 'S/ ' . number_format($newSubtotal, 2),
                'newGrandTotal' => 'S/ ' . number_format($newGrandTotal, 2)
            ];
        }
        break;
}

echo json_encode($response);
exit;
?>