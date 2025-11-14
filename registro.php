<?php
// Configuración de sesión primero
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Iniciar sesión
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit();
}

// Procesar el formulario de registro
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefono = trim($_POST['telefono'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un email válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            include_once 'config/database.php';
            include_once 'models/User.php';
            
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);
            
            if ($user->register($nombre, $email, $password, $telefono, $whatsapp)) {
                $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                // Limpiar el formulario
                $nombre = $email = $telefono = $whatsapp = '';
            } else {
                $error = 'El email ya está registrado. Por favor, usa otro email.';
            }
        } catch (Exception $e) {
            $error = 'Error del sistema. Por favor, intenta más tarde.';
            error_log("Error en registro: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MI HOGAR EN LINEA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables de colores y estilos (igual que login) */
        :root {
            --azul-primario: #1a3a5f;
            --azul-secundario: #2c5282;
            --verde: #38a169;
            --beige: #f7fafc;
            --blanco: #ffffff;
            --gris: #718096;
            --gris-oscuro: #2d3748;
            --sombra: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transicion: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--beige);
            color: var(--gris-oscuro);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicion);
            text-align: center;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primario {
            background-color: var(--azul-primario);
            color: var(--blanco);
            box-shadow: 0 4px 12px rgba(26, 58, 95, 0.2);
        }
        
        .btn-primario:hover {
            background-color: var(--azul-secundario);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(26, 58, 95, 0.3);
        }
        
        .btn-secundario {
            background-color: var(--verde);
            color: var(--blanco);
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.2);
        }
        
        .btn-secundario:hover {
            background-color: #2f855a;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(56, 161, 105, 0.3);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--azul-primario);
            border: 2px solid var(--azul-primario);
        }
        
        .btn-outline:hover {
            background-color: var(--azul-primario);
            color: var(--blanco);
        }
        
        /* Header */
        header {
            background-color: var(--blanco);
            box-shadow: var(--sombra);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transicion);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo h1 {
            color: var(--azul-primario);
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .logo span {
            color: var(--verde);
        }
        
        /* Contenedor de registro */
        .register-container {
            display: flex;
            min-height: calc(100vh - 80px);
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .register-box {
            background-color: var(--blanco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            transition: var(--transicion);
        }
        
        .register-box:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: var(--azul-primario);
            font-size: 1.8rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .register-header h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -8px;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--verde);
        }
        
        .register-header p {
            color: var(--gris);
            font-size: 1rem;
        }
        
        /* Mensajes de alerta */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background-color: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gris-oscuro);
        }
        
        .form-group label .required {
            color: #e53e3e;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gris);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            transition: var(--transicion);
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--azul-primario);
            box-shadow: 0 0 0 3px rgba(26, 58, 95, 0.1);
            outline: none;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .password-strength.weak {
            color: #e53e3e;
        }
        
        .password-strength.medium {
            color: #d69e2e;
        }
        
        .password-strength.strong {
            color: var(--verde);
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: var(--azul-primario);
            font-weight: 600;
            transition: var(--transicion);
        }
        
        .login-link a:hover {
            color: var(--azul-secundario);
        }
        
        .terms {
            margin-top: 20px;
            padding: 15px;
            background-color: #f7fafc;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .terms a {
            color: var(--azul-primario);
            font-weight: 500;
        }
        
        .terms a:hover {
            color: var(--azul-secundario);
        }
        
        /* Footer */
        footer {
            background-color: var(--azul-primario);
            color: var(--blanco);
            padding: 40px 0 20px;
            margin-top: auto;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .footer-col h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background-color: var(--verde);
        }
        
        .footer-col ul {
            list-style: none;
        }
        
        .footer-col ul li {
            margin-bottom: 10px;
        }
        
        .footer-col a {
            transition: var(--transicion);
        }
        
        .footer-col a:hover {
            color: var(--verde);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .register-box {
                padding: 30px 25px;
            }
        }
        
        @media (max-width: 576px) {
            .register-box {
                padding: 25px 20px;
            }
            
            .register-header h2 {
                font-size: 1.5rem;
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
                <a href="index.php" class="btn btn-outline">Volver al Inicio</a>
            </nav>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h2>Crear Cuenta</h2>
                <p>Regístrate para comenzar a publicar propiedades</p>
            </div>
            
            <!-- Mostrar mensajes de éxito -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <!-- Mostrar mensajes de error -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form id="registerForm" method="POST" action="registro.php">
                <div class="form-group">
                    <label for="nombre">Nombre Completo <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nombre" name="nombre" class="form-control" 
                               placeholder="Tu nombre completo" 
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="tu@email.com" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="telefono" name="telefono" class="form-control" 
                               placeholder="+1 234 567 890" 
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="whatsapp">WhatsApp <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fab fa-whatsapp"></i>
                        <input type="tel" id="whatsapp" name="whatsapp" class="form-control" 
                               placeholder="+1 234 567 890" 
                               value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>"
                               required>
                    </div>
                    <small style="color: var(--gris); font-size: 0.85rem; margin-top: 5px; display: block;">
                        Este número será visible para contactarte sobre tus propiedades
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Mínimo 6 caracteres" 
                               minlength="6" required>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Repite tu contraseña" 
                               minlength="6" required>
                    </div>
                    <div class="password-match" id="passwordMatch"></div>
                </div>
                
                <div class="terms">
                    <p>Al registrarte, aceptas nuestros <a href="terminos.php">Términos de Servicio</a> y <a href="privacidad.php">Política de Privacidad</a>.</p>
                </div>
                
                <button type="submit" class="btn btn-primario btn-register">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>
                
                <div class="login-link">
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión aquí</a></p>
                </div>
            </form>
        </div>
    </main>

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
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="propiedades.php">Propiedades</a></li>
                        <li><a href="publicar.php">Publicar Propiedad</a></li>
                        <li><a href="about.php">Sobre Nosotros</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> MI HOGAR EN LINEA. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            const registerBtn = document.querySelector('.btn-register');
            
            // Validar fortaleza de contraseña
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = '';
                let strengthClass = '';
                
                if (password.length === 0) {
                    strength = '';
                } else if (password.length < 6) {
                    strength = 'Débil - Mínimo 6 caracteres';
                    strengthClass = 'weak';
                } else if (password.length < 8) {
                    strength = 'Media';
                    strengthClass = 'medium';
                } else {
                    // Verificar si tiene números y letras
                    const hasNumbers = /\d/.test(password);
                    const hasLetters = /[a-zA-Z]/.test(password);
                    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                    
                    if (hasNumbers && hasLetters && hasSpecial) {
                        strength = 'Fuerte';
                        strengthClass = 'strong';
                    } else if (hasNumbers && hasLetters) {
                        strength = 'Media';
                        strengthClass = 'medium';
                    } else {
                        strength = 'Débil - Usa números y letras';
                        strengthClass = 'weak';
                    }
                }
                
                passwordStrength.textContent = strength;
                passwordStrength.className = 'password-strength ' + strengthClass;
            });
            
            // Validar que las contraseñas coincidan
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword.length === 0) {
                    passwordMatch.textContent = '';
                } else if (password === confirmPassword) {
                    passwordMatch.textContent = '✓ Las contraseñas coinciden';
                    passwordMatch.style.color = '#38a169';
                } else {
                    passwordMatch.textContent = '✗ Las contraseñas no coinciden';
                    passwordMatch.style.color = '#e53e3e';
                }
            });
            
            // Validación del formulario
            registerForm.addEventListener('submit', function(e) {
                const nombre = document.getElementById('nombre').value;
                const email = document.getElementById('email').value;
                const whatsapp = document.getElementById('whatsapp').value;
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                // Validaciones básicas
                if (!nombre || !email || !whatsapp || !password || !confirmPassword) {
                    e.preventDefault();
                    alert('Por favor, completa todos los campos obligatorios (incluyendo WhatsApp).');
                    return;
                }
                
                // Validar formato de WhatsApp (debe tener al menos 10 dígitos)
                const whatsappClean = whatsapp.replace(/[^0-9+]/g, '');
                if (whatsappClean.length < 10) {
                    e.preventDefault();
                    alert('Por favor, ingresa un número de WhatsApp válido.');
                    return;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres.');
                    return;
                }
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden.');
                    return;
                }
                
                // Mostrar estado de carga
                const originalText = registerBtn.innerHTML;
                registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando cuenta...';
                registerBtn.disabled = true;
            });
        });
    </script>
</body>
</html>