<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "No autenticado"));
    exit;
}

include_once '../config/database.php';
include_once '../models/Property.php';

$database = new Database();
$db = $database->getConnection();
$property = new Property($db);

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

if ($method == 'DELETE') {
    // Eliminar imagen
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->image_id) && !empty($data->property_id)) {
        // Verificar que la propiedad pertenece al usuario
        $prop_data = $property->getPropertyById($data->property_id, $user_id);
        if (!$prop_data) {
            http_response_code(403);
            echo json_encode(array("success" => false, "message" => "No tienes permisos para esta propiedad"));
            exit;
        }
        
        // Obtener información de la imagen antes de eliminarla
        $images_stmt = $property->getPropertyImages($data->property_id);
        $image_to_delete = null;
        while ($img = $images_stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($img['id'] == $data->image_id) {
                $image_to_delete = $img;
                break;
            }
        }
        
        if ($image_to_delete && $property->deleteImage($data->image_id, $data->property_id)) {
            // Eliminar archivo físico
            $file_path = "../" . $image_to_delete['imagen_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            echo json_encode(array(
                "success" => true,
                "message" => "Imagen eliminada exitosamente"
            ));
        } else {
            http_response_code(404);
            echo json_encode(array("success" => false, "message" => "Imagen no encontrada"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos incompletos"));
    }
    
} elseif ($method == 'PUT') {
    // Establecer imagen como principal
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->image_id) && !empty($data->property_id)) {
        // Verificar que la propiedad pertenece al usuario
        $prop_data = $property->getPropertyById($data->property_id, $user_id);
        if (!$prop_data) {
            http_response_code(403);
            echo json_encode(array("success" => false, "message" => "No tienes permisos para esta propiedad"));
            exit;
        }
        
        if ($property->setPrimaryImage($data->image_id, $data->property_id)) {
            echo json_encode(array(
                "success" => true,
                "message" => "Imagen principal actualizada exitosamente"
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al actualizar la imagen principal"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos incompletos"));
    }
    
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Método no permitido"));
}
?>

