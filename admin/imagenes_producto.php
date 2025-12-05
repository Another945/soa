<?php
include 'layouts/header.php';

if (!isset($_GET['id_producto']) || !is_numeric($_GET['id_producto'])) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>ID de producto no válido.</div></div>";
    include 'layouts/footer.php';
    exit;
}

$id_producto = intval($_GET['id_producto']);

// Obtener info del producto
$stmt_prod = $conn->prepare("SELECT NombreProductos FROM productos WHERE IdProductos = ?");
$stmt_prod->bind_param("i", $id_producto);
$stmt_prod->execute();
$producto = $stmt_prod->get_result()->fetch_assoc();

if (!$producto) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Producto no encontrado.</div></div>";
    include 'layouts/footer.php';
    exit;
}

$nombre_producto = $producto['NombreProductos'];

// Lógica para SUBIR imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen_producto'])) {
    $file = $_FILES['imagen_producto'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/img/productos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'producto_' . $id_producto . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        $db_path = '../assets/img/productos/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Insertar en la base de datos
            $stmt_insert = $conn->prepare("INSERT INTO imagenes (Tipo, IdRelacionado, RutaImagen) VALUES ('producto', ?, ?)");
            $stmt_insert->bind_param("is", $id_producto, $db_path);
            if ($stmt_insert->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Imagen subida y guardada correctamente.'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al guardar la ruta en la base de datos.'];
            }
            $stmt_insert->close();
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al mover el archivo subido.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al subir el archivo. Código: ' . $file['error']];
    }
    header("Location: imagenes_producto.php?id_producto=" . $id_producto);
    exit;
}

// Lógica para ELIMINAR imagen
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id_imagen'])) {
    $id_imagen = intval($_GET['id_imagen']);

    // Obtener ruta para borrar archivo físico
    $stmt_get = $conn->prepare("SELECT RutaImagen FROM imagenes WHERE IdImagen = ? AND IdRelacionado = ?");
    $stmt_get->bind_param("ii", $id_imagen, $id_producto);
    $stmt_get->execute();
    $imagen_a_borrar = $stmt_get->get_result()->fetch_assoc();

    if ($imagen_a_borrar) {
        // Borrar de la base de datos
        $stmt_delete = $conn->prepare("DELETE FROM imagenes WHERE IdImagen = ?");
        $stmt_delete->bind_param("i", $id_imagen);
        if ($stmt_delete->execute()) {
            // Borrar archivo físico si existe
            if (file_exists($imagen_a_borrar['RutaImagen'])) {
                unlink($imagen_a_borrar['RutaImagen']);
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Imagen eliminada correctamente.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al eliminar la imagen de la base de datos.'];
        }
        $stmt_delete->close();
    }
    header("Location: imagenes_producto.php?id_producto=" . $id_producto);
    exit;
}

// Obtener imágenes existentes
$stmt_imgs = $conn->prepare("SELECT IdImagen, RutaImagen FROM imagenes WHERE Tipo = 'producto' AND IdRelacionado = ? ORDER BY IdImagen DESC");
$stmt_imgs->bind_param("i", $id_producto);
$stmt_imgs->execute();
$imagenes = $stmt_imgs->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Imágenes de: <?php echo htmlspecialchars($nombre_producto); ?></h1>
        <a href="productos.php" class="btn btn-secondary">Volver a Productos</a>
    </div>

    <!-- Mensajes de alerta -->
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']['text']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); endif; ?>

    <!-- Formulario para subir nueva imagen -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Subir Nueva Imagen</h5>
        </div>
        <div class="card-body">
            <form action="imagenes_producto.php?id_producto=<?php echo $id_producto; ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="imagen_producto" class="form-label">Seleccionar archivo (JPG, PNG, WEBP)</label>
                    <input class="form-control" type="file" id="imagen_producto" name="imagen_producto" accept="image/jpeg, image/png, image/webp" required>
                </div>
                <button type="submit" class="btn btn-primary">Subir Imagen</button>
            </form>
        </div>
    </div>

    <!-- Galería de imágenes existentes -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Galería de Imágenes</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if (empty($imagenes)): ?>
                    <p class="text-center">Este producto no tiene imágenes.</p>
                <?php else: ?>
                    <?php foreach ($imagenes as $img): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($img['RutaImagen']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <div class="card-footer text-center">
                                    <a href="imagenes_producto.php?id_producto=<?php echo $id_producto; ?>&action=delete&id_imagen=<?php echo $img['IdImagen']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta imagen?');">
                                        Eliminar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>