// Variables globales
// currentUser está definido en common.js
let userProperties = [];
let tabId = null;
let broadcastChannel = null;

// Inicializar la aplicación
document.addEventListener('DOMContentLoaded', function() {
    initTabSecurity();
    checkAuth();
    setupEventListeners();
});

// ============================================
// SISTEMA DE SEGURIDAD - UNA SOLA PESTAÑA
// ============================================

// Sistema de seguridad: cerrar sesión en nueva pestaña
function initTabSecurity() {
    // Generar ID único para esta pestaña
    tabId = 'tab_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    // Crear canal de comunicación entre pestañas
    broadcastChannel = new BroadcastChannel('dashboard_session');
    
    let isNewTab = false;
    
    // Escuchar mensajes de otras pestañas
    broadcastChannel.onmessage = function(event) {
        if (event.data.type === 'tab_heartbeat') {
            if (event.data.tabId !== tabId) {
                // Hay otra pestaña activa (más antigua), esta es la nueva
                isNewTab = true;
                handleSessionClose();
            }
        } else if (event.data.type === 'new_tab_opened') {
            if (event.data.tabId !== tabId) {
                // Otra pestaña se abrió, verificar si esta es más nueva
                const otherTimestamp = event.data.timestamp;
                const thisTimestamp = Date.now();
                
                // Si esta pestaña es más nueva (se abrió después), cerrar sesión
                if (thisTimestamp > otherTimestamp) {
                    isNewTab = true;
                    handleSessionClose();
                }
            }
        }
    };
    
    // Verificar si hay otra pestaña activa al cargar
    const storedTabId = sessionStorage.getItem('dashboard_tab_id');
    const storedTimestamp = sessionStorage.getItem('dashboard_tab_timestamp');
    
    if (storedTabId && storedTabId !== tabId && storedTimestamp) {
        const storedTime = parseInt(storedTimestamp);
        const currentTime = Date.now();
        
        // Si hay una pestaña activa previa, esta es la nueva pestaña
        if (currentTime - storedTime > 100) {
            isNewTab = true;
            setTimeout(function() {
                handleSessionClose();
            }, 100);
            return; // No continuar con la inicialización
        }
    }
    
    // Esta es la pestaña original, guardar su ID
    sessionStorage.setItem('dashboard_tab_id', tabId);
    sessionStorage.setItem('dashboard_tab_timestamp', Date.now().toString());
    
    // Notificar a otras pestañas que esta es la pestaña activa
    broadcastChannel.postMessage({
        type: 'new_tab_opened',
        tabId: tabId,
        timestamp: Date.now()
    });
    
    // Enviar latido periódico para indicar que esta pestaña está activa
    setInterval(function() {
        if (!isNewTab) {
            broadcastChannel.postMessage({
                type: 'tab_heartbeat',
                tabId: tabId,
                timestamp: Date.now()
            });
        }
    }, 2000);
    
    // Detectar cuando se cierra la pestaña
    window.addEventListener('beforeunload', function() {
        sessionStorage.removeItem('dashboard_tab_id');
        sessionStorage.removeItem('dashboard_tab_timestamp');
        broadcastChannel.postMessage({
            type: 'tab_closing',
            tabId: tabId
        });
    });
}

// Cerrar sesión cuando se detecta que esta es una nueva pestaña
async function handleSessionClose() {
    try {
        // Cerrar sesión en el servidor
        await fetch('../api/auth.php', {
            method: 'DELETE',
            credentials: 'include'
        });
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    }
    
    // Mostrar mensaje y redirigir
    alert('Por seguridad, solo puedes tener una sesión activa a la vez. Esta pestaña será cerrada.');
    window.location.href = '../login.php';
}

// ============================================
// AUTENTICACIÓN Y USUARIO
// ============================================

// Verificar autenticación
async function checkAuth() {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // currentUser y updateUserInfo están en common.js
            if (typeof updateUserInfo === 'function') {
                currentUser = data.user;
                updateUserInfo();
            }
            loadUserProperties();
        } else {
            window.location.href = '../login.php';
        }
    } catch (error) {
        console.error('Error al verificar autenticación:', error);
        window.location.href = '../login.php';
    }
}

// updateUserInfo está definido en common.js

// ============================================
// NAVEGACIÓN Y EVENT LISTENERS
// ============================================

// Configurar event listeners
function setupEventListeners() {
    // No hay navegación interna en el dashboard principal

    // El botón de cerrar sesión está manejado en common.js
}

