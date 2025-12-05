</div> <footer class="footer-custom">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="../assets/img/logo.jpg" alt="Logo Footer" style="height: 60px; margin-bottom: 1rem;">
                    <p>La barbería líder en estilos urbanos y cuidado masculino. Calidad, profesionalismo y un ambiente único.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Navegación</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="inicio.php">Inicio</a></li>
                        <li><a href="productos.php">Productos</a></li>
                        <li><a href="servicios.php">Servicios</a></li>
                        <li><a href="reservas.php">Reservas</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contacto</h5>
                    <ul class="list-unstyled ">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-warning"></i> Av. Principal 123, Lima</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-warning"></i> +51 987 654 321</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-warning"></i> contacto@estilourbano.com</li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Síguenos</h5>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> M BARBER. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/cart_ajax.js"></script>
    <script src="../assets/js/profile_actions.js"></script>
    <script src="../assets/js/pagination.js"></script>

    <?php
    // Script para mostrar alertas de sesión (ej: "Producto añadido")
    if (isset($_SESSION['alert'])) {
        $alertType = $_SESSION['alert']['type'];
        $alertMessage = $_SESSION['alert']['message'];
        
        echo "
        <script>
            Swal.fire({
                icon: '{$alertType}',
                title: '{$alertMessage}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        </script>
        ";
        
        // Limpiar la alerta para que no se muestre de nuevo
        unset($_SESSION['alert']);
    }
    ?>
</body>
</html>