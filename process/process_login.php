<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$identifier = trim($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    header('Location: ../pages/login.php?error=Credenciales requeridas');
    exit;
}

$stmt = $pdo->prepare("SELECT IdUsuarios, NombreUsuarios, ContrasenaUsuario, EmailUsuarios FROM usuarios WHERE EmailUsuarios = ? AND EstadoUsuarios = 1");
$stmt->execute([$identifier]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['ContrasenaUsuario'])) {
    $otp = rand(100000, 999999);
    $_SESSION['otp_code'] = (string)$otp;
    $_SESSION['otp_user_id'] = $user['IdUsuarios'];
    $_SESSION['otp_user_name'] = $user['NombreUsuarios'];

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 2;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elmercancer0907@gmail.com';
        $mail->Password   = 'pvpvhwzloneyaqsr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('no-reply@mbarber.com', 'M Barber');
        $mail->addAddress($user['EmailUsuarios'], $user['NombreUsuarios']);
        $mail->isHTML(true);
        $mail->Subject = 'Tu código de verificación';
        $mail->Body    = "Hola {$user['NombreUsuarios']},<br><br>Tu código para iniciar sesión es: <h2><b>{$otp}</b></h2>";
        
        $mail->send();
        
        header('Location: ../pages/verify_otp.php');
        exit;

    } catch (Exception $e) {
        echo "El mensaje no se pudo enviar. Error de PHPMailer: {$mail->ErrorInfo}";
        exit;
    }
}

$stmt = $pdo->prepare("SELECT IdAdministrador, UsuarioAdministrador, ContrasenaAdministrador FROM administrador WHERE UsuarioAdministrador = ?");
$stmt->execute([$identifier]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['ContrasenaAdministrador'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $admin['IdAdministrador'];
    $_SESSION['username'] = $admin['UsuarioAdministrador'];
    $_SESSION['user_type'] = 'administrador';
    header('Location: ../admin/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT IdEmpleados, NombreEmpleado, ContrasenaEmpleados FROM empleados WHERE EmailEmpleados = ?");
$stmt->execute([$identifier]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if ($employee && password_verify($password, $employee['ContrasenaEmpleados'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $employee['IdEmpleados'];
    $_SESSION['username'] = $employee['NombreEmpleado'];
    $_SESSION['user_type'] = 'empleado';
    header('Location: ../admin/dashboard.php');
    exit;
}

header('Location: ../pages/login.php?error=Credenciales inválidas');
exit;