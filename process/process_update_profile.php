<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/perfil.php');
    exit;
}

$userId = $_SESSION['user_id'];
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$email = trim($_POST['email']);
$telefono = trim($_POST['telefono']);
$dni = trim($_POST['dni']);

// Validar que el nuevo email no esté en uso por otro usuario
$stmt = $pdo->prepare("SELECT IdUsuarios FROM usuarios WHERE EmailUsuarios = ? AND IdUsuarios != ?");
$stmt->execute([$email, $userId]);
if ($stmt->fetch()) {
    header('Location: ../pages/perfil.php?error=El email ya está en uso por otra cuenta.');
    exit;
}

// Actualizar los datos
$updateStmt = $pdo->prepare("UPDATE usuarios SET NombreUsuarios = ?, ApellidoUsuarios = ?, EmailUsuarios = ?, TelefonoUsuarios = ?, DNIUsuarios = ? WHERE IdUsuarios = ?");
if ($updateStmt->execute([$nombre, $apellido, $email, $telefono, $dni, $userId])) {
    header('Location: ../pages/perfil.php?success=1');
} else {
    header('Location: ../pages/perfil.php?error=No se pudieron actualizar los datos.');
}
exit;