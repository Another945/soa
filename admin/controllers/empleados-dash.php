<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

// --- FUNCIÓN PARA SUBIR IMAGEN ---
function procesarImagenEmpleado($file, $id_empleado) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Carpeta de destino (creada si no existe)
        $upload_dir = __DIR__ . '/../../assets/img/barberos/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'barbero_' . $id_empleado . '_' . time() . '.' . $ext;
        $upload_path = $upload_dir . $new_name;
        $db_path = '../assets/img/barberos/' . $new_name;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $db_path;
        }
    }
    return null;
}

// --- LÓGICA CRUD (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // EDITAR EMPLEADO
    if (isset($_POST['editar_id'])) {
        $id = intval($_POST['editar_id']);
        $nombre = trim($_POST['editar_nombre']);
        $apellido = trim($_POST['editar_apellido']);
        $dni = trim($_POST['editar_dni']);
        $telefono = trim($_POST['editar_telefono']);
        $email = trim($_POST['editar_email']);
        $rol = trim($_POST['editar_rol']);

        $stmt = $conn->prepare("UPDATE empleados SET NombreEmpleado=?, ApellidoEmpleados=?, DNIEmpleados=?, TelefonoEmpleados=?, EmailEmpleados=?, RolEmpleados=? WHERE IdEmpleados=?");
        $stmt->bind_param("ssssssi", $nombre, $apellido, $dni, $telefono, $email, $rol, $id);
        $stmt->execute();
        $stmt->close();

        // Procesar nueva imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $ruta = procesarImagenEmpleado($_FILES['imagen'], $id);
            if ($ruta) {
                // Borrar referencia anterior y actualizar
                $conn->query("DELETE FROM imagenes WHERE Tipo='barbero' AND IdRelacionado=$id");
                $conn->query("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('barbero', $id, '$ruta')");
            }
        }
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Empleado actualizado correctamente.'];
        header("Location: ../empleados.php"); exit;
    }
    
    // AGREGAR EMPLEADO
    else {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $dni = trim($_POST['dni']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $rol = trim($_POST['rol']);
        $password = password_hash("123456", PASSWORD_DEFAULT); // Contraseña por defecto

        $stmt = $conn->prepare("INSERT INTO empleados (NombreEmpleado, ApellidoEmpleados, DNIEmpleados, TelefonoEmpleados, EmailEmpleados, RolEmpleados, ContrasenaEmpleados, Activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssss", $nombre, $apellido, $dni, $telefono, $email, $rol, $password);
        
        if ($stmt->execute()) {
            $id_nuevo = $conn->insert_id;
            // Procesar imagen al crear
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $ruta = procesarImagenEmpleado($_FILES['imagen'], $id_nuevo);
                if ($ruta) {
                    $conn->query("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('barbero', $id_nuevo, '$ruta')");
                }
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Empleado agregado correctamente.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al agregar empleado.'];
        }
        $stmt->close();
        header("Location: ../empleados.php"); exit;
    }
}

// --- ELIMINAR EMPLEADO (GET) ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("UPDATE empleados SET Activo = 0 WHERE IdEmpleados = $id");
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Empleado dado de baja.'];
    header("Location: ../empleados.php"); exit;
}

// ==========================================
// LÓGICA AJAX (Listar, Buscar, Paginar)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    $limite = 5; // Registros por página
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $limite;
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

    $where = "WHERE e.Activo = 1";
    if ($busqueda) {
        $where .= " AND (e.NombreEmpleado LIKE '%$busqueda%' OR e.ApellidoEmpleados LIKE '%$busqueda%' OR e.DNIEmpleados LIKE '%$busqueda%')";
    }

    // Contar total para paginación
    $total = $conn->query("SELECT COUNT(*) as total FROM empleados e $where")->fetch_assoc()['total'];
    $paginas = ceil($total / $limite);

    // Consulta principal con Imagen
    $sql = "SELECT e.*, i.RutaImagen 
            FROM empleados e 
            LEFT JOIN imagenes i ON e.IdEmpleados = i.IdRelacionado AND i.Tipo = 'barbero' 
            $where 
            ORDER BY e.NombreEmpleado ASC 
            LIMIT $limite OFFSET $offset";
    $result = $conn->query($sql);

    // Generar Tabla HTML
    ob_start();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { 
            $img = $row['RutaImagen'] ?? '../assets/img/barberos/default.jpg';
            ?>
            <tr>
                <td class="text-center">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Foto" class="rounded-circle shadow-sm" style="width: 70px; height: 70px; object-fit: cover; border: 2px solid #dee2e6;">
                </td>
                <td class="fw-bold text-dark align-middle"><?php echo htmlspecialchars($row['NombreEmpleado'] . ' ' . $row['ApellidoEmpleados']); ?></td>
                <td class="align-middle"><?php echo htmlspecialchars($row['DNIEmpleados']); ?></td>
                <td class="align-middle"><?php echo htmlspecialchars($row['TelefonoEmpleados']); ?></td>
                <td class="align-middle text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['EmailEmpleados']); ?>">
                    <?php echo htmlspecialchars($row['EmailEmpleados']); ?>
                </td>
                <td class="text-center align-middle">
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarEmpleado"
                        data-id="<?php echo $row['IdEmpleados']; ?>"
                        data-nombre="<?php echo htmlspecialchars($row['NombreEmpleado']); ?>"
                        data-apellido="<?php echo htmlspecialchars($row['ApellidoEmpleados']); ?>"
                        data-dni="<?php echo htmlspecialchars($row['DNIEmpleados']); ?>"
                        data-telefono="<?php echo htmlspecialchars($row['TelefonoEmpleados']); ?>"
                        data-email="<?php echo htmlspecialchars($row['EmailEmpleados']); ?>"
                        data-rol="<?php echo htmlspecialchars($row['RolEmpleados']); ?>"
                        data-imagen="<?php echo htmlspecialchars($img); ?>">Editar</button>
                    <a href="controllers/empleados-dash.php?eliminar=<?php echo $row['IdEmpleados']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Dar de baja?');">Baja</a>
                </td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="6" class="text-center py-4">No se encontraron empleados.</td></tr>'; }
    $tabla = ob_get_clean();

    // Generar Paginación HTML
    ob_start();
    if ($paginas > 1) { ?>
        <nav><ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarEmpleados(<?php echo $pagina - 1; ?>); return false;">Anterior</a></li>
            <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>"><a class="page-link" href="#" onclick="cargarEmpleados(<?php echo $i; ?>); return false;"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarEmpleados(<?php echo $pagina + 1; ?>); return false;">Siguiente</a></li>
        </ul></nav>
    <?php }
    $paginacion = ob_get_clean();

    echo json_encode(['tabla' => $tabla, 'paginacion' => $paginacion]);
    exit;
}
?>