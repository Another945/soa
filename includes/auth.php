<?php
require_once 'config.php';
require_once 'functions.php';


function requireClientLogin() {
    if (!isLoggedIn()) {
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'pages/inicio.php';
        header('Location: pages/login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: admin/login.php');
        exit;
    }
}

?>