document.addEventListener('DOMContentLoaded', () => {
    const direccionesContainer = document.getElementById('direcciones-container');
    const addDireccionBtn = document.getElementById('add-direccion');
    const editButton = document.getElementById('edit-button');
    const deleteButton = document.getElementById('delete-button');
    const responseMessage = document.getElementById('response-message');
    let direccionCount = 1;

    const apiUrl = 'http://localhost/emarket/backend/public/usuarios/perfil';
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'http://localhost/emarket/frontend/login.html';
        return;
    }

    // Función para agregar un nuevo bloque de dirección
    const createDireccionBlock = (index) => {
        const newDireccionItem = document.createElement('div');
        newDireccionItem.className = 'direccion-item';
        newDireccionItem.setAttribute('data-index', index);
        newDireccionItem.innerHTML = `
            <h3>Dirección ${index + 1}</h3>
            <div class="direccion-campos">
                <div class="form-sub-group">
                    <label>Departamento:</label>
                    <input type="text" name="direccion[${index}][departamento]" class="direccion-input">
                </div>
                <div class="form-sub-group">
                    <label>Provincia:</label>
                    <input type="text" name="direccion[${index}][provincia]" class="direccion-input">
                </div>
                <div class="form-sub-group">
                    <label>Ciudad:</label>
                    <input type="text" name="direccion[${index}][ciudad]" class="direccion-input">
                </div>
                <div class="form-sub-group">
                    <label>Zona:</label>
                    <input type="text" name="direccion[${index}][zona]" class="direccion-input">
                </div>
                <div class="form-sub-group">
                    <label>Calle:</label>
                    <input type="text" name="direccion[${index}][calle]" class="direccion-input">
                </div>
                <div class="form-sub-group">
                    <label>Número:</label>
                    <input type="text" name="direccion[${index}][numero]" class="direccion-input">
                </div>
                <div class="form-sub-group">
                    <label>Referencias:</label>
                    <input type="text" name="direccion[${index}][referencias]" class="direccion-input">
                </div>
            </div>
            <div class="principal-option">
                <input type="radio" name="direccion_principal" value="${index}"> Principal
            </div>
        `;
        direccionesContainer.appendChild(newDireccionItem);
    };

    addDireccionBtn.addEventListener('click', () => {
        createDireccionBlock(direccionCount);
        direccionCount++;
    });

    // Función para mostrar mensajes de respuesta
    const showMessage = (message, type) => {
        responseMessage.textContent = message;
        responseMessage.className = `response-message ${type}`;
        responseMessage.style.display = 'block';
    };

    // Función para editar perfil
    const editarPerfil = async () => {
        const form = document.getElementById('user-profile-form');
        const data = {};
        const direcciones = [];
        let principalIndex = -1;

        // Recolectar datos de campos directos
        data.nombres = form.elements.nombres.value;
        data.apellidos = form.elements.apellidos.value;
        data.email = form.elements.email.value;
        data.telefono = form.elements.telefono.value;
        data.ci_nit = form.elements.ci_nit.value;

        // Recolectar datos de las direcciones
        const direccionItems = document.querySelectorAll('.direccion-item');
        direccionItems.forEach((item, index) => {
            const direccion = {};
            const inputs = item.querySelectorAll('input[type="text"]');
            inputs.forEach(input => {
                const nameMatch = input.name.match(/\[(\w+)\]$/);
                if (nameMatch) {
                    const field = nameMatch[1];
                    direccion[field] = input.value;
                }
            });
            direcciones.push(direccion);
        });

        // Obtener el índice de la dirección principal seleccionada
        const selectedRadio = document.querySelector('input[name="direccion_principal"]:checked');
        if (selectedRadio) {
            principalIndex = parseInt(selectedRadio.value);
        }

        // Asignar las direcciones y marcar la principal dentro del array
        data.direcciones = direcciones.map((dir, index) => {
            if (index === principalIndex) {
                return { ...dir, principal: true };
            }
            return dir;
        });
        console.log('Datos a enviar:', data);
        console.log("=== DATOS JSON ===");
        console.log(JSON.stringify(data, null, 2));
        console.log("===================================");

        try {
            const response = await fetch(apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(data)
            });
            console.log('Datos a enviar:', data);
            console.log("=== DATOS RECIBIDOS ===");
            console.log(JSON.stringify(data, null, 2));
            console.log("===================================");
            console.log('Response status:', response.status);
            console.log('Response headers:', [...response.headers.entries()]);

            // Verificar si la respuesta es OK
            if (!response.ok) {
                // Intentar obtener el cuerpo de la respuesta incluso en errores
                let errorText = '';
                try {
                    const errorResult = await response.json();
                    errorText = errorResult.mensaje || errorResult.message || errorResult.error || `Error ${response.status}`;
                } catch {
                    errorText = `HTTP ${response.status}: ${response.statusText}`;
                }
                throw new Error(errorText);
            }


            if (response.ok) {
                const result = await response.json();
                console.log('Respuesta de la API:', result);
                showMessage('Usuario Registrado con éxito. ¡Conexión con la API establecida! 🎉', 'success');
            } else {
                throw new Error('Error al registrar el usuario.');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Hubo un error al conectar con la API. 🙁', 'error');
        }
    };

    async function eliminarPerfil() {
        if (!confirm('¿Seguro que quieres eliminar tu perfil?')) return;
        try {
            const response = await fetch(apiUrl, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            console.log('Response status:', response.status);
            console.log('Response headers:', [...response.headers.entries()]);

            // Verificar si la respuesta es OK
            if (!response.ok) {
                // Intentar obtener el cuerpo de la respuesta incluso en errores
                let errorText = '';
                try {
                    const errorResult = await response.json();
                    errorText = errorResult.mensaje || errorResult.message || errorResult.error || `Error ${response.status}`;
                } catch {
                    errorText = `HTTP ${response.status}: ${response.statusText}`;
                }
                throw new Error(errorText);
            }
            alert('Perfil eliminado correctamente.');
            localStorage.removeItem('token');
            window.location.href = '/emarket/frontend/login.html';
        } catch (error) {
            document.getElementById('error').textContent = `Error al eliminar: ${error.message}`;
        }
    }

    const mostrarPerfil = (usuario) => {
        // Rellenar datos personales
        document.getElementById('nombres').value = usuario.nombres || '';
        document.getElementById('apellidos').value = usuario.apellidos || '';
        document.getElementById('email').value = usuario.email || '';
        document.getElementById('telefono').value = usuario.telefono || '';
        document.getElementById('ci_nit').value = usuario.ci_nit || '';

        // Limpiar contenedor de direcciones
        direccionesContainer.innerHTML = '';
        direccionCount = 0;

        // Rellenar direcciones
        const direcciones = usuario.direcciones || [];
        direcciones.forEach((dir, index) => {
            createDireccionBlock(index); // Crea el bloque visual
            direccionCount++;

            const bloque = direccionesContainer.querySelector(`.direccion-item[data-index="${index}"]`);
            if (!bloque) return;

            bloque.querySelector(`input[name="direccion[${index}][departamento]"]`).value = dir.departamento || '';
            bloque.querySelector(`input[name="direccion[${index}][provincia]"]`).value = dir.provincia || '';
            bloque.querySelector(`input[name="direccion[${index}][ciudad]"]`).value = dir.ciudad || '';
            bloque.querySelector(`input[name="direccion[${index}][zona]"]`).value = dir.zona || '';
            bloque.querySelector(`input[name="direccion[${index}][calle]"]`).value = dir.calle || '';
            bloque.querySelector(`input[name="direccion[${index}][numero]"]`).value = dir.numero || '';
            bloque.querySelector(`input[name="direccion[${index}][referencias]"]`).value = dir.referencias || '';

            // Marcar como principal si coincide con id_direccion_principal
            if (usuario.id_direccion_principal && usuario.id_direccion_principal === dir.id_direccion) {
                bloque.querySelector(`input[type="radio"][name="direccion_principal"]`).checked = true;
            }
        });
    };

    async function cargarPerfil() {
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
            .then(res => res.json())
            .then(data => {
                console.log("=== DATOS RECIBIDOS ===");
                console.log(JSON.stringify(data, null, 2));
                console.log("===================================");
                if (data.status === 'success') {
                    mostrarPerfil(data.data);
                } else {
                    showMessage('Error al cargar perfil', 'error');
                }
            })
            .catch(err => {
                console.error('Error al conectar con la API:', err);
                showMessage('Error de conexión', 'error');
            });
    }
    window.onload = function () {
        cargarPerfil();
    };

    // Asignar los event listeners a los botones
    editButton.addEventListener('click', editarPerfil);
    deleteButton.addEventListener('click', eliminarPerfil);

});