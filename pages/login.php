<?php
require_once '../includes/functions.php';
if (isLoggedIn()) {
    header('Location: inicio.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - M Barber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/auth_style.css">
</head>
<body class="bg-barber">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6 col-xl-5">
                <div class="card auth-card text-white">
                    <div class="card-body">
                        <img src="../assets/img/logo.jpg" alt="Logo Barbería" class="auth-logo">
                        <h2 class="text-center mb-4">Iniciar Sesión</h2>
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?><div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
                        <form action="../process/process_login.php" method="POST">
                            <div class="mb-4 input-wrapper">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" name="identifier" placeholder="Email o Usuario" required>
                            </div>
                            <div class="mb-4 input-wrapper">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-custom-auth w-100">Ingresar</button>
                        </form>
                        <p class="text-center mt-4">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>