// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    initTabSecurity();
    checkAuth();
    setupEventListeners();
    loadUserSettings();
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
    const form = document.getElementById('settingsForm');
    if (form) {
        form.addEventListener('submit', handleSettingsSubmit);
    }
    
    // El botón de cerrar sesión está manejado en common.js
}

// Cargar configuración del usuario
async function loadUserSettings() {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success && data.user) {
            const user = data.user;
            document.getElementById('settingsNombre').value = user.nombre || '';
            document.getElementById('settingsEmail').value = user.email || '';
            document.getElementById('settingsTelefono').value = user.telefono || '';
            document.getElementById('settingsWhatsapp').value = user.whatsapp || '';
        }
    } catch (error) {
        console.error('Error al cargar configuración:', error);
    }
}

// Manejar envío del formulario de configuración
async function handleSettingsSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('saveSettingsBtn');
    const originalText = submitBtn.innerHTML;
    
    try {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        const userData = {
            nombre: document.getElementById('settingsNombre').value,
            email: document.getElementById('settingsEmail').value,
            telefono: document.getElementById('settingsTelefono').value,
            whatsapp: document.getElementById('settingsWhatsapp').value
        };
        
        const password = document.getElementById('settingsPassword').value;
        if (password && password.length > 0) {
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }
            userData.password = password;
        }
        
        const response = await fetch('../api/user.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Configuración actualizada exitosamente');
        } else {
            alert('Error al actualizar la configuración: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al actualizar la configuración');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// handleLogout está definido en common.js

