// Funciones comunes para todas las páginas del admin
let currentUser = null;

// Cargar información del usuario
async function loadUserInfo() {
    try {
        const response = await fetch('../api/auth.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success && data.user) {
            currentUser = data.user;
            updateUserInfo();
            return true;
        } else {
            return false;
        }
    } catch (error) {
        console.error('Error al cargar información del usuario:', error);
        return false;
    }
}

// Actualizar información del usuario en la UI
function updateUserInfo() {
    const userNameEl = document.getElementById('userName');
    const userAvatarEl = document.getElementById('userAvatar');
    
    if (userNameEl && currentUser) {
        userNameEl.textContent = currentUser.nombre || 'Usuario';
    }
    
    if (userAvatarEl && currentUser) {
        if (currentUser.avatar) {
            userAvatarEl.style.backgroundImage = `url(${currentUser.avatar})`;
            userAvatarEl.textContent = '';
        } else {
            userAvatarEl.textContent = (currentUser.nombre || 'U').charAt(0).toUpperCase();
        }
    }
}

// Cerrar sesión
async function handleLogout() {
    if (!confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/auth.php', {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '../index.php';
        } else {
            alert('Error al cerrar sesión');
        }
    } catch (error) {
        console.error('Error:', error);
        window.location.href = '../index.php';
    }
}

// Inicializar información del usuario cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    loadUserInfo();
    
    // Configurar botón de cerrar sesión
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleLogout();
        });
    }
});

