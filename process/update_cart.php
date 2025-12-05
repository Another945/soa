<?php
require_once '../includes/functions.php';

// Validar que se recibieron los datos necesarios
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['qty']) && is_numeric($_GET['qty'])) {
    
    $idProducto = (int)$_GET['id'];
    $cantidad = (int)$_GET['qty'];

    // Asegurarse de que la cantidad sea al menos 1
    if ($cantidad > 0) {
        $cart = getCart();

        // Si el producto existe en el carrito, actualizar su cantidad
        if (isset($cart[$idProducto])) {
            $cart[$idProducto]['cantidad'] = $cantidad;
            $_SESSION['cart'] = $cart;
        }
    }
}

// Redirigir siempre de vuelta al carrito
header('Location: ../pages/carrito.php');
exit;