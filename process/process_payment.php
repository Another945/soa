<?php
require_once '../includes/functions.php';
require_once '../vendor/autoload.php'; 

// TU CLAVE SECRETA
\Stripe\Stripe::setApiKey('sk_test_51STaglL0CpOiDccydVmZOjCofJRo6x4VLSyQ4TCMasvuVAxRcwvFe1bBaSiZZ2aAGTHtUVEubZhlcRIxMH9Zj0lF00KgPEt2Sg');

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) { throw new Exception("Usuario no autenticado."); }
    
    // Lógica para obtener email
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT EmailUsuarios FROM usuarios WHERE IdUsuarios = ?");
    $stmt->execute([$user_id]);
    $user_email = $stmt->fetchColumn();

    $cart = getCart();
    if (empty($cart)) { throw new Exception("El carrito está vacío."); }

    $line_items = [];
    foreach ($cart as $id => $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'pen',
                'product_data' => [ 'name' => $item['nombre'] ],
                'unit_amount' => (int)($item['precio'] * 100), // Centavos
            ],
            'quantity' => $item['cantidad'],
        ];
    }

    $protocol = 'http://';
    $host = $_SERVER['HTTP_HOST'];
    // Redirige a pago_exitoso.php al terminar
    $success_url = $protocol . $host . '/SoaProyecto/pages/pago_exitoso.php?session_id={CHECKOUT_SESSION_ID}';
    $cancel_url = $protocol . $host . '/SoaProyecto/pages/carrito.php?cancel=1';

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'customer_email' => $user_email,
    ]);

    echo json_encode(['id' => $session->id]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>