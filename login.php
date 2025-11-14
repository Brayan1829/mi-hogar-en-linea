<?php
// CONFIGURACIÓN DE SESIÓN PRIMERO - ESTO DEBE IR ANTES DE session_start()
session_set_cookie_params([
    'lifetime' => 86400, // 24 horas
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// LUEGO INICIAR SESIÓN - ESTO DEBE IR DESPUÉS
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit();
}

// Procesar el formulario de login si se envió
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            include_once 'config/database.php';
            include_once 'models/User.php';
            
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);
            
            if ($user->login($email, $password)) {
                // Login exitoso
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->nombre;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_avatar'] = $user->avatar;
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                
                // Regenerar ID de sesión para prevenir fixation attacks
                session_regenerate_id(true);
                
                // Redirigir al dashboard
                header('Location: admin/dashboard.php');
                exit();
            } else {
                $error = 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.';
            }
        } catch (Exception $e) {
            $error = 'Error del sistema. Por favor, intenta más tarde.';
            error_log("Error en login: " . $e->getMessage());
        }
    }
}

// Mostrar mensajes de éxito (por ejemplo, después del registro)
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'registered':
            $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            break;
        case 'logout':
            $success = 'Has cerrado sesión correctamente.';
            break;
    }
}

// Mostrar mensajes de error
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.';
            break;
        case 'unauthorized':
            $error = 'Debes iniciar sesión para acceder a esa página.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MI HOGAR EN LINEA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables de colores y estilos */
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
        
        /* Contenedor de login */
        .login-container {
            display: flex;
            min-height: calc(100vh - 80px);
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .login-box {
            background-color: var(--blanco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            transition: var(--transicion);
        }
        
        .login-box:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: var(--azul-primario);
            font-size: 1.8rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .login-header h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -8px;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--verde);
        }
        
        .login-header p {
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
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: var(--azul-primario);
            font-weight: 500;
            transition: var(--transicion);
        }
        
        .forgot-password:hover {
            color: var(--azul-secundario);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #e2e8f0;
        }
        
        .divider span {
            padding: 0 15px;
            color: var(--gris);
            font-size: 0.9rem;
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .social-btn {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 500;
            transition: var(--transicion);
            border: 1px solid #e2e8f0;
            background-color: var(--blanco);
            cursor: pointer;
        }
        
        .social-btn.google {
            color: #DB4437;
        }
        
        .social-btn.facebook {
            color: #4267B2;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .signup-link a {
            color: var(--azul-primario);
            font-weight: 600;
            transition: var(--transicion);
        }
        
        .signup-link a:hover {
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
            .login-box {
                padding: 30px 25px;
            }
            
            .social-login {
                flex-direction: column;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .login-box {
                padding: 25px 20px;
            }
            
            .login-header h2 {
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
    <main class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Accede a tu cuenta para gestionar tus propiedades</p>
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
            
            <form id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="tu@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Tu contraseña" required>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordarme</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn btn-primario btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
                
                <div class="divider">
                    <span>O inicia sesión con</span>
                </div>
                
                <div class="social-login">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button type="button" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </button>
                </div>
                
                <div class="signup-link">
                    <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
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
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.querySelector('.btn-login');
            
            loginForm.addEventListener('submit', function(e) {
                // Validación básica del lado del cliente
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Por favor, completa todos los campos requeridos.');
                    return;
                }
                
                // Mostrar estado de carga
                const originalText = loginBtn.innerHTML;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
                loginBtn.disabled = true;
                
                // El formulario se enviará normalmente ya que estamos usando method="POST"
                // Si quieres usar AJAX, descomenta el código siguiente y comenta la línea anterior
                /*
                e.preventDefault();
                
                try {
                    const formData = new FormData(this);
                    
                    fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'admin/dashboard.php';
                        } else {
                            alert('Error: ' + data.message);
                            loginBtn.innerHTML = originalText;
                            loginBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al conectar con el servidor');
                        loginBtn.innerHTML = originalText;
                        loginBtn.disabled = false;
                    });
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                    loginBtn.innerHTML = originalText;
                    loginBtn.disabled = false;
                }
                */
            });
            
            // Funcionalidad para los botones de redes sociales
            document.querySelectorAll('.social-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const provider = this.classList.contains('google') ? 'Google' : 'Facebook';
                    alert(`Iniciar sesión con ${provider} - Esta funcionalidad se implementaría en un entorno real`);
                });
            });
            
            // Funcionalidad para "Olvidé mi contraseña"
            document.querySelector('.forgot-password').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Funcionalidad de recuperación de contraseña - Se enviaría un correo para restablecer la contraseña');
            });
        });
    </script>
</body>
</html>