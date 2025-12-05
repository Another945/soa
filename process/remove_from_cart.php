<?php
require_once '../includes/functions.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    removeFromCart($id);
}
header('Location: ../pages/carrito.php');
?>