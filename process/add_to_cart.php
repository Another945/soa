<?php
require_once '../includes/functions.php'; // functions.php se encarga de iniciar la sesión

// Validar que se recibió un ID de producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si no hay ID, redirigir a la página de productos
    header('Location: ../pages/productos.php');
    exit;
}

$idProducto = (int)$_GET['id'];
$cantidad = 1; // Por defecto, se agrega una unidad

// Buscar el producto en la base de datos para obtener sus detalles
$stmt = $pdo->prepare("SELECT NombreProductos, PrecioProductos, StockProductos FROM productos WHERE IdProductos = ? AND ActivoProductos = 1");
$stmt->execute([$idProducto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el producto existe y tiene stock
if ($producto && $producto['StockProductos'] > 0) {
    
    // Obtener el carrito actual de la sesión
    $cart = getCart();

    // Si el producto ya está en el carrito, aumentar la cantidad
    if (isset($cart[$idProducto])) {
        $cart[$idProducto]['cantidad'] += $cantidad;
    } else {
        // Si no, agregarlo al carrito
        $cart[$idProducto] = [
            'nombre' => $producto['NombreProductos'],
            'precio' => $producto['PrecioProductos'],
            'cantidad' => $cantidad
        ];
    }

    // Guardar el carrito actualizado en la sesión
    $_SESSION['cart'] = $cart;

    // Crear una alerta de éxito para mostrarla en la siguiente página
    $_SESSION['alert'] = [
        'type' => 'success',
        'message' => '¡Producto añadido al carrito!'
    ];

} else {
    // Si el producto no existe o no tiene stock, crear una alerta de error
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'El producto no está disponible.'
    ];
}

// Redirigir al usuario a la página anterior desde donde hizo clic
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;