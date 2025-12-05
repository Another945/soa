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
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if ($new_password !== $confirm_password) {
    header('Location: ../pages/perfil.php?error=Las nuevas contraseñas no coinciden.');
    exit;
}

// Obtener la contraseña actual del usuario
$stmt = $pdo->prepare("SELECT ContrasenaUsuario FROM usuarios WHERE IdUsuarios = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($current_password, $user['ContrasenaUsuario'])) {
    header('Location: ../pages/perfil.php?error=La contraseña actual es incorrecta.');
    exit;
}

// Hashear y actualizar la nueva contraseña
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$updateStmt = $pdo->prepare("UPDATE usuarios SET ContrasenaUsuario = ? WHERE IdUsuarios = ?");

if ($updateStmt->execute([$new_hashed_password, $userId])) {
    header('Location: ../pages/perfil.php?success=1');
} else {
    header('Location: ../pages/perfil.php?error=No se pudo cambiar la contraseña.');
}
exit;