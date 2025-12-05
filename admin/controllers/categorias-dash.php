<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

// --- CRUD (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // EDITAR
    if (isset($_POST['editar_id'])) {
        $id = intval($_POST['editar_id']);
        $nombre = trim($_POST['editar_nombre']);
        $stmt = $conn->prepare("UPDATE categorias SET NombreCategorias=? WHERE IdCategorias=?");
        $stmt->bind_param("si", $nombre, $id);
        if ($stmt->execute()) { $_SESSION['message'] = ['type' => 'success', 'text' => 'Categoría actualizada.']; }
        else { $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al actualizar.']; }
        $stmt->close();
        header("Location: ../categorias.php"); exit;
    }
    // AGREGAR
    else {
        $nombre = trim($_POST['nombre']);
        if ($nombre !== '') {
            $stmt = $conn->prepare("INSERT INTO categorias (NombreCategorias, ActivoCategorias) VALUES (?, 1)");
            $stmt->bind_param("s", $nombre);
            if ($stmt->execute()) { $_SESSION['message'] = ['type' => 'success', 'text' => 'Categoría agregada.']; }
            else { $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al agregar.']; }
            $stmt->close();
        }
        header("Location: ../categorias.php"); exit;
    }
}

// --- ELIMINAR (GET) ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("UPDATE categorias SET ActivoCategorias = 0 WHERE IdCategorias = $id");
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Categoría desactivada.'];
    header("Location: ../categorias.php"); exit;
}

// ==========================================
// LÓGICA AJAX
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    $limite = 5; // Paginación de 5
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $limite;
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

    $where = "WHERE ActivoCategorias = 1";
    if ($busqueda) {
        $where .= " AND NombreCategorias LIKE '%$busqueda%'";
    }

    $total = $conn->query("SELECT COUNT(*) as total FROM categorias $where")->fetch_assoc()['total'];
    $paginas = ceil($total / $limite);

    $sql = "SELECT * FROM categorias $where ORDER BY NombreCategorias ASC LIMIT $limite OFFSET $offset";
    $result = $conn->query($sql);

    ob_start();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td class="fw-bold"><?php echo htmlspecialchars($row['NombreCategorias']); ?></td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarCategoria"
                        data-id="<?php echo $row['IdCategorias']; ?>"
                        data-nombre="<?php echo htmlspecialchars($row['NombreCategorias']); ?>">Editar</button>
                    <a href="controllers/categorias-dash.php?eliminar=<?php echo $row['IdCategorias']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Dar de baja?');">Baja</a>
                </td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="2" class="text-center">No se encontraron categorías.</td></tr>'; }
// ...
    $tabla = ob_get_clean();

    ob_start();
    if ($paginas > 1) { ?>
        <nav><ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarCategorias(<?php echo $pagina - 1; ?>); return false;">Anterior</a></li>
            <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>"><a class="page-link" href="#" onclick="cargarCategorias(<?php echo $i; ?>); return false;"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarCategorias(<?php echo $pagina + 1; ?>); return false;">Siguiente</a></li>
        </ul></nav>
    <?php }
    $paginacion = ob_get_clean();

    echo json_encode(['tabla' => $tabla, 'paginacion' => $paginacion]);
    exit;
}
?>