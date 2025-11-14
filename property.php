<?php
// property.php - Mostrar propiedades disponibles
session_start();
require_once 'config/database.php';

// Crear instancia de Database y obtener conexión PDO
$database = new Database();
$pdo = $database->getConnection();

// Obtener filtros de la URL
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_precio_min = isset($_GET['precio_min']) ? (float)$_GET['precio_min'] : 0;
$filtro_precio_max = isset($_GET['precio_max']) ? (float)$_GET['precio_max'] : 0;
$filtro_habitaciones = isset($_GET['habitaciones']) ? (int)$_GET['habitaciones'] : 0;
$filtro_ciudad = isset($_GET['ciudad']) ? $_GET['ciudad'] : '';

// Construir la consulta con filtros
$query = "
    SELECT 
        p.id,
        p.titulo,
        p.descripcion,
        p.precio,
        p.direccion,
        p.ubicacion as ciudad,
        p.habitaciones,
        p.banos,
        p.area,
        p.tipo as tipo_propiedad,
        p.estado,
        p.estacionamiento,
        p.mascotas,
        p.amueblado,
        p.fecha_publicacion as fecha_creacion,
        ip.imagen_url as imagen_principal
    FROM propiedades p
    LEFT JOIN imagenes_propiedad ip ON p.id = ip.id_propiedad AND ip.es_principal = 1
    WHERE p.estado = 'disponible'
";

$params = [];

if (!empty($filtro_tipo)) {
    $query .= " AND p.tipo = :tipo";
    $params[':tipo'] = $filtro_tipo;
}

if ($filtro_precio_min > 0) {
    $query .= " AND p.precio >= :precio_min";
    $params[':precio_min'] = $filtro_precio_min;
}

if ($filtro_precio_max > 0) {
    $query .= " AND p.precio <= :precio_max";
    $params[':precio_max'] = $filtro_precio_max;
}

if ($filtro_habitaciones > 0) {
    $query .= " AND p.habitaciones >= :habitaciones";
    $params[':habitaciones'] = $filtro_habitaciones;
}

if (!empty($filtro_ciudad)) {
    $query .= " AND p.ubicacion LIKE :ciudad";
    $params[':ciudad'] = '%' . $filtro_ciudad . '%';
}

$query .= " ORDER BY p.fecha_publicacion DESC";

