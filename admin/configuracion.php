<?php
$pageTitle = 'Configuración';
require_once 'includes/header.php';
?>

<div class="section-content active">
    <div class="dashboard-header">
        <h2>Configuración de Cuenta</h2>
    </div>
    <div class="form-container">
        <form id="settingsForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="settingsNombre">Nombre Completo *</label>
                    <input type="text" id="settingsNombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="settingsEmail">Email *</label>
                    <input type="email" id="settingsEmail" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="settingsTelefono">Teléfono</label>
                    <input type="tel" id="settingsTelefono" class="form-control">
                </div>
                <div class="form-group">
                    <label for="settingsWhatsapp">WhatsApp *</label>
                    <input type="tel" id="settingsWhatsapp" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="settingsPassword">Nueva Contraseña (dejar vacío para no cambiar)</label>
                <input type="password" id="settingsPassword" class="form-control" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primario" id="saveSettingsBtn">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

