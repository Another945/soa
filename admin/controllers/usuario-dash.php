<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$nombre_usuario = 'Invitado';

if (isset($_SESSION['username'])) {
    $nombre_usuario = $_SESSION['username'];
}
?>