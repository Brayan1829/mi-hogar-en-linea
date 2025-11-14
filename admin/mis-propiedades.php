<?php
$pageTitle = 'Mis Propiedades';
require_once 'includes/header.php';
?>

<div class="section-content active">
    <div class="dashboard-header">
        <h2>Mis Propiedades Publicadas</h2>
        <a href="publicar-propiedad.php" class="btn btn-primario"><i class="fas fa-plus"></i> Nueva Propiedad</a>
    </div>
    
    <div class="properties-list" id="userPropertiesList">
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--gris);"></i>
            <p style="margin-top: 20px; color: var(--gris);">Cargando propiedades...</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

