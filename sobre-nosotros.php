<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - MI HOGAR EN LINEA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .about-hero {
            background: linear-gradient(135deg, var(--azul-primario) 0%, var(--azul-secundario) 100%);
            color: white;
            padding: 100px 0 80px;
            text-align: center;
        }
        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .about-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .about-content {
            padding: 80px 0;
        }
        .about-section {
            margin-bottom: 60px;
        }
        .about-section h2 {
            color: var(--azul-primario);
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
        }
        .about-section p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--gris-oscuro);
            margin-bottom: 20px;
            text-align: justify;
        }
        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }
        .mission-card, .vision-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--sombra);
            text-align: center;
        }
        .mission-card i, .vision-card i {
            font-size: 3rem;
            color: var(--azul-primario);
            margin-bottom: 20px;
        }
        .mission-card h3, .vision-card h3 {
            color: var(--azul-primario);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        .value-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--sombra);
            text-align: center;
            transition: var(--transicion);
        }
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        .value-card i {
            font-size: 2.5rem;
            color: var(--verde);
            margin-bottom: 20px;
        }
        .value-card h4 {
            color: var(--azul-primario);
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        .team-section {
            background: var(--beige);
            padding: 80px 0;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--sombra);
            text-align: center;
        }
        .stat-card .number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--azul-primario);
            margin-bottom: 10px;
        }
        .stat-card .label {
            color: var(--gris);
            font-size: 1.1rem;
        }
    </style>
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
    <section class="about-hero">
        <div class="container">
            <h1>Sobre Nosotros</h1>
            <p>Conectando personas con su hogar ideal desde 2023</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <div class="about-section">
                <h2>Nuestra Historia</h2>
                <p>
                    MI HOGAR EN LINEA nació con la visión de revolucionar la forma en que las personas encuentran y publican propiedades en arrendamiento. Fundada en 2023, nuestra plataforma se ha convertido en el punto de encuentro entre propietarios y arrendatarios, facilitando transacciones seguras y eficientes.
                </p>
                <p>
                    Comenzamos con una simple idea: hacer que el proceso de encontrar un hogar sea más accesible, transparente y confiable. Hoy en día, miles de usuarios confían en nuestra plataforma para encontrar su próximo hogar o para publicar sus propiedades.
                </p>
            </div>

            <div class="mission-vision">
                <div class="mission-card">
                    <i class="fas fa-bullseye"></i>
                    <h3>Nuestra Misión</h3>
                    <p>
                        Facilitar el encuentro entre propietarios y arrendatarios mediante una plataforma digital innovadora, segura y fácil de usar, que transforme la experiencia de buscar y publicar propiedades.
                    </p>
                </div>
                <div class="vision-card">
                    <i class="fas fa-eye"></i>
                    <h3>Nuestra Visión</h3>
                    <p>
                        Ser la plataforma líder en arrendamiento de propiedades, reconocida por nuestra innovación, confiabilidad y compromiso con la satisfacción de nuestros usuarios.
                    </p>
                </div>
            </div>

            <div class="about-section">
                <h2>Nuestros Valores</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Confianza</h4>
                        <p>Garantizamos transacciones seguras y transparentes para todos nuestros usuarios.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-users"></i>
                        <h4>Compromiso</h4>
                        <p>Estamos comprometidos con la satisfacción y el éxito de cada usuario de nuestra plataforma.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-lightbulb"></i>
                        <h4>Innovación</h4>
                        <p>Continuamente mejoramos nuestra plataforma con las últimas tecnologías y mejores prácticas.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-heart"></i>
                        <h4>Servicio</h4>
                        <p>Brindamos un servicio excepcional y atención personalizada a cada usuario.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="team-section">
        <div class="container">
            <h2 style="text-align: center; color: var(--azul-primario); font-size: 2.5rem; margin-bottom: 30px;">Números que Hablan</h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="number">1000+</div>
                    <div class="label">Propiedades Publicadas</div>
                </div>
                <div class="stat-card">
                    <div class="number">500+</div>
                    <div class="label">Usuarios Activos</div>
                </div>
                <div class="stat-card">
                    <div class="number">98%</div>
                    <div class="label">Satisfacción del Cliente</div>
                </div>
                <div class="stat-card">
                    <div class="number">24/7</div>
                    <div class="label">Soporte Disponible</div>
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
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Inicio</a></li>
                        <li><a href="property.php"><i class="fas fa-chevron-right"></i> Propiedades</a></li>
                        <li><a href="admin/dashboard.php"><i class="fas fa-chevron-right"></i> Publicar Propiedad</a></li>
                        <li><a href="sobre-nosotros.php"><i class="fas fa-chevron-right"></i> Sobre Nosotros</a></li>
                        <li><a href="contactanos.php"><i class="fas fa-chevron-right"></i> Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contacto</h3>
                    <ul>
                        <li><a href="mailto:info@mihogarenlinea.com"><i class="fas fa-envelope"></i> info@mihogarenlinea.com</a></li>
                        <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +1 234 567 890</a></li>
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
</body>
</html>

