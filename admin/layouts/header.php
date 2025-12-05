<?php
include_once 'conexion.php';
include_once 'controllers/usuario-dash.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - M Barber</title>
    <link rel="icon" type="image/jpeg" href="../assets/img/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>

<body>
    <div class="d-flex">
        <nav class="sidebar bg-dark text-white flex-shrink-0 p-3 d-flex flex-column">
            <a href="dashboard.php"
                class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="bi bi-scissors sidebar-brand-icon me-2"></i>
                <span class="sidebar-brand-text">M Barber</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="dashboard.php"
                        class="nav-link text-white <?php if ($current_page == 'dashboard.php') echo 'active'; ?>"
                        aria-current="page">
                        <i class="bi bi-house"></i> Inicio
                    </a>
                </li>
                <li>
                    <a href="productos.php"
                        class="nav-link text-white <?php if ($current_page == 'productos.php') echo 'active'; ?>">
                        <i class="bi bi-box"></i> Productos
                    </a>
                </li>
                <li>
                    <a href="empleados.php"
                        class="nav-link text-white <?php if ($current_page == 'empleados.php') echo 'active'; ?>">
                        <i class="bi bi-gear"></i> Empleados
                    </a>
                </li>
                <li>
                    <a href="clientes.php"
                        class="nav-link text-white <?php if ($current_page == 'clientes.php') echo 'active'; ?>">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                </li>
                <li>
                    <a href="categorias.php"
                        class="nav-link text-white <?php if ($current_page == 'categorias.php') echo 'active'; ?>">
                        <i class="bi bi-grid"></i> Categorías
                    </a>
                </li>
                <li>
                    <a href="servicios.php"
                        class="nav-link text-white <?php if ($current_page == 'servicios.php') echo 'active'; ?>">
                        <i class="bi bi-scissors"></i> Servicios
                    </a>
                </li>
                <li>
                    <a href="reservas.php"
                        class="nav-link text-white <?php if ($current_page == 'reservas.php') echo 'active'; ?>">
                        <i class="bi bi-briefcase"></i> Reservas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="nueva_venta.php" class="nav-link text-white <?php if ($current_page == 'nueva_venta.php') echo 'active'; ?>">
                        <i class="bi bi-cart-plus-fill"></i> Nueva Venta (POS)
                    </a>
                </li>
                <li>
                    <a href="ventas.php" class="nav-link text-white <?php if ($current_page == 'ventas.php') echo 'active'; ?>">
                        <i class="bi bi-tags"></i> Historial Ventas
                    </a>
                </li>
                <li>
                    <a href="kardex.php" class="nav-link text-white <?php if ($current_page == 'kardex.php') echo 'active'; ?>">
                        <i class="bi bi-bar-chart-line"></i> Kardex
                    </a>
                </li>
            </ul>
            <ul class="nav flex-column mt-auto">
                <li>
                    <a href="../process/logout.php" class="nav-link text-white">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-white">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($nombre_usuario); ?>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="main-content d-flex flex-column flex-grow-1">