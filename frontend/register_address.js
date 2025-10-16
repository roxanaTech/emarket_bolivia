document.addEventListener('DOMContentLoaded', () => {
    const direccionesContainer = document.getElementById('direcciones-container');
    const addDireccionBtn = document.getElementById('add-direccion');
    const form = document.getElementById('registerForm');
    const responseMessage = document.getElementById('response-message');
    let direccionCount = 1; // ya existe la primera dirección (index 0)

    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const nextStepBtn = document.getElementById('nextStep');
    const prevStepBtn = document.getElementById('prevStep');

    const createDireccionBlock = (index) => {
        const newDireccionItem = document.createElement('div');
        newDireccionItem.className = 'direccion-item mb-3';
        newDireccionItem.setAttribute('data-index', index);
        newDireccionItem.innerHTML = `
            <h6>Dirección ${index + 1}</h6>
            <div class="row g-2">
                <div class="col-md-6"><input type="text" name="direccion[${index}][departamento]" class="form-control" placeholder="Departamento"></div>
                <div class="col-md-6"><input type="text" name="direccion[${index}][provincia]" class="form-control" placeholder="Provincia"></div>
                <div class="col-md-6"><input type="text" name="direccion[${index}][ciudad]" class="form-control" placeholder="Ciudad"></div>
                <div class="col-md-6"><input type="text" name="direccion[${index}][zona]" class="form-control" placeholder="Zona"></div>
                <div class="col-md-6"><input type="text" name="direccion[${index}][calle]" class="form-control" placeholder="Calle"></div>
                <div class="col-md-3"><input type="text" name="direccion[${index}][numero]" class="form-control" placeholder="Número"></div>
                <div class="col-md-9"><input type="text" name="direccion[${index}][referencias]" class="form-control" placeholder="Referencias"></div>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="direccion_principal" value="${index}" ${index===0 ? 'checked' : ''}>
                <label class="form-check-label">Principal</label>
            </div>
        `;
        direccionesContainer.appendChild(newDireccionItem);
    };

    addDireccionBtn.addEventListener('click', () => {
        createDireccionBlock(direccionCount);
        direccionCount++;
    });

    const showMessage = (message, type) => {
        if(!responseMessage) return;
        responseMessage.textContent = message;
        responseMessage.className = `response-message ${type}`;
        responseMessage.style.display = 'block';
    };

    const handleUserRegistration = async (event) => {
        event.preventDefault(); // <- evita recarga
        const data = {};
        const direcciones = [];
        let principalIndex = -1;

        data.tipo_vendedor = form.elements.tipo_vendedor.value;
        data.cuenta_bancaria = form.elements.cuenta_bancaria.value;
        data.razon_social = form.elements.razon_social.value;
        data.nit = form.elements.nit.value;
        data.matricula_comercial = form.elements.matricula_comercial.value;
        data.correo_comercial = form.elements.correo_comercial.value;
        data.telefono_comercial = form.elements.telefono_comercial.value;

        document.querySelectorAll('.direccion-item').forEach((item, index) => {
            const dir = {};
            item.querySelectorAll('input[type="text"]').forEach(input => {
                const field = input.name.match(/\[(\w+)\]$/)[1];
                dir[field] = input.value;
            });
            direcciones.push(dir);
        });

        const selectedRadio = document.querySelector('input[name="direccion_principal"]:checked');
        if(selectedRadio) principalIndex = parseInt(selectedRadio.value);

        data.direcciones = direcciones.map((dir, index) => index === principalIndex ? {...dir, principal:true} : dir);

        try {
            const response = await fetch('http://localhost/emarket_bolivia/backend/public/usuarios/registro', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(data)
            });
            if(!response.ok) throw new Error(`Error ${response.status}`);
            const result = await response.json();
            showMessage('Usuario registrado con éxito 🎉', 'success');
            console.log(result);
        } catch(error){
            console.error(error);
            showMessage('Error al conectar con la API 🙁', 'error');
        }
    };

    form.addEventListener('submit', handleUserRegistration);

    nextStepBtn.addEventListener('click', () => { step1.style.display='none'; step2.style.display='block'; });
    prevStepBtn.addEventListener('click', () => { step2.style.display='none'; step1.style.display='block'; });

});
