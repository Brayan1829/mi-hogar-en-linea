<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['images']) && isset($_POST['property_id'])) {
        $property_id = $_POST['property_id'];
        $user_id = $_SESSION['user_id'];
        
        // Verificar que la propiedad pertenece al usuario
        $prop_data = $property->getPropertyById($property_id, $user_id);
        if (!$prop_data) {
            http_response_code(403);
            echo json_encode(array("success" => false, "message" => "No tienes permisos para esta propiedad"));
            exit;
        }
        
        $upload_dir = "../uploads/properties/" . $property_id . "/";
        
        // Crear directorio si no existe
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $uploaded_files = array();
        $errors = array();
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_size = $_FILES['images']['size'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            $file_type = $_FILES['images']['type'][$key];
            
            // Validar tipo de archivo
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_types)) {
                $errors[] = "Tipo de archivo no permitido: $file_name";
                continue;
            }
            
            // Validar tamaño (máximo 5MB)
            if ($file_size > 5000000) {
                $errors[] = "Archivo demasiado grande: $file_name";
                continue;
            }
            
            // Generar nombre único
            $new_file_name = uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $image_url = "uploads/properties/" . $property_id . "/" . $new_file_name;
                
                // Determinar si es la primera imagen (será la principal)
                $is_primary = (count($uploaded_files) == 0) ? 1 : 0;
                
                // Guardar en la base de datos
                if ($property->addImage($property_id, $image_url, $is_primary)) {
                    $uploaded_files[] = array(
                        "original_name" => $file_name,
                        "saved_name" => $new_file_name,
                        "url" => $image_url,
                        "is_primary" => $is_primary
                    );
                } else {
                    $errors[] = "Error al guardar información de la imagen: $file_name";
                    // Eliminar archivo subido si falla la BD
                    unlink($file_path);
                }
            } else {
                $errors[] = "Error al subir el archivo: $file_name";
            }
        }
        
        if (count($uploaded_files) > 0) {
            echo json_encode(array(
                "success" => true,
                "message" => count($uploaded_files) . " archivo(s) subido(s) exitosamente",
                "files" => $uploaded_files,
                "errors" => $errors
            ));
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "No se pudieron subir los archivos",
                "errors" => $errors
            ));
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