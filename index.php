<?php
session_start();
require_once 'config/database.php';

// Obtener las 3 primeras propiedades destacadas
$database = new Database();
$pdo = $database->getConnection();

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.titulo,
            p.descripcion,
            p.precio,
            p.direccion,
            p.ubicacion,
            p.habitaciones,
            p.banos,
            p.area,
            p.tipo,
            p.estado,
            p.estacionamiento,
            p.mascotas,
            p.amueblado,
            ip.imagen_url as imagen_principal
        FROM propiedades p
        LEFT JOIN imagenes_propiedad ip ON p.id = ip.id_propiedad AND ip.es_principal = 1
        WHERE p.estado = 'disponible'
        ORDER BY p.fecha_publicacion ASC
        LIMIT 3
    ");
    $stmt->execute();
    $propiedades_destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $propiedades_destacadas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MI HOGAR EN LINEA - Encuentra tu hogar ideal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <h1>MI <span>HOGAR</span> EN LINEA</h1>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="property.php">Propiedades</a></li>
                    <li><a href="admin/dashboard.php">Publicar</a></li>
                    <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
                    <li><a href="contactanos.php">Contacto</a></li>
                </ul>
            </nav>
            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="logout.php" class="btn btn-primario">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-primario">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2 class="fade-in">Encuentra tu hogar ideal</h2>
            <p class="fade-in delay-1">Miles de propiedades disponibles para arrendar. Filtra por ubicación, precio y características para encontrar la vivienda perfecta para ti.</p>
            
            <!-- Buscador -->
            <div class="buscador fade-in delay-2">
                <form class="buscador-form" action="property.php" method="GET">
                    <div class="form-group">
                        <label for="ciudad"><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                        <input type="text" id="ciudad" name="ciudad" class="form-control" placeholder="¿Dónde quieres vivir?" value="<?php echo isset($_GET['ciudad']) ? htmlspecialchars($_GET['ciudad']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="tipo"><i class="fas fa-home"></i> Tipo de propiedad</label>
                        <select id="tipo" name="tipo" class="form-control">
                            <option value="">Todos los tipos</option>
                            <option value="apartamento" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'apartamento') ? 'selected' : ''; ?>>Apartamento</option>
                            <option value="casa" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'casa') ? 'selected' : ''; ?>>Casa</option>
                            <option value="estudio" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'estudio') ? 'selected' : ''; ?>>Estudio</option>
                            <option value="duplex" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'duplex') ? 'selected' : ''; ?>>Dúplex</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="precio-min"><i class="fas fa-dollar-sign"></i> Precio mínimo</label>
                        <input type="number" id="precio-min" name="precio_min" class="form-control" placeholder="Mínimo" value="<?php echo isset($_GET['precio_min']) ? htmlspecialchars($_GET['precio_min']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="precio-max"><i class="fas fa-dollar-sign"></i> Precio máximo</label>
                        <input type="number" id="precio-max" name="precio_max" class="form-control" placeholder="Máximo" value="<?php echo isset($_GET['precio_max']) ? htmlspecialchars($_GET['precio_max']) : ''; ?>">
                    </div>
                    <button type="submit" class="btn-buscar"><i class="fas fa-search"></i> Buscar Propiedades</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Propiedades Destacadas -->
    <section class="propiedades">
        <div class="container">
            <div class="section-title">
                <h2>Propiedades Destacadas</h2>
                <p>Descubre las viviendas más populares de nuestra plataforma</p>
            </div>
            
            <div class="propiedades-grid">
                <?php if (!empty($propiedades_destacadas)): ?>
                    <?php 
                    $delay_classes = ['fade-in', 'fade-in delay-1', 'fade-in delay-2'];
                    $delay_index = 0;
                    foreach ($propiedades_destacadas as $propiedad): 
                        $imagen_url = !empty($propiedad['imagen_principal']) 
                            ? $propiedad['imagen_principal'] 
                            : 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80';
                    ?>
                        <div class="propiedad-card <?php echo $delay_classes[$delay_index]; ?>">
                            <div class="propiedad-img" style="background-image: url('<?php echo htmlspecialchars($imagen_url); ?>');">
                                <span class="propiedad-destacada">Destacada</span>
                            </div>
                            <div class="propiedad-info">
                                <div class="propiedad-precio">$<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>/mes</div>
                                <h3 class="propiedad-titulo"><?php echo htmlspecialchars($propiedad['titulo']); ?></h3>
                                <div class="propiedad-ubicacion">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($propiedad['ubicacion']); ?>
                                </div>
                                <div class="propiedad-caracteristicas">
                                    <div class="caracteristica">
                                        <i class="fas fa-bed"></i>
                                        <span><?php echo $propiedad['habitaciones'] ?? 0; ?> Habitaciones</span>
                                    </div>
                                    <div class="caracteristica">
                                        <i class="fas fa-bath"></i>
                                        <span><?php echo $propiedad['banos'] ?? 0; ?> Baños</span>
                                    </div>
                                    <div class="caracteristica">
                                        <i class="fas fa-vector-square"></i>
                                        <span><?php echo $propiedad['area'] ?? 0; ?> m²</span>
                                    </div>
                                </div>
                                <a href="property-details.php?id=<?php echo $propiedad['id']; ?>" class="btn btn-primario" style="width: 100%; margin-top: 20px;">Ver Detalles</a>
                            </div>
                        </div>
                    <?php 
                        $delay_index++;
                    endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--gris);">
                        <i class="fas fa-home" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                        <p>No hay propiedades destacadas disponibles en este momento.</p>
                        <p style="margin-top: 10px;">Sé el primero en publicar una propiedad.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 50px;">
                <a href="property.php" class="btn btn-secundario">Ver Todas las Propiedades</a>
            </div>
        </div>
    </section>

    <!-- Cómo funciona -->
    <section class="como-funciona">
        <div class="container">
            <div class="section-title">
                <h2>¿Cómo funciona?</h2>
                <p>Encuentra o publica una propiedad en simples pasos</p>
            </div>
            
            <div class="pasos">
                <div class="paso fade-in">
                    <div class="paso-numero">1</div>
                    <h3>Regístrate</h3>
                    <p>Crea una cuenta gratuita para acceder a todas las funcionalidades de la plataforma.</p>
                </div>
                <div class="paso fade-in delay-1">
                    <div class="paso-numero">2</div>
                    <h3>Busca o Publica</h3>
                    <p>Encuentra la propiedad ideal usando nuestros filtros o publica tu vivienda para arrendar.</p>
                </div>
                <div class="paso fade-in delay-2">
                    <div class="paso-numero">3</div>
                    <h3>Contacta</h3>
                    <p>Comunícate directamente con propietarios o arrendatarios a través de nuestra plataforma.</p>
                </div>
                <div class="paso fade-in delay-3">
                    <div class="paso-numero">4</div>
                    <h3>Finaliza el trato</h3>
                    <p>Acuerda los términos y firma el contrato de arrendamiento de manera segura.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>MI HOGAR EN LINEA</h3>
                    <p>La plataforma líder para encontrar y publicar propiedades en arrendamiento. Conectamos a propietarios y arrendatarios de forma segura y eficiente.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Inicio</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Propiedades</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Publicar Propiedad</a></li>
                        <li><a href="sobre-nosotros.php"><i class="fas fa-chevron-right"></i> Sobre Nosotros</a></li>
                        <li><a href="contactanos.php"><i class="fas fa-chevron-right"></i> Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contacto</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-envelope"></i> info@mihogarenlinea.com</a></li>
                        <li><a href="#"><i class="fas fa-phone"></i> +1 234 567 890</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Calle Principal #123, Ciudad</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Newsletter</h3>
                    <p>Suscríbete para recibir las últimas propiedades y ofertas.</p>
                    <form>
                        <input type="email" class="form-control" placeholder="Tu correo electrónico" style="margin-bottom: 10px;">
                        <button type="submit" class="btn btn-secundario" style="width: 100%;">Suscribirse</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 MI HOGAR EN LINEA. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Efecto de animación al hacer scroll
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            const fadeInOnScroll = function() {
                fadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.classList.add('active');
                    }
                });
            };
            
            // Ejecutar al cargar y al hacer scroll
            fadeInOnScroll();
            window.addEventListener('scroll', fadeInOnScroll);
        });
    </script>
</body>
</html>