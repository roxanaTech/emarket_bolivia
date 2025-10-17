// Variables para datos

let currentUser = { nombres: '', email: '', telefono: '' };
let updateOnlyTexts = false;

// Función helper para auth headers
function getAuthHeaders() {
    const token = localStorage.getItem('token');
    console.log('[DEBUG] Token recuperado del localStorage:', token ? '✅ presente' : '❌ ausente');
    return {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    };
}

// Función para logout en error auth
function handleAuthError() {
    console.warn('[AUTH ERROR] Sesión no válida o expirada. Eliminando token...');
    localStorage.removeItem('token');
    alert('Sesión expirada. Redirigiendo al login.');
    window.location.href = '/emarket_bolivia/frontend/login.php';
}

// Función para cargar perfil 
function loadProfile(updateOnlyTexts = true) {
    updateOnlyTexts = true;
    console.log('[PERFIL] Iniciando carga de perfil. updateOnlyTexts:', updateOnlyTexts);

    fetch(`${apiUrl}/usuarios/perfil`, {
        method: 'GET',
        headers: getAuthHeaders()
    })
        .then(response => {
            console.log('[PERFIL] Respuesta recibida. Status:', response.status);
            if (!response.ok) {
                if (response.status === 401) {
                    console.warn('[PERFIL] Error 401: No autorizado');
                    handleAuthError();
                }
                throw new Error('Error al obtener perfil: ' + response.statusText + ' (Status: ' + response.status + ')');
            }
            return response.json();
        })
        .then(data => {
            console.log('[PERFIL] Datos recibidos del backend:', data);
            if (data.status === 'success') {
                const user = data.data;
                console.log('[PERFIL] Usuario cargado:', user);

                // Actualizar textos display
                document.getElementById('user-name').textContent = user.nombres;
                document.getElementById('user-email-display').textContent = user.email;

                // Actualizar inputs
                document.getElementById('email').value = user.email;
                document.getElementById('phone').value = user.telefono;
                document.getElementById('password').value = '••••••••';

                const nombresInput = document.getElementById('nombres');
                if (nombresInput) nombresInput.value = user.nombres;

                currentUser = { nombres: user.nombres, email: user.email, telefono: user.telefono };
                console.log("uodtae ", updateOnlyTexts);
                if (updateOnlyTexts) {
                    let imgUrl = user.imagen || '/default-avatar.png';
                    if (!imgUrl.startsWith('http')) {
                        imgUrl = apiUrl + imgUrl;
                    }
                    console.log("imagen ", imgUrl);
                    const profileDiv = document.getElementById('profile-image-div');
                    profileDiv.style.backgroundImage = `url(${imgUrl})`;

                    const previewImg = document.getElementById('profile-preview');
                    if (previewImg) {
                        previewImg.src = imgUrl;
                        previewImg.onerror = () => {
                            console.warn('[IMAGEN] Error al cargar imagen. Usando fallback.');
                            previewImg.src = '/default-avatar.png';
                        };
                    }
                }

                console.log('[PERFIL] ✅ Perfil cargado y renderizado correctamente.');
            } else {
                console.error('[PERFIL] Respuesta sin éxito:', data);
                throw new Error(data.mensaje || 'Respuesta sin estado "success"');
            }
        })
        .catch(error => {
            console.error('[PERFIL] ❌ Error crítico al cargar perfil:', error);
        });
}

function startEditing() {
    Alpine.store('profile').editing = true;
    console.log('[EDITAR] Modo edición activado');
}