// Función showSection ya no es necesaria en el dashboard principal

// ============================================
// PROPIEDADES
// ============================================

// Cargar propiedades del usuario
async function loadUserProperties() {
    try {
        const response = await fetch('../api/properties.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            userProperties = data.properties;
            updatePropertiesUI();
            updateStats();
        } else {
            console.error('Error al cargar propiedades:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar propiedades:', error);
    }
}

// Actualizar UI de propiedades
function updatePropertiesUI() {
    const recentContainer = document.getElementById('recentProperties');
    
    if (!recentContainer) return;
    
    // Limpiar contenedor
    recentContainer.innerHTML = '';
    
    if (userProperties.length === 0) {
        recentContainer.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-home" style="font-size: 3rem; color: var(--gris); margin-bottom: 20px;"></i>
                <p style="color: var(--gris);">No tienes propiedades publicadas aún.</p>
                <a href="publicar-propiedad.php" class="btn btn-primario" style="margin-top: 20px;"><i class="fas fa-plus"></i> Publicar Primera Propiedad</a>
            </div>
        `;
        return;
    }
    
    // Mostrar las 3 propiedades más recientes en el dashboard
    const recentProperties = userProperties.slice(0, 3);
    recentProperties.forEach(property => {
        recentContainer.appendChild(createPropertyCard(property));
    });
    
    // Mostrar todas las propiedades en la sección de propiedades
    userProperties.forEach(property => {
        propertiesContainer.appendChild(createPropertyCard(property));
    });
}

// Crear tarjeta de propiedad
function createPropertyCard(property) {
    const card = document.createElement('div');
    card.className = 'property-card';
    card.dataset.id = property.id;
    
    const statusClass = property.estado === 'disponible' ? '' : 'style="background-color: #e53e3e;"';
    const imageUrl = property.imagen_principal || 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80';
    
    card.innerHTML = `
        <div class="property-image" style="background-image: url('${imageUrl}');">
            <span class="property-status" ${statusClass}>${property.estado}</span>
            <div class="property-actions">
                <div class="property-action" title="Editar">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="property-action" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
        </div>
        <div class="property-info">
            <div class="property-price">$${parseFloat(property.precio).toLocaleString()}/mes</div>
            <h3 class="property-title">${property.titulo}</h3>
            <div class="property-location"><i class="fas fa-map-marker-alt"></i> ${property.ubicacion}</div>
            <div class="property-features">
                <div class="property-feature">
                    <i class="fas fa-bed"></i> ${property.habitaciones} Habitaciones
                </div>
                <div class="property-feature">
                    <i class="fas fa-bath"></i> ${property.banos} Baños
                </div>
                <div class="property-feature">
                    <i class="fas fa-vector-square"></i> ${property.area} m²
                </div>
            </div>
            <div class="property-messages">
                <small><i class="fas fa-envelope"></i> ${property.total_mensajes || 0} mensajes</small>
            </div>
        </div>
    `;
    
    // Agregar event listeners a los botones de acción
    const editBtn = card.querySelector('.property-action .fa-edit').closest('.property-action');
    const deleteBtn = card.querySelector('.property-action .fa-trash').closest('.property-action');
    
    editBtn.addEventListener('click', () => {
        window.location.href = `publicar-propiedad.php?edit=${property.id}`;
    });
    deleteBtn.addEventListener('click', () => deleteProperty(property.id));
    
    return card;
}

// Actualizar estadísticas
function updateStats() {
    document.getElementById('totalProperties').textContent = userProperties.length;
    
    // Calcular total de mensajes
    const totalMessages = userProperties.reduce((sum, prop) => sum + (parseInt(prop.total_mensajes) || 0), 0);
    document.getElementById('totalMessages').textContent = totalMessages;
    
    // Las visualizaciones podrían calcularse de manera similar
    document.getElementById('totalViews').textContent = userProperties.length * 15; // Ejemplo
}

// Manejar envío del formulario de propiedad
async function handlePropertySubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('publishPropertyBtn');
    const originalText = submitBtn.innerHTML;
    
    try {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (editingPropertyId ? 'Guardando...' : 'Publicando...');
        submitBtn.disabled = true;
        
        const propertyData = {
            titulo: document.getElementById('propertyTitle').value,
            descripcion: document.getElementById('propertyDescription').value,
            tipo: document.getElementById('propertyType').value,
            precio: parseFloat(document.getElementById('propertyPrice').value),
            ubicacion: document.getElementById('propertyLocation').value,
            direccion: document.getElementById('propertyAddress').value,
            habitaciones: parseInt(document.getElementById('bedrooms').value),
            banos: parseInt(document.getElementById('bathrooms').value),
            area: parseFloat(document.getElementById('area').value),
            amueblado: document.getElementById('amueblado').checked ? 1 : 0,
            mascotas: document.getElementById('mascotas').checked ? 1 : 0,
            estacionamiento: document.getElementById('estacionamiento').checked ? 1 : 0
        };
        
        // Si estamos editando, agregar el ID y usar PUT
        if (editingPropertyId) {
            propertyData.id = editingPropertyId;
        }
        
        const response = await fetch('../api/properties.php', {
            method: editingPropertyId ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(propertyData),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const propertyId = editingPropertyId || data.property_id;
            
            // Subir imágenes nuevas si hay alguna
            const files = document.getElementById('propertyImages').files;
            if (files.length > 0) {
                await uploadPropertyImages(propertyId, files);
            }
            
            alert(editingPropertyId ? '¡Propiedad actualizada exitosamente!' : '¡Propiedad publicada exitosamente!');
            resetPropertyForm();
            showSection('dashboard');
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.querySelector('[data-section="dashboard"]').classList.add('active');
            
            // Recargar propiedades
            await loadUserProperties();
        } else {
            alert('Error al publicar la propiedad: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al publicar la propiedad');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Subir imágenes de propiedad
async function uploadPropertyImages(propertyId, files) {
    const formData = new FormData();
    formData.append('property_id', propertyId);
    
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }
    
    try {
        const response = await fetch('../api/upload.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (!data.success) {
            console.error('Error al subir imágenes:', data.message);
        }
    } catch (error) {
        console.error('Error al subir imágenes:', error);
    }
}

// Configurar subida de archivos
function setupFileUpload() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('propertyImages');
    const filePreview = document.getElementById('filePreview');
    
    fileUploadArea.addEventListener('click', () => {
        fileInput.click();
    });
    
    fileUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadArea.style.borderColor = 'var(--azul-primario)';
        fileUploadArea.style.backgroundColor = 'rgba(26, 58, 95, 0.05)';
    });
    
    fileUploadArea.addEventListener('dragleave', () => {
        fileUploadArea.style.borderColor = '#e2e8f0';
        fileUploadArea.style.backgroundColor = 'transparent';
    });
    
    fileUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadArea.style.borderColor = '#e2e8f0';
        fileUploadArea.style.backgroundColor = 'transparent';
        
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelection();
        }
    });
    
    fileInput.addEventListener('change', handleFileSelection);
    
    function handleFileSelection() {
        filePreview.innerHTML = '';
        const files = fileInput.files;
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!file.type.match('image.*')) continue;
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'file-preview-item';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                
                const removeBtn = document.createElement('div');
                removeBtn.className = 'remove';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function() {
                    previewItem.remove();
                });
                
                previewItem.appendChild(img);
                previewItem.appendChild(removeBtn);
                filePreview.appendChild(previewItem);
            }
            
            reader.readAsDataURL(file);
        }
    }
}

// Variable global para controlar si estamos editando
let editingPropertyId = null;
let existingImages = [];

// Resetear formulario de propiedad
function resetPropertyForm() {
    document.getElementById('propertyForm').reset();
    document.getElementById('filePreview').innerHTML = '';
    document.getElementById('propertyImages').value = '';
    document.getElementById('existingImagesContainer').innerHTML = '';
    document.getElementById('publishPropertyBtn').innerHTML = '<i class="fas fa-plus-circle"></i> Publicar Propiedad';
    editingPropertyId = null;
    existingImages = [];
    
    // Cambiar título del formulario
    const formHeader = document.querySelector('#publish-section .form-header h3');
    if (formHeader) {
        formHeader.innerHTML = '<i class="fas fa-plus-circle"></i> Publicar Nueva Propiedad';
    }
}

// Editar propiedad
async function editProperty(propertyId) {
    try {
        // Cambiar a la sección de publicar
        showSection('publish-section');
        
        // Cambiar título del formulario
        const formHeader = document.querySelector('#publish-section .form-header h3');
        if (formHeader) {
            formHeader.innerHTML = '<i class="fas fa-edit"></i> Editar Propiedad';
        }
        
        // Obtener datos de la propiedad
        const response = await fetch(`../api/properties.php?id=${propertyId}`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success && data.property) {
            const prop = data.property;
            editingPropertyId = propertyId;
            existingImages = prop.imagenes || [];
            
            // Llenar el formulario con los datos
            document.getElementById('propertyTitle').value = prop.titulo || '';
            document.getElementById('propertyType').value = prop.tipo || '';
            document.getElementById('propertyPrice').value = prop.precio || '';
            document.getElementById('propertyLocation').value = prop.ubicacion || '';
            document.getElementById('propertyAddress').value = prop.direccion || '';
            document.getElementById('bedrooms').value = prop.habitaciones || '';
            document.getElementById('bathrooms').value = prop.banos || '';
            document.getElementById('area').value = prop.area || '';
            document.getElementById('amueblado').checked = prop.amueblado == 1;
            document.getElementById('mascotas').checked = prop.mascotas == 1;
            document.getElementById('estacionamiento').checked = prop.estacionamiento == 1;
            document.getElementById('propertyDescription').value = prop.descripcion || '';
            
            // Mostrar imágenes existentes
            displayExistingImages(existingImages);
            
            // Cambiar texto del botón
            document.getElementById('publishPropertyBtn').innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
            
            // Scroll al formulario
            document.getElementById('publish-section').scrollIntoView({ behavior: 'smooth' });
        } else {
            alert('Error al cargar la propiedad: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar la propiedad');
    }
}

// Mostrar imágenes existentes
function displayExistingImages(images) {
    const container = document.getElementById('existingImagesContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (images.length === 0) {
        container.innerHTML = '<p style="color: var(--gris); margin-top: 10px;">No hay imágenes subidas</p>';
        return;
    }
    
    const imagesGrid = document.createElement('div');
    imagesGrid.style.display = 'grid';
    imagesGrid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(150px, 1fr))';
    imagesGrid.style.gap = '15px';
    imagesGrid.style.marginTop = '15px';
    
    images.forEach(image => {
        const imageCard = document.createElement('div');
        imageCard.style.position = 'relative';
        imageCard.style.border = '2px solid #e2e8f0';
        imageCard.style.borderRadius = '8px';
        imageCard.style.overflow = 'hidden';
        imageCard.style.background = 'white';
        
        const img = document.createElement('img');
        img.src = '../' + image.imagen_url;
        img.style.width = '100%';
        img.style.height = '150px';
        img.style.objectFit = 'cover';
        img.style.display = 'block';
        
        const overlay = document.createElement('div');
        overlay.style.position = 'absolute';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.background = 'rgba(0,0,0,0.5)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.gap = '10px';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s';
        
        const primaryBadge = document.createElement('span');
        if (image.es_principal == 1) {
            primaryBadge.textContent = 'Principal';
            primaryBadge.style.background = 'var(--verde)';
            primaryBadge.style.color = 'white';
            primaryBadge.style.padding = '5px 10px';
            primaryBadge.style.borderRadius = '4px';
            primaryBadge.style.fontSize = '0.8rem';
            primaryBadge.style.position = 'absolute';
            primaryBadge.style.top = '5px';
            primaryBadge.style.left = '5px';
            imageCard.appendChild(primaryBadge);
        }
        
        const setPrimaryBtn = document.createElement('button');
        setPrimaryBtn.innerHTML = '<i class="fas fa-star"></i>';
        setPrimaryBtn.className = 'btn btn-outline';
        setPrimaryBtn.style.padding = '8px';
        setPrimaryBtn.style.fontSize = '0.9rem';
        setPrimaryBtn.title = 'Establecer como principal';
        if (image.es_principal == 1) {
            setPrimaryBtn.disabled = true;
            setPrimaryBtn.style.opacity = '0.5';
        }
        setPrimaryBtn.onclick = (e) => {
            e.stopPropagation();
            setPrimaryImage(image.id, editingPropertyId);
        };
        
        const deleteBtn = document.createElement('button');
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.className = 'btn btn-outline';
        deleteBtn.style.padding = '8px';
        deleteBtn.style.fontSize = '0.9rem';
        deleteBtn.style.background = '#e53e3e';
        deleteBtn.style.color = 'white';
        deleteBtn.style.border = 'none';
        deleteBtn.title = 'Eliminar imagen';
        deleteBtn.onclick = (e) => {
            e.stopPropagation();
            deleteImage(image.id, editingPropertyId);
        };
        
        overlay.appendChild(setPrimaryBtn);
        overlay.appendChild(deleteBtn);
        
        imageCard.appendChild(img);
        imageCard.appendChild(overlay);
        
        imageCard.addEventListener('mouseenter', () => {
            overlay.style.opacity = '1';
        });
        
        imageCard.addEventListener('mouseleave', () => {
            overlay.style.opacity = '0';
        });
        
        imagesGrid.appendChild(imageCard);
    });
    
    container.appendChild(imagesGrid);
}

// Establecer imagen como principal
async function setPrimaryImage(imageId, propertyId) {
    try {
        const response = await fetch('../api/images.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                image_id: imageId,
                property_id: propertyId
            }),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Recargar la propiedad para actualizar las imágenes
            await editProperty(propertyId);
        } else {
            alert('Error al establecer imagen principal: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al establecer imagen principal');
    }
}

// Eliminar imagen
async function deleteImage(imageId, propertyId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/images.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                image_id: imageId,
                property_id: propertyId
            }),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Recargar la propiedad para actualizar las imágenes
            await editProperty(propertyId);
        } else {
            alert('Error al eliminar la imagen: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar la imagen');
    }
}

// Eliminar propiedad
async function deleteProperty(propertyId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta propiedad?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/properties.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: propertyId }),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Propiedad eliminada exitosamente');
            await loadUserProperties();
        } else {
            alert('Error al eliminar la propiedad: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar la propiedad');
    }
}

// ============================================
// MENSAJES
// ============================================

// Cargar mensajes
async function loadMessages() {
    try {
        const response = await fetch('../api/messages.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
        } else {
            document.getElementById('messagesList').innerHTML = '<p>Error al cargar mensajes.</p>';
        }
    } catch (error) {
        console.error('Error al cargar mensajes:', error);
        document.getElementById('messagesList').innerHTML = '<p>Error al cargar mensajes.</p>';
    }
}

// Mostrar mensajes
function displayMessages(messages) {
    const container = document.getElementById('messagesList');
    
    if (messages.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--gris); padding: 40px;">No tienes mensajes aún.</p>';
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 15px;">';
    messages.forEach(msg => {
        const fecha = new Date(msg.fecha_envio).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const leidoClass = msg.leido ? '' : 'unread';
        
        html += `
            <div class="message-item ${leidoClass}">
                <div class="message-header">
                    <h3>${escapeHtml(msg.nombre)}</h3>
                    <span class="message-date">${fecha}</span>
                </div>
                <p class="message-info"><strong>Propiedad:</strong> ${escapeHtml(msg.propiedad_titulo)}</p>
                <p class="message-info"><strong>Email:</strong> <a href="mailto:${escapeHtml(msg.email)}">${escapeHtml(msg.email)}</a></p>
                ${msg.telefono ? `<p class="message-info"><strong>Teléfono:</strong> <a href="tel:${escapeHtml(msg.telefono)}">${escapeHtml(msg.telefono)}</a></p>` : ''}
                <div class="message-body">
                    <p>${escapeHtml(msg.mensaje)}</p>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

// Función para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// CONFIGURACIÓN DE USUARIO
// ============================================

// Cargar configuración del usuario
async function loadUserSettings() {
    // Esperar a que currentUser esté disponible
    if (!currentUser) {
        setTimeout(loadUserSettings, 100);
        return;
    }
    
    const nombreField = document.getElementById('settingsNombre');
    const emailField = document.getElementById('settingsEmail');
    const telefonoField = document.getElementById('settingsTelefono');
    const whatsappField = document.getElementById('settingsWhatsapp');
    
    if (nombreField) nombreField.value = currentUser.nombre || '';
    if (emailField) emailField.value = currentUser.email || '';
    if (telefonoField) telefonoField.value = currentUser.telefono || '';
    if (whatsappField) whatsappField.value = currentUser.whatsapp || '';
}

// Guardar configuración
document.getElementById('settingsForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('saveSettingsBtn');
    const originalText = submitBtn.innerHTML;
    
    try {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        const settingsData = {
            nombre: document.getElementById('settingsNombre').value,
            email: document.getElementById('settingsEmail').value,
            telefono: document.getElementById('settingsTelefono').value,
            whatsapp: document.getElementById('settingsWhatsapp').value,
            password: document.getElementById('settingsPassword').value || null
        };
        
        const response = await fetch('../api/user.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(settingsData),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Configuración guardada exitosamente');
            currentUser = data.user;
            updateUserInfo();
            document.getElementById('settingsPassword').value = '';
        } else {
            alert('Error al guardar: ' + (data.message || 'Intenta nuevamente'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar la configuración');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// handleLogout está definido en common.js

