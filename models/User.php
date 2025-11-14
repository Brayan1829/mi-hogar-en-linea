<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $telefono;
    public $whatsapp;
    public $avatar;
    public $fecha_registro;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT id, nombre, email, password, telefono, whatsapp, avatar 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND activo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->nombre = $row['nombre'];
                $this->email = $row['email'];
                $this->telefono = $row['telefono'];
                $this->whatsapp = $row['whatsapp'];
                $this->avatar = $row['avatar'];
                
                return true;
            }
        }
        return false;
    }

    public function register($nombre, $email, $password, $telefono = null, $whatsapp = null) {
        // Verificar si el email ya existe
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false; // Email ya existe
        }

        // Insertar nuevo usuario
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, email=:email, password=:password, telefono=:telefono, whatsapp=:whatsapp";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":whatsapp", $whatsapp);
        
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $password_hash);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->nombre = $nombre;
            $this->email = $email;
            $this->telefono = $telefono;
            $this->whatsapp = $whatsapp;
            return true;
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT id, nombre, email, telefono, whatsapp, avatar, fecha_registro 
                  FROM " . $this->table_name . " 
                  WHERE id = :id AND activo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        }
        return false;
    }

    public function update($id, $nombre, $email, $telefono = null, $whatsapp = null, $password = null) {
        // Verificar si el email ya existe en otro usuario
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false; // Email ya existe en otro usuario
        }

        // Construir query de actualización
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre=:nombre, email=:email, telefono=:telefono, whatsapp=:whatsapp";
        
        if ($password) {
            $query .= ", password=:password";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":whatsapp", $whatsapp);
        
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $password_hash);
        }

        if ($stmt->execute()) {
            return $this->getUserById($id);
        }
        return false;
    }
}
?>