<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

function procesarImagenProducto($file, $id_producto) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../assets/img/productos/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'prod_' . $id_producto . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
            return '../assets/img/productos/' . $new_name;
        }
    }
    return null;
}

// --- CRUD (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // EDITAR
    if (isset($_POST['editar_id'])) {
        $id = intval($_POST['editar_id']);
        $nombre = $_POST['editar_nombre'];
        $desc = $_POST['editar_descripcion'];
        $precio = $_POST['editar_precio'];
        $stock = $_POST['editar_stock'];
        $cat = $_POST['editar_categoria'];
        
        $stmt = $conn->prepare("UPDATE productos SET NombreProductos=?, DescripcionProductos=?, PrecioProductos=?, StockProductos=?, IdCategorias=? WHERE IdProductos=?");
        $stmt->bind_param("ssdiii", $nombre, $desc, $precio, $stock, $cat, $id);
        $stmt->execute();
        $stmt->close();

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $ruta = procesarImagenProducto($_FILES['imagen'], $id);
            if ($ruta) {
                $conn->query("DELETE FROM imagenes WHERE Tipo='producto' AND IdRelacionado=$id");
                $conn->query("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('producto', $id, '$ruta')");
            }
        }
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Producto actualizado.'];
        header("Location: ../productos.php"); exit;
    }
    // AGREGAR
    else {
        $nombre = $_POST['nombre'];
        $desc = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $cat = $_POST['categoria'];

        $stmt = $conn->prepare("INSERT INTO productos (NombreProductos, DescripcionProductos, PrecioProductos, StockProductos, IdCategorias, ActivoProductos) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssdii", $nombre, $desc, $precio, $stock, $cat);
        $stmt->execute();
        $id_nuevo = $conn->insert_id;
        $stmt->close();

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $ruta = procesarImagenProducto($_FILES['imagen'], $id_nuevo);
            if ($ruta) {
                $conn->query("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('producto', $id_nuevo, '$ruta')");
            }
        }
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Producto agregado.'];
        header("Location: ../productos.php"); exit;
    }
}

// --- ELIMINAR (GET) ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("UPDATE productos SET ActivoProductos = 0 WHERE IdProductos = $id");
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Producto eliminado.'];
    header("Location: ../productos.php"); exit;
}

// --- AJAX LISTAR ---
if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    $limite = 5;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $limite;
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

    $where = "WHERE p.ActivoProductos = 1";
    if ($busqueda) { $where .= " AND p.NombreProductos LIKE '%$busqueda%'"; }

    $total = $conn->query("SELECT COUNT(*) as total FROM productos p $where")->fetch_assoc()['total'];
    $paginas = ceil($total / $limite);

    $sql = "SELECT p.*, i.RutaImagen FROM productos p LEFT JOIN imagenes i ON p.IdProductos = i.IdRelacionado AND i.Tipo = 'producto' $where ORDER BY p.NombreProductos ASC LIMIT $limite OFFSET $offset";
    $result = $conn->query($sql);

    ob_start();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $img = $row['RutaImagen'] ?? '../assets/img/productos/default.jpg';
            ?>
            <tr>
                <td><img src="<?php echo htmlspecialchars($img); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                <td><?php echo htmlspecialchars($row['NombreProductos']); ?></td>
                <td><?php echo htmlspecialchars($row['DescripcionProductos']); ?></td>
                <td>S/ <?php echo number_format($row['PrecioProductos'], 2); ?></td>
                <td><?php echo $row['StockProductos']; ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarProducto"
                        data-id="<?php echo $row['IdProductos']; ?>"
                        data-nombre="<?php echo htmlspecialchars($row['NombreProductos']); ?>"
                        data-descripcion="<?php echo htmlspecialchars($row['DescripcionProductos']); ?>"
                        data-precio="<?php echo $row['PrecioProductos']; ?>"
                        data-stock="<?php echo $row['StockProductos']; ?>"
                        data-categoria="<?php echo $row['IdCategorias']; ?>"
                        data-imagen="<?php echo htmlspecialchars($img); ?>">Editar</button>
                    <a href="controllers/productos-dash.php?eliminar=<?php echo $row['IdProductos']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?');">Baja</a>
                </td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="6" class="text-center">No se encontraron productos.</td></tr>'; }
    $tabla = ob_get_clean();

    ob_start();
    if ($paginas > 1) { ?>
        <nav><ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarProductos(<?php echo $pagina - 1; ?>); return false;">Anterior</a></li>
            <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>"><a class="page-link" href="#" onclick="cargarProductos(<?php echo $i; ?>); return false;"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarProductos(<?php echo $pagina + 1; ?>); return false;">Siguiente</a></li>
        </ul></nav>
    <?php }
    $paginacion = ob_get_clean();

    echo json_encode(['tabla' => $tabla, 'paginacion' => $paginacion]);
    exit;
}
?>