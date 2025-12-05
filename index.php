<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Detecta si es admin logueado
if (isAdminLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit;
}

// Si cliente logueado, redirige a inicio; sino, muestra inicio
if (isLoggedIn()) {
    header('Location: pages/inicio.php');
    exit;
} else {
    header('Location: pages/inicio.php');
    exit;
}

// Si no hay redirección, muestra página de bienvenida simple (fallback)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbería M Barber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Bienvenido a Barbería Estilo Urbano</h1>
        <p><a href="pages/inicio.php" class="btn btn-primary">Ir al Inicio</a></p>
        <p><a href="admin/" class="btn btn-warning">Dashboard Admin</a></p>
    </div>
</body>
</html>