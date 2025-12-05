<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || isLoggedIn()) {
    header('Location: ../pages/inicio.php');
    exit;
}

// Sanitizar datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$password = $_POST['password'] ?? '';

// Validaciones
if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || empty($password) || strlen($password) < 6) {
    header('Location: ../pages/register.php?error=Datos inválidos o incompletos.');
    exit;
}

// Verificar si el email ya existe en la tabla de usuarios
$stmt = $pdo->prepare("SELECT IdUsuarios FROM usuarios WHERE EmailUsuarios = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: ../pages/register.php?error=El email ya está registrado.');
    exit;
}


$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (NombreUsuarios, ApellidoUsuarios, EmailUsuarios, TelefonoUsuarios, DNIUsuarios, ContrasenaUsuario, EstadoUsuarios, FechaAltaUsuarios) 
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
        
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$nombre, $apellido, $email, $telefono, $dni, $hashedPassword])) {
    $_SESSION['success'] = '¡Registro completado! Ya puedes iniciar sesión.';
    header('Location: ../pages/login.php');
} else {
    header('Location: ../pages/register.php?error=Error al crear la cuenta.');
}
exit;
?>