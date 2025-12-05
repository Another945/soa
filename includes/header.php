<?php
// Lógica para detectar la página actual
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbería M BARBER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="../assets/img/logo.jpg">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../pages/inicio.php"><img src="../assets/img/logo.jpg" alt="LogoMBarber" style="height: 7vh;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link <?php if ($currentPage == 'inicio.php') echo 'active'; ?>" href="../pages/inicio.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link <?php if ($currentPage == 'productos.php') echo 'active'; ?>" href="../pages/productos.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link <?php if ($currentPage == 'servicios.php') echo 'active'; ?>" href="../pages/servicios.php">Servicios</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($currentPage == 'carrito.php') echo 'active'; ?>" href="../pages/carrito.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-count" class="badge bg-warning text-dark ms-1"><?php echo count(getCart()); ?></span>
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php if ($currentPage == 'perfil.php') echo 'active'; ?>" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> Hola, <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="../pages/perfil.php">Mi Perfil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../process/logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?php if ($currentPage == 'login.php') echo 'active'; ?>" href="../pages/login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link <?php if ($currentPage == 'register.php') echo 'active'; ?>" href="../pages/register.php">Registro</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">