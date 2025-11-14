<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Property.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "No autenticado"));
    exit;
}

$database = new Database();
$db = $database->getConnection();
$property = new Property($db);

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

if ($method == 'GET') {
    // Si hay un ID en la URL, obtener una propiedad específica
    if (isset($_GET['id'])) {
        $property_id = $_GET['id'];
        $prop_data = $property->getPropertyById($property_id, $user_id);
        
        if ($prop_data) {
            // Obtener imágenes de la propiedad
            $images_stmt = $property->getPropertyImages($property_id);
            $images = array();
            while ($img_row = $images_stmt->fetch(PDO::FETCH_ASSOC)) {
                $images[] = $img_row;
            }
            
            $prop_data['imagenes'] = $images;
            
            echo json_encode(array(
                "success" => true,
                "property" => $prop_data
            ));
        } else {
            http_response_code(404);
            echo json_encode(array("success" => false, "message" => "Propiedad no encontrada"));
        }
    } else {
        // Obtener todas las propiedades del usuario
        $stmt = $property->getPropertiesByUser($user_id);
        $properties = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $row;
        }
        
        echo json_encode(array(
            "success" => true,
            "properties" => $properties
        ));
    }
    
} elseif ($method == 'POST') {
    // Crear nueva propiedad
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->titulo) && !empty($data->tipo) && !empty($data->precio) && !empty($data->ubicacion)) {
        $property->id_usuario = $user_id;
        $property->titulo = $data->titulo;
        $property->descripcion = $data->descripcion ?? '';
        $property->tipo = $data->tipo;
        $property->precio = $data->precio;
        $property->ubicacion = $data->ubicacion;
        $property->direccion = $data->direccion ?? '';
        $property->habitaciones = $data->habitaciones ?? 0;
        $property->banos = $data->banos ?? 0;
        $property->area = $data->area ?? 0;
        $property->amueblado = $data->amueblado ?? 0;
        $property->mascotas = $data->mascotas ?? 0;
        $property->estacionamiento = $data->estacionamiento ?? 0;
        
        if ($property->create()) {
            echo json_encode(array(
                "success" => true,
                "message" => "Propiedad creada exitosamente",
                "property_id" => $property->id
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al crear la propiedad"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos incompletos"));
    }
    
} elseif ($method == 'PUT') {
    // Actualizar propiedad
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->id) && !empty($data->titulo) && !empty($data->tipo) && !empty($data->precio) && !empty($data->ubicacion)) {
        $property->id = $data->id;
        $property->id_usuario = $user_id;
        $property->titulo = $data->titulo;
        $property->descripcion = $data->descripcion ?? '';
        $property->tipo = $data->tipo;
        $property->precio = $data->precio;
        $property->ubicacion = $data->ubicacion;
        $property->direccion = $data->direccion ?? '';
        $property->habitaciones = $data->habitaciones ?? 0;
        $property->banos = $data->banos ?? 0;
        $property->area = $data->area ?? 0;
        $property->amueblado = $data->amueblado ?? 0;
        $property->mascotas = $data->mascotas ?? 0;
        $property->estacionamiento = $data->estacionamiento ?? 0;
        $property->estado = $data->estado ?? 'disponible';
        
        if ($property->update()) {
            echo json_encode(array(
                "success" => true,
                "message" => "Propiedad actualizada exitosamente"
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al actualizar la propiedad"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos incompletos"));
    }
    
} elseif ($method == 'DELETE') {
    // Eliminar propiedad
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->id)) {
        $property->id = $data->id;
        $property->id_usuario = $user_id;
        
        if ($property->delete()) {
            echo json_encode(array(
                "success" => true,
                "message" => "Propiedad eliminada exitosamente"
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al eliminar la propiedad"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "ID de propiedad no proporcionado"));
    }
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Método no permitido"));
}
?>