// Consulta para obtener las propiedades disponibles
try {
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las propiedades: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propiedades Disponibles - Mi Hogar en Línea</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/property.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div>
                <h1><i class="fas fa-home"></i> Propiedades Disponibles</h1>
                <p>Encuentra tu hogar ideal entre nuestra selección de propiedades</p>
            </div>
            <div class="header-nav">
                <a href="index.php" class="btn-header">Inicio</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin/dashboard.php" class="btn-header">Dashboard</a>
                    <a href="logout.php" class="btn-header">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-header">Iniciar Sesión</a>
                    <a href="registro.php" class="btn-header">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Filtros -->
        <section class="filtros">
            <h3><i class="fas fa-filter"></i> Filtrar Propiedades</h3>
            <form method="GET" class="filtro-grid">
                <div class="form-group">
                    <label for="tipo">Tipo de Propiedad</label>
                    <select id="tipo" name="tipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <option value="apartamento" <?php echo ($filtro_tipo == 'apartamento') ? 'selected' : ''; ?>>Apartamento</option>
                        <option value="casa" <?php echo ($filtro_tipo == 'casa') ? 'selected' : ''; ?>>Casa</option>
                        <option value="estudio" <?php echo ($filtro_tipo == 'estudio') ? 'selected' : ''; ?>>Estudio</option>
                        <option value="duplex" <?php echo ($filtro_tipo == 'duplex') ? 'selected' : ''; ?>>Dúplex</option>
                        <option value="penthouse" <?php echo ($filtro_tipo == 'penthouse') ? 'selected' : ''; ?>>Penthouse</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="precio_min">Precio Mínimo</label>
                    <input type="number" id="precio_min" name="precio_min" class="form-control" placeholder="Mínimo" value="<?php echo $filtro_precio_min > 0 ? $filtro_precio_min : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="precio_max">Precio Máximo</label>
                    <input type="number" id="precio_max" name="precio_max" class="form-control" placeholder="Máximo" value="<?php echo $filtro_precio_max > 0 ? $filtro_precio_max : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="habitaciones">Habitaciones</label>
                    <select id="habitaciones" name="habitaciones" class="form-control">
                        <option value="">Cualquiera</option>
                        <option value="1" <?php echo ($filtro_habitaciones == 1) ? 'selected' : ''; ?>>1+</option>
                        <option value="2" <?php echo ($filtro_habitaciones == 2) ? 'selected' : ''; ?>>2+</option>
                        <option value="3" <?php echo ($filtro_habitaciones == 3) ? 'selected' : ''; ?>>3+</option>
                        <option value="4" <?php echo ($filtro_habitaciones == 4) ? 'selected' : ''; ?>>4+</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ciudad">Ubicación</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" placeholder="Ej: Madrid" value="<?php echo htmlspecialchars($filtro_ciudad); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                <?php if (!empty($filtro_tipo) || $filtro_precio_min > 0 || $filtro_precio_max > 0 || $filtro_habitaciones > 0 || !empty($filtro_ciudad)): ?>
                <div class="form-group">
                    <a href="property.php" class="btn" style="background-color: #95a5a6; color: white; text-decoration: none; display: inline-block; padding: 0.75rem 1.5rem;">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </section>

        <!-- Grid de propiedades -->
        <section class="propiedades-lista">
            <?php if (isset($propiedades) && count($propiedades) > 0): ?>
                <div class="propiedades-grid">
                    <?php foreach ($propiedades as $propiedad): ?>
                        <div class="propiedad-card">
                            <!-- Imagen de la propiedad -->
                            <img 
                                src="<?php echo $propiedad['imagen_principal'] ?: 'images/default-property.jpg'; ?>" 
                                alt="<?php echo htmlspecialchars($propiedad['titulo']); ?>"
                                class="propiedad-imagen"
                                onerror="this.src='images/default-property.jpg'"
                            >
                            
                            <div class="propiedad-content">
                                <!-- Header con precio y tipo -->
                                <div class="propiedad-header">
                                    <div class="propiedad-precio">
                                        $<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>
                                    </div>
                                    <span class="propiedad-tipo">
                                        <?php echo ucfirst($propiedad['tipo_propiedad']); ?>
                                    </span>
                                </div>

                                <!-- Título y dirección -->
                                <h3 class="propiedad-titulo">
                                    <?php echo htmlspecialchars($propiedad['titulo']); ?>
                                </h3>
                                <div class="propiedad-direccion">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($propiedad['direccion'] . ', ' . $propiedad['ciudad']); ?>
                                </div>

                                <!-- Características principales -->
                                <div class="propiedad-caracteristicas">
                                    <div class="caracteristica">
                                        <i class="fas fa-bed"></i>
                                        <span><?php echo $propiedad['habitaciones']; ?> hab.</span>
                                    </div>
                                    <div class="caracteristica">
                                        <i class="fas fa-bath"></i>
                                        <span><?php echo $propiedad['banos']; ?> baños</span>
                                    </div>
                                    <div class="caracteristica">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span><?php echo $propiedad['area']; ?> m²</span>
                                    </div>
                                </div>

                                <!-- Características adicionales -->
                                <div class="propiedad-extra">
                                    <div class="extra-item">
                                        <i class="fas fa-car"></i>
                                        <span><?php echo $propiedad['estacionamiento'] ? 'Con garage' : 'Sin garage'; ?></span>
                                    </div>
                                    <div class="extra-item">
                                        <i class="fas fa-paw"></i>
                                        <span><?php echo $propiedad['mascotas'] ? 'Mascotas OK' : 'No mascotas'; ?></span>
                                    </div>
                                </div>

                                <!-- Botón ver detalles -->
                                <button class="btn-ver-detalles" onclick="verDetalles(<?php echo $propiedad['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Estado vacío -->
                <div class="empty-state">
                    <i class="fas fa-home"></i>
                    <h3>No hay propiedades disponibles en este momento</h3>
                    <p>Vuelve más tarde para descubrir nuevas oportunidades</p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Mi Hogar en Línea. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        function verDetalles(propiedadId) {
            window.location.href = 'property-details.php?id=' + propiedadId;
        }

        // Los filtros ya funcionan con el método GET del formulario
    </script>
</body>
</html>