<?php
session_start();
require_once 'config/database.php';
require_once 'models/Property.php';

// Obtener el ID de la propiedad
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id <= 0) {
    header('Location: property.php');
    exit;
}

// Crear instancia de Database y Property
$database = new Database();
$pdo = $database->getConnection();
$property = new Property($db = $pdo);

// Obtener la propiedad
$propiedad = $property->getPropertyById($property_id);

if (!$propiedad) {
    header('Location: property.php');
    exit;
}

// Obtener imágenes de la propiedad
$images_stmt = $property->getPropertyImages($property_id);
$images = [];
while ($row = $images_stmt->fetch(PDO::FETCH_ASSOC)) {
    $images[] = $row;
}

// Obtener información del propietario
try {
    $stmt = $pdo->prepare("SELECT nombre, email, telefono, whatsapp FROM usuarios WHERE id = :user_id");
    $stmt->bindParam(':user_id', $propiedad['id_usuario']);
    $stmt->execute();
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $propietario = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($propiedad['titulo']); ?> - Mi Hogar en Línea</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/property-details.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>MI <span>HOGAR</span> EN LINEA</h1>
            </div>
            <nav>
                <a href="index.php" class="btn btn-primario">Volver al Inicio</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="property-header">
            <h1 class="property-title"><?php echo htmlspecialchars($propiedad['titulo']); ?></h1>
            <div class="property-location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo htmlspecialchars($propiedad['direccion'] . ', ' . $propiedad['ubicacion']); ?></span>
            </div>
            <div class="property-price">$<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>/mes</div>
        </div>

        <!-- Galería de imágenes -->
        <div class="property-images">
            <?php if (!empty($images)): ?>
                <img src="<?php echo htmlspecialchars($images[0]['imagen_url']); ?>" 
                     alt="<?php echo htmlspecialchars($propiedad['titulo']); ?>" 
                     class="main-image" 
                     id="mainImage">
                <div class="thumbnail-grid">
                    <?php 
                    $thumbnails = array_slice($images, 1, 4);
                    foreach ($thumbnails as $thumb): 
                    ?>
                        <img src="<?php echo htmlspecialchars($thumb['imagen_url']); ?>" 
                             alt="Thumbnail" 
                             class="thumbnail" 
                             onclick="changeMainImage('<?php echo htmlspecialchars($thumb['imagen_url']); ?>')">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                     alt="Imagen por defecto" 
                     class="main-image">
            <?php endif; ?>
        </div>

        <div class="property-content">
            <div class="property-details">
                <h2 class="section-title">Descripción</h2>
                <div class="property-description">
                    <?php echo nl2br(htmlspecialchars($propiedad['descripcion'] ?: 'No hay descripción disponible.')); ?>
                </div>

                <h2 class="section-title">Características</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-bed"></i>
                        <div>
                            <strong><?php echo $propiedad['habitaciones'] ?? 0; ?></strong>
                            <div>Habitaciones</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bath"></i>
                        <div>
                            <strong><?php echo $propiedad['banos'] ?? 0; ?></strong>
                            <div>Baños</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-vector-square"></i>
                        <div>
                            <strong><?php echo $propiedad['area'] ?? 0; ?> m²</strong>
                            <div>Área</div>
                        </div>
                    </div>
                </div>

                <h2 class="section-title">Información Adicional</h2>
                <div class="property-info">
                    <div class="info-item">
                        <i class="fas fa-home"></i>
                        <span><strong>Tipo:</strong> <?php echo ucfirst($propiedad['tipo']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-car"></i>
                        <span><strong>Estacionamiento:</strong> <?php echo $propiedad['estacionamiento'] ? 'Sí' : 'No'; ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-paw"></i>
                        <span><strong>Mascotas:</strong> <?php echo $propiedad['mascotas'] ? 'Permitidas' : 'No permitidas'; ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-couch"></i>
                        <span><strong>Amueblado:</strong> <?php echo $propiedad['amueblado'] ? 'Sí' : 'No'; ?></span>
                    </div>
                </div>
            </div>

            <div class="contact-card">
                <h2 class="section-title">Contactar Propietario</h2>
                <?php if ($propietario): ?>
                    <div style="margin-bottom: 20px;">
                        <p><strong>Propietario:</strong> <?php echo htmlspecialchars($propietario['nombre']); ?></p>
                        <?php if ($propietario['telefono']): ?>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($propietario['telefono']); ?></p>
                        <?php endif; ?>
                        <?php if ($propietario['whatsapp']): ?>
                            <p style="margin-top: 10px;">
                                <strong>WhatsApp:</strong> <?php echo htmlspecialchars($propietario['whatsapp']); ?>
                            </p>
                            <?php
                            // Limpiar el número de WhatsApp (remover espacios, guiones, etc.)
                            $whatsapp_clean = preg_replace('/[^0-9+]/', '', $propietario['whatsapp']);
                            
                            // Normalizar el número de WhatsApp
                            // Si no empieza con +, asumimos que es un número colombiano
                            if (strpos($whatsapp_clean, '+') !== 0) {
                                // Si tiene 10 dígitos, es un número colombiano sin código de país
                                $digits_only = preg_replace('/[^0-9]/', '', $whatsapp_clean);
                                if (strlen($digits_only) == 10) {
                                    // Agregar código de país de Colombia (+57)
                                    $whatsapp_clean = '57' . $digits_only;
                                } elseif (strlen($digits_only) > 10) {
                                    // Si tiene más de 10 dígitos, puede que ya tenga código de país sin el +
                                    // Intentar detectar si empieza con 57 (Colombia)
                                    if (substr($digits_only, 0, 2) == '57' && strlen($digits_only) == 12) {
                                        $whatsapp_clean = $digits_only;
                                    } else {
                                        // Si no empieza con 57 y tiene más de 10 dígitos, agregar 57
                                        $whatsapp_clean = '57' . $digits_only;
                                    }
                                } else {
                                    // Si tiene menos de 10 dígitos, usar tal cual (puede ser número internacional sin +)
                                    $whatsapp_clean = $digits_only;
                                }
                            } else {
                                // Si ya tiene +, removerlo para la URL (wa.me no necesita el +)
                                $whatsapp_clean = str_replace('+', '', $whatsapp_clean);
                            }
                            
                            $mensaje_whatsapp = urlencode("Hola, estoy interesado en la propiedad: " . $propiedad['titulo']);
                            $whatsapp_url = "https://wa.me/" . $whatsapp_clean . "?text=" . $mensaje_whatsapp;
                            ?>
                            <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="btn btn-whatsapp" style="width: 100%; margin-top: 15px; margin-bottom: 20px;">
                                <i class="fab fa-whatsapp"></i> Enviar Mensaje por WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--gris);">Información del propietario no disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function changeMainImage(imageUrl) {
            document.getElementById('mainImage').src = imageUrl;
        }
    </script>
</body>
</html>

