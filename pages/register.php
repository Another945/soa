<?php require_once '../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Estilo Urbano</title>
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
                        <h2 class="text-center mb-4">Crear Cuenta</h2>
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
                        <form action="../process/process_register.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3 input-wrapper">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="nombre" placeholder="Nombre" required>
                                </div>
                                <div class="col-md-6 mb-3 input-wrapper">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="apellido" placeholder="Apellido" required>
                                </div>
                            </div>
                            <div class="mb-3 input-wrapper">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>
                            <div class="mb-3 input-wrapper">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" name="telefono" placeholder="Teléfono" required>
                            </div>
                            <div class="mb-4 input-wrapper">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Contraseña" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-custom-auth w-100">Registrarse</button>
                        </form>
                        <p class="text-center mt-4">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>