<?php
// Verificar sesión
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?> - MI HOGAR EN LINEA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <a href="../index.php" style="text-decoration: none; color: inherit;">
                    <h1>MI <span>HOGAR</span> EN LINEA</h1>
                </a>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar" id="userAvatar">?</div>
                    <span id="userName">Cargando...</span>
                </div>
                <a href="#" class="btn btn-outline" id="logoutBtn">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Dashboard Layout -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="publicar-propiedad.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'publicar-propiedad.php') ? 'class="active"' : ''; ?>><i class="fas fa-plus-circle"></i> Publicar Propiedad</a></li>
                <li><a href="mis-propiedades.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'mis-propiedades.php') ? 'class="active"' : ''; ?>><i class="fas fa-home"></i> Mis Propiedades</a></li>
                <li><a href="configuracion.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'configuracion.php') ? 'class="active"' : ''; ?>><i class="fas fa-cog"></i> Configuración</a></li>
                <li><a href="../index.php" class="sidebar-external-link"><i class="fas fa-globe"></i> Página Principal</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">

