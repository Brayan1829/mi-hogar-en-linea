// Variables globales
let userProperties = [];

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initTabSecurity();
    checkAuth();
    setupEventListeners();
    loadUserProperties();
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
    // El botón de cerrar sesión está manejado en common.js
}

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
        } else {
            document.getElementById('userPropertiesList').innerHTML = '<p style="text-align: center; padding: 40px; color: var(--gris);">Error al cargar las propiedades</p>';
        }
    } catch (error) {
        console.error('Error al cargar propiedades:', error);
        document.getElementById('userPropertiesList').innerHTML = '<p style="text-align: center; padding: 40px; color: var(--gris);">Error al cargar las propiedades</p>';
    }
}

// Actualizar UI de propiedades
function updatePropertiesUI() {
    const container = document.getElementById('userPropertiesList');
    
    if (userProperties.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 60px;">
                <i class="fas fa-home" style="font-size: 4rem; color: var(--gris); margin-bottom: 20px;"></i>
                <h3 style="color: var(--gris-oscuro); margin-bottom: 10px;">No tienes propiedades publicadas</h3>
                <p style="color: var(--gris); margin-bottom: 30px;">Comienza publicando tu primera propiedad</p>
                <a href="publicar-propiedad.php" class="btn btn-primario"><i class="fas fa-plus"></i> Publicar Propiedad</a>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    userProperties.forEach(property => {
        const card = createPropertyCard(property);
        container.appendChild(card);
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
        </div>
    `;
    
    // Agregar event listeners
    const editBtn = card.querySelector('.property-action .fa-edit').closest('.property-action');
    const deleteBtn = card.querySelector('.property-action .fa-trash').closest('.property-action');
    
    editBtn.addEventListener('click', () => {
        window.location.href = `publicar-propiedad.php?edit=${property.id}`;
    });
    
    deleteBtn.addEventListener('click', () => {
        deleteProperty(property.id);
    });
    
    return card;
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

// handleLogout está definido en common.js

