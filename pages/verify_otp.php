<?php
session_start();
if (!isset($_SESSION['otp_user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Código - M Barber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/auth_style.css">
</head>
<body class="bg-barber">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card auth-card text-white">
                    <div class="card-body">
                        <img src="../assets/img/logo.jpg" alt="Logo Barbería" class="auth-logo">
                        <h2 class="text-center mb-3">Verificación</h2>
                        <p class="text-center form-text mb-4">Ingresa el código de 6 dígitos que enviamos a tu correo.</p>
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
                        <form action="../process/process_otp.php" method="POST">
                            <div class="mb-4 otp-input-container">
                                <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                                <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                                <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                                <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                                <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                                <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                            </div>
                            <input type="hidden" name="otp" id="otp-hidden">
                            <button type="submit" class="btn btn-custom-auth w-100">Verificar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('otp-hidden');
        const form = document.querySelector('form');

        // Enfocar el primer input al cargar la página
        window.addEventListener('load', () => inputs[0].focus());

        inputs.forEach((input, index) => {
            // Evento para mover el foco hacia ADELANTE
            input.addEventListener('input', () => {
                const value = input.value;
                // Si se escribe un dígito y no es el último cuadro, pasar al siguiente
                if (value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateHiddenInput();
            });

            // Evento para mover el foco hacia ATRÁS
            input.addEventListener('keydown', (e) => {
                // Si se presiona Backspace en un cuadro vacío y no es el primero, ir al anterior
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Evento para manejar el PEGADO de código
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').trim().slice(0, 6);
                
                // Distribuir los caracteres pegados en los cuadros
                for (let i = 0; i < pastedData.length; i++) {
                    if (inputs[i]) {
                        inputs[i].value = pastedData[i];
                    }
                }
                
                // Enfocar el último cuadro rellenado o el último cuadro disponible
                const focusIndex = Math.min(pastedData.length, inputs.length - 1);
                inputs[focusIndex].focus();
                
                updateHiddenInput();
            });
        });

        // Función para unir los dígitos en el campo oculto que se enviará
        function updateHiddenInput() {
            let otp = "";
            inputs.forEach(input => {
                otp += input.value;
            });
            hiddenInput.value = otp;
        }
    </script>
</body>
</html>