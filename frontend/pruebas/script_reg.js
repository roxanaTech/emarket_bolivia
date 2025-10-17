document.addEventListener('DOMContentLoaded', () => {
    const direccionesContainer = document.getElementById('direcciones-container');
    const addDireccionBtn = document.getElementById('add-direccion');
    const editButton = document.getElementById('edit-button');
    const cancelarButton = document.getElementById('delete-button');
    const responseMessage = document.getElementById('response-message');
    let direccionCount = 1;

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

    // Función para el registro de usuario
    const handleUserRegistration = async () => {
        const form = document.getElementById('user-register');
        const data = {};
        const direcciones = [];
        let principalIndex = -1;

        // Recolectar datos de campos directos
        data.nombres = form.elements.nombres.value;
        data.apellidos = form.elements.apellidos.value;
        data.email = form.elements.email.value;
        data.password = form.elements.password.value;
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

        const apiUrl = 'http://localhost/emarket_bolivia/backend/public/usuarios/registro';

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
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


    const handleCancel = async () => {
        var confirmacion = confirm("¿Está seguro de que desea cancelar la operación?");

        if (confirmacion) {
            window.location.href = '/emarket_bolivia/backend/public/login.html';
        }
    };

    // Asignar los event listeners a los botones
    editButton.addEventListener('click', handleUserRegistration);
    cancelarButton.addEventListener('click', handleCancel);
    // Función para probar la conexión con el servidor
    async function testConnection() {
        try {
            const response = await fetch('http://localhost/emarket_bolivia/backend/public/status');
            const result = await response.json();
            console.log('Test de conexión exitoso:', result);
        } catch (error) {
            console.error('Test de conexión falló:', error);
        }
    }

    // Ejecutar test de conexión al cargar la página
    window.addEventListener('load', testConnection);
});