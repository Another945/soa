<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['otp_user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$submitted_otp = trim($_POST['otp'] ?? '');
$stored_otp = $_SESSION['otp_code'] ?? '';

if ($submitted_otp === $stored_otp) {

    session_regenerate_id(true);
    $_SESSION['user_id'] = $_SESSION['otp_user_id'];
    $_SESSION['username'] = $_SESSION['otp_user_name'];
    $_SESSION['user_type'] = 'cliente';

    unset($_SESSION['otp_code'], $_SESSION['otp_user_id'], $_SESSION['otp_user_name']);

    header('Location: ../pages/inicio.php?success=login');
    exit;
} else {
    header('Location: ../pages/verify_otp.php?error=Código incorrecto. Inténtalo de nuevo.');
    exit;
}