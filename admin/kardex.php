<?php 
include 'layouts/header.php'; 

// Cargar datos para los filtros (Productos y Categorías)
$productos_list = $conn->query("SELECT IdProductos, NombreProductos FROM productos WHERE ActivoProductos = 1 ORDER BY NombreProductos");
$categorias_list = $conn->query("SELECT IdCategorias, NombreCategorias FROM categorias WHERE ActivoCategorias = 1 ORDER BY NombreCategorias");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="container-fluid p-4">
    <h1 class="mb-4">Kardex de Inventario</h1>
    
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h5 class="mb-0">Control de Stock</h5>
            <div>
                <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tablaKardex')">Excel</button>
                <button class="btn btn-danger btn-sm" onclick="exportTableToPDF()">PDF</button>
            </div>
        </div>
        <div class="card-body">
            
            <div class="row mb-3 g-2 no-print">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Filtrar Productos:</label>
                    <select id="f_prod" class="form-select" multiple="multiple">
                        <?php while($p = $productos_list->fetch_assoc()): ?>
                            <option value="<?php echo $p['IdProductos']; ?>"><?php echo htmlspecialchars($p['NombreProductos']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Filtrar Categorías:</label>
                    <select id="f_cat" class="form-select" multiple="multiple">
                        <?php while($c = $categorias_list->fetch_assoc()): ?>
                            <option value="<?php echo $c['IdCategorias']; ?>"><?php echo htmlspecialchars($c['NombreCategorias']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Filtrar Estado:</label>
                    <select id="f_estado" class="form-select" multiple="multiple">
                        <option value="Normal">Normal (Stock > 10)</option>
                        <option value="Bajo">Bajo (Stock 1-10)</option>
                        <option value="Agotado">Agotado (Stock 0)</option>
                    </select>
                </div>
            </div>

            <div id="contenidoPDF" class="p-2 bg-white text-dark">
                <div class="mb-3 d-none d-print-block">
                    <h3 class="fw-bold">Kardex de Inventario - M BARBER</h3>
                    <p class="text-muted">Fecha: <?php echo date('d/m/Y'); ?></p>
                </div>

                <div class="table-responsive">
                    <table id="tablaKardex" class="table table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Salidas</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tablaKardexBody"><tr><td colspan="6">Cargando inventario...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// --- INICIALIZAR SELECT2 Y CARGAR DATOS ---
$(document).ready(function() {
    // Configuración base para los selectores
    const select2Config = {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: "Seleccionar...",
        closeOnSelect: false, // Permite seleccionar varios sin cerrar el dropdown
        allowClear: true
    };

    $('#f_prod').select2({ ...select2Config, placeholder: "Todos los productos" });
    $('#f_cat').select2({ ...select2Config, placeholder: "Todas las categorías" });
    $('#f_estado').select2({ ...select2Config, placeholder: "Todos los estados" });

    // Escuchar cambios en cualquier filtro
    $('#f_prod, #f_cat, #f_estado').on('change', function() {
        cargarKardex();
    });

    // Carga inicial
    cargarKardex();
});

function cargarKardex() {
    // Obtener valores seleccionados (Select2 devuelve un array)
    const prod = $('#f_prod').val().join(',');
    const cat = $('#f_cat').val().join(',');
    const est = $('#f_estado').val().join(',');
    
    const url = `controllers/kardex-dash.php?action=ajax_list&producto=${encodeURIComponent(prod)}&categoria=${encodeURIComponent(cat)}&estado=${encodeURIComponent(est)}`;
    
    fetch(url).then(r => r.json()).then(d => {
        document.getElementById('tablaKardexBody').innerHTML = d.tabla;
    });
}

// --- EXPORTAR A EXCEL ---
function exportTableToExcel(tableID, filename = '') {
    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.table_to_sheet(document.getElementById(tableID));
    XLSX.utils.book_append_sheet(wb, ws, "Kardex");
    filename = filename ? filename + '.xlsx' : 'Kardex_MBarber.xlsx';
    XLSX.writeFile(wb, filename);
}

// --- EXPORTAR A PDF ---
function exportTableToPDF() {
    const element = document.getElementById('contenidoPDF');
    const opt = {
        margin: [10, 10, 10, 10],
        filename: 'Kardex_MBarber.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>

<style>
    @media print { .no-print { display: none !important; } }
    /* Ajuste pequeño para que Select2 se vea bien en modo oscuro/claro */
    .select2-selection__choice { background-color: #ffc107 !important; color: #000 !important; border: none !important; }
</style>

<?php include 'layouts/footer.php'; ?>