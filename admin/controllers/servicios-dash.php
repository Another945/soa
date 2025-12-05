<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php'; 

// --- FUNCIÓN PARA PROCESAR LA IMAGEN DEL SERVICIO ---
function procesarImagenServicio($file, $id_servicio) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../assets/img/servicios/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        
        $original_filename = pathinfo($file['name'], PATHINFO_FILENAME);
        $safe_filename = preg_replace('/[^a-z0-9_ -]/', '', strtolower($original_filename));
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'servicio_' . $id_servicio . '_' . time() . '.' . $file_extension;
        
        $upload_path = $upload_dir . $new_filename;
        $db_path = '../assets/img/servicios/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $db_path;
        }
    }
    return null;
}

// --- LÓGICA CRUD (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ACTUALIZAR
    if (isset($_POST['editar_id'])) {
        $idEditar = intval($_POST['editar_id']);
        $tipo = trim($_POST['editar_tipo']);
        $precio = floatval($_POST['editar_precio']);
        $descripcion = trim($_POST['editar_descripcion']);
        $duracion = intval($_POST['editar_duracion']);

        if ($tipo !== '' && $precio > 0 && $duracion > 0 && $idEditar > 0) {
            $stmt = $conn->prepare("UPDATE servicios SET TipoServicios=?, PrecioServicios=?, DescripcionServicios=?, DuracionMinutos=? WHERE IdServicios=?");
            $stmt->bind_param("sdsii", $tipo, $precio, $descripcion, $duracion, $idEditar);
            $stmt->execute();
            $stmt->close();
        
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $ruta_imagen = procesarImagenServicio($_FILES['imagen'], $idEditar);
                if ($ruta_imagen) {
                    // Lógica simplificada para actualizar imagen
                    $conn->query("DELETE FROM imagenes WHERE Tipo='servicio' AND IdRelacionado=$idEditar");
                    $stmt_img = $conn->prepare("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('servicio', ?, ?)");
                    $stmt_img->bind_param("is", $idEditar, $ruta_imagen);
                    $stmt_img->execute();
                }
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Servicio actualizado.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Datos inválidos.'];
        }
        header("Location: ../servicios.php"); exit;
    } 
    // CREAR
    else {
        $tipo = trim($_POST['tipo']);
        $precio = floatval($_POST['precio']);
        $descripcion = trim($_POST['descripcion']);
        $duracion = intval($_POST['duracion']);

        if ($tipo !== '' && $precio > 0 && $duracion > 0) {
            $stmt = $conn->prepare("INSERT INTO servicios (TipoServicios, PrecioServicios, DescripcionServicios, DuracionMinutos, ActivoServicios) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("sdsi", $tipo, $precio, $descripcion, $duracion);
            
            if ($stmt->execute()) {
                $id_nuevo = $conn->insert_id;
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $ruta_imagen = procesarImagenServicio($_FILES['imagen'], $id_nuevo);
                    if ($ruta_imagen) {
                        $conn->query("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('servicio', $id_nuevo, '$ruta_imagen')");
                    }
                }
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Servicio agregado.'];
            }
            $stmt->close();
        }
        header("Location: ../servicios.php"); exit;
    }
}

// --- ELIMINAR (GET) ---
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $conn->query("UPDATE servicios SET ActivoServicios = 0 WHERE IdServicios = $idEliminar");
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Servicio eliminado.'];
    header("Location: ../servicios.php"); exit;
}

// ==========================================
// LÓGICA AJAX (Listar)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    $limite = 5; // Paginación de 5
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $limite;
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

    $where = "WHERE s.ActivoServicios = 1";
    if ($busqueda) {
        $where .= " AND (s.TipoServicios LIKE '%$busqueda%' OR s.DescripcionServicios LIKE '%$busqueda%')";
    }

    $total = $conn->query("SELECT COUNT(*) as total FROM servicios s $where")->fetch_assoc()['total'];
    $paginas = ceil($total / $limite);

    $sql = "SELECT s.*, i.RutaImagen FROM servicios s LEFT JOIN imagenes i ON s.IdServicios = i.IdRelacionado AND i.Tipo = 'servicio' $where ORDER BY s.TipoServicios ASC LIMIT $limite OFFSET $offset";
    $result = $conn->query($sql);

    ob_start();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { 
            $img = $row['RutaImagen'] ?? '../assets/img/servicios/default.jpg';
            ?>
            <tr>
                <td><img src="<?php echo htmlspecialchars($img); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;"></td>
                <td><?php echo htmlspecialchars($row['TipoServicios']); ?></td>
                <td>S/ <?php echo number_format($row['PrecioServicios'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['DescripcionServicios']); ?></td>
                <td><?php echo htmlspecialchars($row['DuracionMinutos']); ?> min</td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarServicio"
                        data-id="<?php echo $row['IdServicios']; ?>"
                        data-tipo="<?php echo htmlspecialchars($row['TipoServicios']); ?>"
                        data-precio="<?php echo $row['PrecioServicios']; ?>"
                        data-descripcion="<?php echo htmlspecialchars($row['DescripcionServicios']); ?>"
                        data-duracion="<?php echo $row['DuracionMinutos']; ?>"
                        data-imagen="<?php echo htmlspecialchars($img); ?>">Editar</button>
                    <a href="controllers/servicios-dash.php?eliminar=<?php echo $row['IdServicios']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Dar de baja?');">Baja</a>
                </td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="6" class="text-center">No se encontraron servicios.</td></tr>'; }
    $tabla = ob_get_clean();

    ob_start();
    if ($paginas > 1) { ?>
        <nav><ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarServicios(<?php echo $pagina - 1; ?>); return false;">Anterior</a></li>
            <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>"><a class="page-link" href="#" onclick="cargarServicios(<?php echo $i; ?>); return false;"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarServicios(<?php echo $pagina + 1; ?>); return false;">Siguiente</a></li>
        </ul></nav>
    <?php }
    $paginacion = ob_get_clean();

    echo json_encode(['tabla' => $tabla, 'paginacion' => $paginacion]);
    exit;
}
?>