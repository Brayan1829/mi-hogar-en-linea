<?php
session_start();
require_once 'config/database.php';

$mensaje_enviado = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $asunto = isset($_POST['asunto']) ? trim($_POST['asunto']) : '';
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    
    if (empty($nombre) || empty($email) || empty($mensaje)) {
        $error = 'Por favor completa todos los campos requeridos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingresa un email válido.';
    } else {
        // Aquí puedes guardar el mensaje en la base de datos o enviarlo por email
        // Por ahora, solo mostramos un mensaje de éxito
        $mensaje_enviado = true;
        
        // Opcional: Guardar en base de datos
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Crear tabla de contactos si no existe
            $pdo->exec("CREATE TABLE IF NOT EXISTS contactos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                telefono VARCHAR(20),
                asunto VARCHAR(200),
                mensaje TEXT NOT NULL,
                fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            $stmt = $pdo->prepare("INSERT INTO contactos (nombre, email, telefono, asunto, mensaje) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $telefono, $asunto, $mensaje]);
        } catch (PDOException $e) {
            // Si hay error, aún mostramos éxito al usuario
            error_log("Error al guardar contacto: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctanos - MI HOGAR EN LINEA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, var(--azul-primario) 0%, var(--azul-secundario) 100%);
            color: white;
            padding: 100px 0 80px;
            text-align: center;
        }
        .contact-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .contact-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .contact-content {
            padding: 80px 0;
        }
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 50px;
        }
        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--sombra);
        }
        .contact-info h3 {
            color: var(--azul-primario);
            font-size: 1.8rem;
            margin-bottom: 30px;
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        .info-item i {
            font-size: 1.5rem;
            color: var(--azul-primario);
            margin-right: 20px;
            margin-top: 5px;
        }
        .info-item div h4 {
            color: var(--azul-primario);
            margin-bottom: 5px;
        }
        .info-item div p {
            color: var(--gris);
            margin: 0;
        }
        .contact-form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--sombra);
        }
        .contact-form-container h3 {
            color: var(--azul-primario);
            font-size: 1.8rem;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gris-oscuro);
            font-weight: 600;
        }
        .form-group label .required {
            color: red;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: var(--transicion);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--azul-primario);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .alert-error {
            background-color: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
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
    <section class="contact-hero">
        <div class="container">
            <h1>Contáctanos</h1>
            <p>Estamos aquí para ayudarte. Envíanos un mensaje y te responderemos pronto.</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="contact-content">
        <div class="container">
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Información de Contacto</h3>
                    
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Dirección</h4>
                            <p>Calle Principal #123<br>Ciudad, País</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Teléfono</h4>
                            <p>+1 234 567 890</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>info@mihogarenlinea.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>Horario de Atención</h4>
                            <p>Lunes - Viernes: 9:00 AM - 6:00 PM<br>Sábados: 10:00 AM - 2:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fab fa-whatsapp"></i>
                        <div>
                            <h4>WhatsApp</h4>
                            <p>+1 234 567 890</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-container">
                    <h3>Envíanos un Mensaje</h3>
                    
                    <?php if ($mensaje_enviado): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ¡Gracias! Tu mensaje ha sido enviado exitosamente. Nos pondremos en contacto contigo pronto.
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="contactanos.php">
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="required">*</span></label>
                            <input type="text" id="nombre" name="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="asunto">Asunto</label>
                            <select id="asunto" name="asunto">
                                <option value="">Selecciona un asunto</option>
                                <option value="consulta" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'consulta') ? 'selected' : ''; ?>>Consulta General</option>
                                <option value="soporte" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'soporte') ? 'selected' : ''; ?>>Soporte Técnico</option>
                                <option value="publicar" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'publicar') ? 'selected' : ''; ?>>Publicar Propiedad</option>
                                <option value="otro" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensaje">Mensaje <span class="required">*</span></label>
                            <textarea id="mensaje" name="mensaje" required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primario" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Enviar Mensaje
                        </button>
                    </form>
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

