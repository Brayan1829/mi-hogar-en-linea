        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>MI HOGAR EN LINEA</h3>
                    <p>La plataforma líder para encontrar y publicar propiedades en arrendamiento.</p>
                </div>
                <div class="footer-col">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="../index.php">Inicio</a></li>
                        <li><a href="../property.php">Propiedades</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="../sobre-nosotros.php">Sobre Nosotros</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contacto</h3>
                    <ul>
                        <li>Email: info@mihogarenlinea.com</li>
                        <li>Teléfono: +1 234 567 890</li>
                        <li>Dirección: Calle Principal #123, Ciudad</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 MI HOGAR EN LINEA. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script src="js/common.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
        <script src="js/dashboard.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) == 'publicar-propiedad.php'): ?>
        <script src="js/publicar-propiedad.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) == 'mis-propiedades.php'): ?>
        <script src="js/mis-propiedades.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) == 'configuracion.php'): ?>
        <script src="js/configuracion.js"></script>
    <?php endif; ?>
</body>
</html>

