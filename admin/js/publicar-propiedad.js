// Variables globales
let editingPropertyId = null;
let existingImages = [];

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initTabSecurity();
    checkAuth();
    setupEventListeners();
    
    // Verificar si hay un ID en la URL para editar
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    if (editId) {
        editProperty(editId);
    }
});

// Sistema de seguridad de sesión única
function initTabSecurity() {
    const tabId = 'tab_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    const broadcastChannel = new BroadcastChannel('dashboard_session');
    
    let isNewTab = false;
    
    broadcastChannel.onmessage = function(event) {
        if (event.data.type === 'tab_heartbeat') {
            if (event.data.tabId !== tabId) {
                isNewTab = true;
                handleSessionClose();
            }
        } else if (event.data.type === 'new_tab_opened') {
            if (event.data.tabId !== tabId) {
                const otherTimestamp = event.data.timestamp;
                const thisTimestamp = Date.now();
                if (thisTimestamp > otherTimestamp) {
                    isNewTab = true;
                    handleSessionClose();
                }
            }
        }
    };
    
    const storedTabId = sessionStorage.getItem('dashboard_tab_id');
    const storedTimestamp = sessionStorage.getItem('dashboard_tab_timestamp');
    
    if (storedTabId && storedTabId !== tabId && storedTimestamp) {
        const storedTime = parseInt(storedTimestamp);
        const currentTime = Date.now();
        if (currentTime - storedTime > 100) {
            isNewTab = true;
            setTimeout(function() {
                handleSessionClose();
            }, 100);
            return;
        }
    }
    
    sessionStorage.setItem('dashboard_tab_id', tabId);
    sessionStorage.setItem('dashboard_tab_timestamp', Date.now().toString());
    
    broadcastChannel.postMessage({
        type: 'new_tab_opened',
        tabId: tabId,
        timestamp: Date.now()
    });
    
    setInterval(function() {
        if (!isNewTab) {
            broadcastChannel.postMessage({
                type: 'tab_heartbeat',
                tabId: tabId,
                timestamp: Date.now()
            });
        }
    }, 2000);
    
    window.addEventListener('beforeunload', function() {
        sessionStorage.removeItem('dashboard_tab_id');
        sessionStorage.removeItem('dashboard_tab_timestamp');
    });
}

async function handleSessionClose() {
    try {
        await fetch('../api/auth.php', {
            method: 'DELETE',
            credentials: 'include'
        });
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    }
    alert('Por seguridad, solo puedes tener una sesión activa a la vez. Esta pestaña será cerrada.');
    window.location.href = '../login.php';
}

// Verificar autenticación
async function checkAuth() {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (!data.success) {
            window.location.href = '../login.php';
        } else {
            // Actualizar información del usuario si está disponible
            if (data.user && typeof updateUserInfo === 'function') {
                currentUser = data.user;
                updateUserInfo();
            }
        }
    } catch (error) {
        console.error('Error al verificar autenticación:', error);
        window.location.href = '../login.php';
    }
}

// Configurar event listeners
function setupEventListeners() {
    // Formulario de propiedad
    const form = document.getElementById('propertyForm');
    if (form) {
        form.addEventListener('submit', handlePropertySubmit);
    }
    
    // Subida de archivos
    setupFileUpload();
    
    // El botón de cerrar sesión está manejado en common.js
}

// Manejar envío del formulario
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
            window.location.href = 'mis-propiedades.php';
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Subir imágenes
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
    
    if (!fileUploadArea || !fileInput || !filePreview) return;
    
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

// Editar propiedad
async function editProperty(propertyId) {
    try {
        const response = await fetch(`../api/properties.php?id=${propertyId}`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success && data.property) {
            const prop = data.property;
            editingPropertyId = propertyId;
            existingImages = prop.imagenes || [];
            
            // Llenar el formulario
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
            
            // Cambiar título y botón
            const formHeader = document.querySelector('.form-header h3');
            if (formHeader) {
                formHeader.innerHTML = '<i class="fas fa-edit"></i> Editar Propiedad';
            }
            document.getElementById('publishPropertyBtn').innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
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
            await editProperty(propertyId);
        } else {
            alert('Error al eliminar la imagen: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar la imagen');
    }
}

// handleLogout está definido en common.js