function saveProfile() {
    // Activar loading al inicio
    Alpine.store('profile').loading = true;

    const nombres = document.getElementById('nombres').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefono = document.getElementById('phone').value.trim();

    // Validación mejorada
    if (!nombres || nombres.length < 2) {
        alert('Nombre debe tener al menos 2 caracteres');
        Alpine.store('profile').loading = false;
        return;
    }
    if (!email.includes('@') || !email.includes('.')) {
        alert('Email inválido');
        Alpine.store('profile').loading = false;
        return;
    }
    if (!/^\d{7,15}$/.test(telefono)) {
        alert('Teléfono inválido (solo números, 7-15 dígitos)');
        Alpine.store('profile').loading = false;
        return;
    }

    const payload = { nombres, email, telefono };

    fetch(`${apiUrl}/usuarios/perfil`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) handleAuthError();
                throw new Error('Error al guardar: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Perfil actualizado exitosamente');
                Alpine.store('profile').editing = false;
                if (data.data) currentUser = { ...data.data };
                loadProfile(true);
            } else {
                throw new Error(data.mensaje || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar: ' + error.message);
        })
        .finally(() => {
            Alpine.store('profile').loading = false; // Ya está, pero las validaciones también lo necesitan
        });
}

function cancelEdit() {
    console.log('[EDITAR] Cancelando edición. Restaurando valores originales...');
    const nombresInput = document.getElementById('nombres');
    if (nombresInput) nombresInput.value = currentUser.nombres;
    document.getElementById('email').value = currentUser.email;
    document.getElementById('phone').value = currentUser.telefono;

    Alpine.store('profile').editing = false;
    loadProfile(true);
}
// Función para eliminar la cuenta de usuario
function deleteAccount() {
    if (!confirm('¿Estás absolutamente seguro? Esta acción es irreversible.')) {
        return;
    }

    fetch(`${apiUrl}/usuarios/perfil`, {
        method: 'DELETE',
        headers: getAuthHeaders()
    })
        .then(response => {
            console.log('[ELIMINAR CUENTA] Respuesta del servidor. Status:', response.status);
            if (!response.ok) {
                if (response.status === 401) {
                    handleAuthError();
                }
                throw new Error('Error al eliminar la cuenta: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('[ELIMINAR CUENTA] Respuesta del backend:', data);
            if (data.status === 'success') {
                alert('Tu cuenta ha sido eliminada exitosamente.');
                localStorage.removeItem('token');
                window.location.href = '/emarket_bolivia/frontend/principal.php';
            } else {
                throw new Error(data.mensaje || 'Error desconocido al eliminar la cuenta');
            }
        })
        .catch(error => {
            console.error('[ELIMINAR CUENTA] ❌ Error:', error);
            alert('No se pudo eliminar la cuenta: ' + error.message);
        })
        .finally(() => {
            // Cierra el modal incluso si falla
            Alpine.store('profile').loading = false;
            // Pero como usamos x-data global, no hay store para deleteModalOpen
            // Así que lo cerramos manualmente si usas Alpine 3+
            // Alternativa: usar una variable global o mejor aún, usar x-data en el scope correcto
        });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function () {
    console.log('[INICIO] DOM cargado. Verificando autenticación...');
    document.getElementById('btn-cambiar-contrasena').addEventListener('click', cambiarContrasena);

    const token = localStorage.getItem('token');
    if (!token) {
        console.warn('[INICIO] ❌ Token no encontrado. Redirigiendo a login.');
        alert('No autorizado. Redirigiendo al login.');
        window.location.href = '/login';
        return;
    }

    console.log('[INICIO] ✅ Token presente. Inicializando Alpine y cargando perfil...');

    Alpine.store('profile', { editing: false, loading: false });

    loadProfile(false);

    // Photo upload handlers
    const triggerBtn = document.getElementById('trigger-file-upload');
    const fileInput = document.getElementById('file-upload-input');
    const previewImg = document.getElementById('profile-preview');
    const formUpload = document.getElementById('form-upload-photo');
    const deleteBtn = document.getElementById('eliminar-foto');

    if (triggerBtn && fileInput) {
        triggerBtn.addEventListener('click', () => {
            console.log('[FOTO] Botón de subida clickeado. Abriendo selector de archivos...');
            fileInput.click();
        });
    }

    if (fileInput && previewImg) {
        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/') && file.size < 5 * 1024 * 1024) {
                console.log('[FOTO] Archivo válido seleccionado:', file.name, file.type, file.size + ' bytes');
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    console.log('[FOTO] Vista previa generada desde FileReader');
                };
                reader.readAsDataURL(file);
            } else {
                console.warn('[FOTO] Archivo inválido:', file ? file.name : 'ninguno');
                alert('Archivo inválido: Solo imágenes JPG/PNG <5MB');
                fileInput.value = '';
            }
        });
    }
    if (formUpload) {
        formUpload.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formUpload);

            // 👇 DEBUG: Loguea el contenido del FormData
            console.log('[FOTO DEBUG] Contenido de FormData:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(key, value.name, value.size, value.type);
                } else {
                    console.log(key, value);
                }
            }

            const token = localStorage.getItem('token');

            if (!token) {
                alert('Debes iniciar sesión.');
                return;
            }

            try {
                const res = await fetch(`${apiUrl}/usuarios/imagen`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.status === 'success') {
                    alert('Foto actualizada');
                    loadProfile(false);
                    Alpine.store('profile').modalOpen = false;
                } else {
                    throw new Error(data.mensaje || 'Error al subir foto');
                }
            } catch (error) {
                console.error('[FOTO UPLOAD] ❌ Error:', error);
                alert('Error: ' + error.message);
            }
        });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (confirm('¿Eliminar foto de perfil?')) {
                console.log('[FOTO DELETE] Confirmando eliminación de foto...');
                try {
                    const res = await fetch(`${apiUrl}/usuarios/imagen`, {
                        method: 'DELETE',
                        headers: getAuthHeaders()
                    });
                    console.log(res);
                    console.log('[FOTO DELETE] Respuesta recibida. Status:', res.status);
                    if (!res.ok) throw new Error('Error al eliminar (Status: ' + res.status + ')');
                    const data = await res.json();
                    console.log('[FOTO DELETE] Respuesta del backend:', data);
                    if (data.status === 'success') {
                        alert('Foto eliminada');
                        loadProfile(false);
                        console.log('[FOTO DELETE] ✅ Foto eliminada y perfil recargado.');
                    } else {
                        throw new Error(data.mensaje || 'Respuesta sin éxito');
                    }
                } catch (error) {
                    console.error('[FOTO DELETE] ❌ Error al eliminar foto:', error);
                    alert('Error al eliminar: ' + error.message);
                }
            }
        });
    }

    // Tabs handler
    const tabs = document.querySelectorAll('#sidebar-nav a[data-tab]');
    const tabContents = document.querySelectorAll('[data-tab-content]');
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.dataset.tab;
            console.log('[TABS] Cambiando a pestaña:', target);

            tabs.forEach(t => t.classList.remove('active-nav'));
            tab.classList.add('active-nav');

            tabContents.forEach(content => {
                content.classList.toggle('active', content.id === `${target}-content`);
            });
        });
    });

    const initialTab = document.querySelector('#sidebar-nav a[data-tab="cuenta"]');
    if (initialTab) {
        console.log('[TABS] Activando pestaña inicial: cuenta');
        initialTab.click();
    }
    // Handler para cerrar sesión
    const logoutBtn = document.getElementById('logout-btn');  // Si usas el ID del botón
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();  // Evita cualquier acción default
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                localStorage.removeItem('token');  // Elimina el token
                console.log('Sesión cerrada');
                alert('Sesión cerrada exitosamente. ¡Hasta pronto!');
                window.location.href = '/emarket_bolivia/frontend/principal.php';  // Redirige al login (ajusta la ruta)
            }
        });
    }
    async function cambiarContrasena() {
        const actual = document.getElementById('current_password').value;
        const nueva = document.getElementById('new_password').value;
        const confirmar = document.getElementById('confirm_password').value;

        if (nueva !== confirmar) {
            alert('Las nuevas contraseñas no coinciden');
            return;
        }

        try {
            const res = await fetch(`${apiUrl}/usuarios/perfil/contrasena`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({
                    contrasena_actual: actual,
                    nueva_contrasena: nueva,
                    confirmar_contrasena: confirmar
                })
            });

            const data = await res.json();
            if (res.ok) {
                alert('✅ Contraseña actualizada');
                // Opcional: limpiar campos
                document.getElementById('cambiar-password-form').reset();
            } else {
                alert('❌ ' + (data.mensaje || 'Error al cambiar la contraseña'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de red al cambiar la contraseña');
        }
    }
});