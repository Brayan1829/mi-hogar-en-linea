<?php
// Configuración de sesión más robusta
session_set_cookie_params([
    'lifetime' => 86400, // 24 horas
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Para solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = file_get_contents("php://input");
    
    if (empty($input)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos vacíos"));
        exit();
    }
    
    $data = json_decode($input);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "JSON inválido"));
        exit();
    }
    
    if (!empty($data->email) && !empty($data->password)) {
        if ($user->login($data->email, $data->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->nombre;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            // Regenerar ID de sesión para prevenir fixation attacks
            session_regenerate_id(true);
            
            echo json_encode(array(
                "success" => true,
                "message" => "Inicio de sesión exitoso",
                "user" => array(
                    "id" => $user->id,
                    "nombre" => $user->nombre,
                    "email" => $user->email,
                    "telefono" => $user->telefono,
                    "avatar" => $user->avatar
                )
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "Credenciales incorrectas"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos incompletos"));
    }
} elseif ($method == 'GET') {
    // Verificar sesión con timeout (30 minutos)
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            // Sesión expirada
            session_unset();
            session_destroy();
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "Sesión expirada"));
            exit();
        }
        
        // Actualizar última actividad
        $_SESSION['last_activity'] = time();
        
        $user_data = $user->getUserById($_SESSION['user_id']);
        if ($user_data) {
            echo json_encode(array(
                "success" => true,
                "user" => $user_data
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "Usuario no encontrado"));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "No autenticado"));
    }
} elseif ($method == 'DELETE') {
    // Cerrar sesión
    session_unset();
    session_destroy();
    echo json_encode(array("success" => true, "message" => "Sesión cerrada"));
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Método no permitido"));
}
?>