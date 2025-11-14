<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "No autenticado"));
    exit;
}

$user_id = $_SESSION['user_id'];

if ($method == 'PUT') {
    // Actualizar información del usuario
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->nombre) || empty($data->email) || empty($data->whatsapp)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Campos requeridos: nombre, email, whatsapp"));
        exit;
    }
    
    // Validar formato de email
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Email inválido"));
        exit;
    }
    
    // Validar contraseña si se proporciona
    if (!empty($data->password) && strlen($data->password) < 6) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "La contraseña debe tener al menos 6 caracteres"));
        exit;
    }
    
    $result = $user->update(
        $user_id,
        $data->nombre,
        $data->email,
        $data->telefono ?? null,
        $data->whatsapp,
        $data->password ?? null
    );
    
    if ($result) {
        // Actualizar sesión
        $_SESSION['user_name'] = $result['nombre'];
        $_SESSION['user_email'] = $result['email'];
        
        echo json_encode(array(
            "success" => true,
            "message" => "Usuario actualizado exitosamente",
            "user" => $result
        ));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Error al actualizar. Verifica que el email no esté en uso."));
    }
} elseif ($method == 'GET') {
    // Obtener información del usuario actual
    $user_data = $user->getUserById($user_id);
    if ($user_data) {
        echo json_encode(array(
            "success" => true,
            "user" => $user_data
        ));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Usuario no encontrado"));
    }
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Método no permitido"));
}
?>

