<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
?>

<div class="section-content active">
                <div class="dashboard-header">
                    <h2>Dashboard</h2>
                    <a href="publicar-propiedad.php" class="btn btn-primario"><i class="fas fa-plus"></i> Publicar Nueva Propiedad</a>
                </div>

                <!-- Estadísticas -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalProperties">0</h3>
                            <p>Propiedades Publicadas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon views">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalViews">0</h3>
                            <p>Visualizaciones</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon messages">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalMessages">0</h3>
                            <p>Mensajes Nuevos</p>
                        </div>
                    </div>
                </div>

                <!-- Propiedades recientes -->
                <div class="dashboard-header">
                    <h2>Propiedades Recientes</h2>
                </div>
                
                <div class="properties-list" id="recentProperties">
                    <!-- Las propiedades se cargarán aquí dinámicamente -->
                </div>
            </div>

<?php require_once 'includes/footer.php'; ?>
