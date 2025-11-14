<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($method == 'POST') {
    // Crear nuevo mensaje
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->id_propiedad) && !empty($data->nombre) && !empty($data->email) && !empty($data->mensaje)) {
        try {
            $query = "INSERT INTO mensajes 
                      SET id_propiedad=:id_propiedad, id_usuario=:id_usuario, nombre=:nombre, 
                      email=:email, telefono=:telefono, mensaje=:mensaje";
            
            $stmt = $pdo->prepare($query);
            
            $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            $stmt->bindParam(":id_propiedad", $data->id_propiedad);
            $stmt->bindParam(":id_usuario", $id_usuario);
            $stmt->bindParam(":nombre", $data->nombre);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":telefono", $data->telefono ?? null);
            $stmt->bindParam(":mensaje", $data->mensaje);
            
            if ($stmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Mensaje enviado exitosamente"
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("success" => false, "message" => "Error al enviar el mensaje"));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error: " . $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Datos incompletos"));
    }
    
} elseif ($method == 'GET') {
    // Obtener mensajes (solo para propietarios)
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "No autenticado"));
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $property_id = isset($_GET['property_id']) ? (int)$_GET['property_id'] : null;
    
    try {
        if ($property_id) {
            // Mensajes de una propiedad específica
            $query = "SELECT m.*, p.titulo as propiedad_titulo 
                      FROM mensajes m
                      INNER JOIN propiedades p ON m.id_propiedad = p.id
                      WHERE p.id_usuario = :user_id AND m.id_propiedad = :property_id
                      ORDER BY m.fecha_envio DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":property_id", $property_id);
        } else {
            // Todos los mensajes del usuario
            $query = "SELECT m.*, p.titulo as propiedad_titulo 
                      FROM mensajes m
                      INNER JOIN propiedades p ON m.id_propiedad = p.id
                      WHERE p.id_usuario = :user_id
                      ORDER BY m.fecha_envio DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
        }
        
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(array(
            "success" => true,
            "messages" => $messages
        ));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("success" => false, "message" => "Error: " . $e->getMessage()));
    }
    
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Método no permitido"));
}
?>

