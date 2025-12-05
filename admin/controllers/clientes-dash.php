<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

// --- LÓGICA CRUD (POST/GET para Eliminar/Editar) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $id = intval($_POST['editar_id']);
    $nombre = $_POST['editar_nombre'];
    $apellido = $_POST['editar_apellido'];
    $email = $_POST['editar_email'];
    $dni = $_POST['editar_dni'];
    $telefono = $_POST['editar_telefono'];
    
    $stmt = $conn->prepare("UPDATE usuarios SET NombreUsuarios=?, ApellidoUsuarios=?, EmailUsuarios=?, DNIUsuarios=?, TelefonoUsuarios=? WHERE IdUsuarios=?");
    $stmt->bind_param("sssssi", $nombre, $apellido, $email, $dni, $telefono, $id);
    
    if ($stmt->execute()) { $_SESSION['message'] = ['type' => 'success', 'text' => 'Cliente actualizado.']; }
    else { $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al actualizar.']; }
    header("Location: ../clientes.php"); exit;
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("UPDATE usuarios SET EstadoUsuarios = 0 WHERE IdUsuarios = $id");
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Cliente dado de baja.'];
    header("Location: ../clientes.php"); exit;
}

// ==========================================
// LÓGICA AJAX PARA BÚSQUEDA Y PAGINACIÓN
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    $limite = 10;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $limite;
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

    $where = "WHERE EstadoUsuarios = 1";
    if ($busqueda) {
        $where .= " AND (NombreUsuarios LIKE '%$busqueda%' OR ApellidoUsuarios LIKE '%$busqueda%' OR EmailUsuarios LIKE '%$busqueda%' OR DNIUsuarios LIKE '%$busqueda%')";
    }

    $total = $conn->query("SELECT COUNT(*) as total FROM usuarios $where")->fetch_assoc()['total'];
    $paginas = ceil($total / $limite);

    $sql = "SELECT * FROM usuarios $where ORDER BY FechaAltaUsuarios DESC LIMIT $limite OFFSET $offset";
    $result = $conn->query($sql);

    ob_start();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['NombreUsuarios'] . ' ' . $row['ApellidoUsuarios']); ?></td>
                <td><?php echo htmlspecialchars($row['EmailUsuarios']); ?></td>
                <td><?php echo htmlspecialchars($row['DNIUsuarios']); ?></td>
                <td><?php echo htmlspecialchars($row['TelefonoUsuarios']); ?></td>
                <td><?php echo htmlspecialchars($row['FechaAltaUsuarios']); ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario"
                        data-id="<?php echo $row['IdUsuarios']; ?>"
                        data-nombre="<?php echo htmlspecialchars($row['NombreUsuarios']); ?>"
                        data-apellido="<?php echo htmlspecialchars($row['ApellidoUsuarios']); ?>"
                        data-email="<?php echo htmlspecialchars($row['EmailUsuarios']); ?>"
                        data-dni="<?php echo htmlspecialchars($row['DNIUsuarios']); ?>"
                        data-telefono="<?php echo htmlspecialchars($row['TelefonoUsuarios']); ?>">Editar</button>
                    <a href="controllers/clientes-dash.php?eliminar=<?php echo $row['IdUsuarios']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Dar de baja?');">Baja</a>
                </td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="6" class="text-center">No se encontraron clientes.</td></tr>'; }
    $tabla = ob_get_clean();

    ob_start();
    if ($paginas > 1) { ?>
        <nav><ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarClientes(<?php echo $pagina - 1; ?>); return false;">Anterior</a></li>
            <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>"><a class="page-link" href="#" onclick="cargarClientes(<?php echo $i; ?>); return false;"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarClientes(<?php echo $pagina + 1; ?>); return false;">Siguiente</a></li>
        </ul></nav>
    <?php }
    $paginacion = ob_get_clean();

    echo json_encode(['tabla' => $tabla, 'paginacion' => $paginacion]);
    exit;
}
?>