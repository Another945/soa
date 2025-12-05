<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- CONEXIÓN ROBUSTA (BUSCA EL ARCHIVO DONDE ESTÉ) ---
// Intentamos cargar functions.php que ya trae la conexión ($pdo)
$rutas_posibles = [
    __DIR__ . '/../../includes/functions.php',  // Ruta estándar
    __DIR__ . '/../includes/functions.php',     // Alternativa
    __DIR__ . '/../../config.php',              // Alternativa
    '../../includes/functions.php'              // Ruta relativa simple
];

$conectado = false;
foreach ($rutas_posibles as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        $conectado = true;
        break;
    }
}

if (!$conectado || !isset($pdo)) {
    // Fallback manual si no encuentra los archivos
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=barberiaestilourbano;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error crítico: No se pudo conectar a la base de datos.");
    }
}

// 1. VERIFICACIÓN DE ROL
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'administrador' && $_SESSION['user_type'] !== 'empleado')) {
    header("Location: ../../pages/login.php");
    exit;
}

// 2. PROCESAR ACCIONES (Confirmar, Cancelar, Eliminar)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($id > 0) {
        if ($action === 'cambiar_estado' && isset($_GET['estado'])) {
            $nuevo_estado = $_GET['estado'];
            $stmt = $pdo->prepare("UPDATE reservas SET EstadoReservas = ? WHERE IdReservas = ?");
            if ($stmt->execute([$nuevo_estado, $id])) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Estado actualizado correctamente.'];
            }
        } 
        elseif ($action === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM reservas WHERE IdReservas = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Reserva eliminada.'];
            }
        }
    }
    // Redirigir de vuelta a la lista
    header("Location: ../reservas.php");
    exit();
}

// 3. OBTENER DATOS PARA LA VISTA
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$userRole = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];

// Filtros
$where_sql = "WHERE 1=1";
$params = [];

if ($busqueda) {
    $where_sql .= " AND (u.NombreUsuarios LIKE ? OR s.TipoServicios LIKE ? OR e.NombreEmpleado LIKE ?)";
    $term = "%$busqueda%";
    $params = array_merge($params, [$term, $term, $term]);
}

if ($userRole === 'empleado') {
    $where_sql .= " AND r.IdBarberos = ?";
    $params[] = $userId;
}

// Consulta Principal (Ordenada por fecha reciente)
$sql = "
    SELECT r.IdReservas, r.FechaReservas, r.HoraReservas, r.EstadoReservas, r.MotivoCancelacion,
           u.NombreUsuarios, u.ApellidoUsuarios, s.TipoServicios,
           e.NombreEmpleado, e.ApellidoEmpleados
    FROM reservas r
    JOIN usuarios u ON r.IdUsuarios = u.IdUsuarios
    JOIN servicios s ON r.IdServicios = s.IdServicios
    LEFT JOIN empleados e ON r.IdBarberos = e.IdEmpleados
    $where_sql
    ORDER BY r.FechaReservas DESC, r.HoraReservas DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función auxiliar para colores
function getBadgeClass($estado) {
    switch (strtolower($estado)) {
        case 'confirmado': return 'bg-success';
        case 'completado': return 'bg-primary';
        case 'cancelado': return 'bg-danger';
        default: return 'bg-warning text-dark';
    }
}
?>