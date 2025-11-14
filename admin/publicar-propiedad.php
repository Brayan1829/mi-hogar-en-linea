<?php
$pageTitle = 'Publicar Propiedad';
require_once 'includes/header.php';
?>

<div class="section-content active">
    <div class="form-container">
        <div class="form-header">
            <h3><i class="fas fa-plus-circle"></i> Publicar Nueva Propiedad</h3>
        </div>
        
        <form id="propertyForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="propertyTitle">Título de la Propiedad *</label>
                    <input type="text" id="propertyTitle" class="form-control" placeholder="Ej: Apartamento moderno en zona residencial" required>
                </div>
                <div class="form-group">
                    <label for="propertyType">Tipo de Propiedad *</label>
                    <select id="propertyType" class="form-control" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="apartamento">Apartamento</option>
                        <option value="casa">Casa</option>
                        <option value="estudio">Estudio</option>
                        <option value="duplex">Dúplex</option>
                        <option value="penthouse">Penthouse</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="propertyPrice">Precio Mensual (USD) *</label>
                    <input type="number" id="propertyPrice" class="form-control" placeholder="Ej: 850" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="propertyLocation">Ubicación *</label>
                    <input type="text" id="propertyLocation" class="form-control" placeholder="Ej: Sector Norte, Ciudad" required>
                </div>
            </div>

            <div class="form-group">
                <label for="propertyAddress">Dirección Completa</label>
                <input type="text" id="propertyAddress" class="form-control" placeholder="Ej: Calle Principal #123, entre Avenida A y B">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms">Habitaciones *</label>
                    <select id="bedrooms" class="form-control" required>
                        <option value="">Selecciona</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bathrooms">Baños *</label>
                    <select id="bathrooms" class="form-control" required>
                        <option value="">Selecciona</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4+</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="area">Área (m²) *</label>
                    <input type="number" id="area" class="form-control" placeholder="Ej: 120" min="0" step="0.01" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="amueblado"> Amueblado
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="mascotas"> Se permiten mascotas
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="estacionamiento"> Estacionamiento
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="propertyDescription">Descripción *</label>
                <textarea id="propertyDescription" class="form-control" placeholder="Describe tu propiedad en detalle..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Imágenes de la Propiedad</label>
                <div id="existingImagesContainer"></div>
                <div class="file-upload" id="fileUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Arrastra y suelta las imágenes aquí o haz clic para seleccionar</p>
                    <button type="button" class="btn btn-outline">Seleccionar Archivos</button>
                    <input type="file" id="propertyImages" class="file-input" multiple accept="image/*">
                </div>
                <div class="file-preview" id="filePreview"></div>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primario" id="publishPropertyBtn">Publicar Propiedad</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

