<?php require('header.php'); ?>

<style>
  /* ==== General Reset ==== */
  body {
    background-color: #f8f9fa;
  }

  /* ==== Imagen lateral fija ==== */
  .side-image {
    position: sticky;
    top: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    /* ocupa toda la altura visible */
    background: #fff;
  }

  .side-image img {
    width: 90%;
    height: auto;
    object-fit: contain;
  }

  /* ==== Formulario ==== */
  .register-container {
    max-width: 650px;
    width: 100%;
    background: #fff;
    border-radius: 12px;
  }

  .subtitle {
    color: #02187D;
    font-weight: bold;
  }

  /* ==== Botones personalizados ==== */
  .btn-primary-custom {
    background-color: #02187D;
    border: none;
    color: #fff;
  }

  .btn-primary-custom:hover {
    background-color: #01125A;
  }

  .btn-warning {
    background-color: #FEBD69;
    border: none;
    color: #000;
  }

  .btn-warning:hover {
    background-color: #e4a94e;
  }

  .btn-danger {
    background-color: #F40009;
    border: none;
  }

  .btn-success {
    background-color: #1DB954;
    border: none;
  }

  /* ==== Direcciones ==== */
  #direcciones-container {
    max-height: 350px;
    overflow-y: auto;
  }

  .direccion-item h6 {
    color: #02187D;
  }
</style>

<div class="container-fluid">
  <div class="row min-vh-100">
    <!-- Imagen fija -->
    <div class="col-12 col-md-6 side-image">
      <img src="register_sales.png" alt="logo" class="img-fluid">
    </div>

    <!-- Formulario con scroll independiente -->
    <div class="col-12 col-md-6 d-flex justify-content-center align-items-center py-4">
      <div class="register-container p-3 p-md-4 shadow">
        <div class="subtitle h4 mb-4 text-center">
          Crea tu cuenta de vendedor y empieza a vender
        </div>

        <form id="registerForm">
          <!-- Paso 1 -->
          <div id="step1">
            <div class="row g-3">
              <div class="col-md-6">
                <input type="text" id="cuenta_bancaria" name="cuenta_bancaria" class="form-control" placeholder="Cuenta Bancaria" required>
              </div>
              <div class="col-md-6">
                <input type="text" id="razon_social" name="razon_social" class="form-control" placeholder="Razón Social" required>
              </div>
              <div class="col-md-6">
                <input type="text" id="nit" name="nit" class="form-control" placeholder="NIT/CI" required>
              </div>
              <div class="col-md-6">
                <input type="text" name="matricula_comercial" id="matricula_comercial" class="form-control" placeholder="Matrícula comercial">
              </div>
              <div class="col-md-6">
                <input type="email" name="correo_comercial" id="correo_comercial" class="form-control" placeholder="Correo Comercial" required>
              </div>
              <div class="col-md-6">
                <input type="text" name="telefono_comercial" id="telefono_comercial" class="form-control" placeholder="Teléfono Comercial" required>
              </div>
              <div class="col-md-6">
                <select name="tipo_vendedor" id="tipo_vendedor" class="form-select" required>
                  <option value="">Tipo de vendedor</option>
                  <option value="individual">Particular</option>
                  <option value="empresa">Empresa</option>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
              <button type="button" id="nextStep" class="btn btn-warning">Siguiente</button>
            </div>
          </div>

          <!-- Paso 2 -->
          <div id="step2" style="display:none;">
            <div class="mt-4">
              <label class="fw-bold">Direcciones:</label>
              <div id="direcciones-container" class="border p-3 rounded">
                <div class="direccion-item mb-3" data-index="0">
                  <h6>Dirección 1</h6>
                  <div class="row g-2">
                    <div class="col-md-6"><input type="text" name="direccion[0][departamento]" class="form-control" placeholder="Departamento"></div>
                    <div class="col-md-6"><input type="text" name="direccion[0][provincia]" class="form-control" placeholder="Provincia"></div>
                    <div class="col-md-6"><input type="text" name="direccion[0][ciudad]" class="form-control" placeholder="Ciudad"></div>
                    <div class="col-md-6"><input type="text" name="direccion[0][zona]" class="form-control" placeholder="Zona"></div>
                    <div class="col-md-6"><input type="text" name="direccion[0][calle]" class="form-control" placeholder="Calle"></div>
                    <div class="col-md-3"><input type="text" name="direccion[0][numero]" class="form-control" placeholder="Número"></div>
                    <div class="col-md-9"><input type="text" name="direccion[0][referencias]" class="form-control" placeholder="Referencias"></div>
                  </div>
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="direccion_principal" value="0" checked>
                    <label class="form-check-label">Principal</label>
                  </div>
                </div>
              </div>
              <button type="button" id="add-direccion" class="btn btn-outline-primary mt-2">Agregar Dirección</button>
            </div>

            <div class="d-flex justify-content-between mt-4">
              <button type="button" id="prevStep" class="btn btn-secondary">Anterior</button>
              <button type="submit" id="submitBtn" class="btn btn-success">Registrarse</button>
            </div>
          </div>
        </form>

        <div id="resultado" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<?php require_once("footer.php"); ?>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const direccionesContainer = document.getElementById('direcciones-container');
    const addDireccionBtn = document.getElementById('add-direccion');
    const editButton = document.getElementById('submitBtn');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const nextStepBtn = document.getElementById('nextStep');
    const prevStepBtn = document.getElementById('prevStep');
    let direccionCount = 1;

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
        <input class="form-check-input" type="radio" name="direccion_principal" value="${index}"> 
        <label class="form-check-label">Principal</label>
      </div>
    `;
      direccionesContainer.appendChild(newDireccionItem);
    };

    addDireccionBtn.addEventListener('click', () => {
      createDireccionBlock(direccionCount);
      direccionCount++;
    });

    // Botones multi-step
    nextStepBtn.addEventListener('click', () => {
      step1.style.display = 'none';
      step2.style.display = 'block';
    });

    prevStepBtn.addEventListener('click', () => {
      step2.style.display = 'none';
      step1.style.display = 'block';
    });

    // Registro
    if (editButton) {
      editButton.addEventListener('click', async (e) => {
        e.preventDefault();
        const form = document.getElementById('registerForm');
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

        const selectedRadio = document.querySelector('input[name="direccion_principal"]:checked');
        if (selectedRadio) principalIndex = parseInt(selectedRadio.value);
        data.direcciones = direcciones.map((dir, index) =>
          index === principalIndex ? {
            ...dir,
            principal: true
          } : dir
        );

        try {
          const response = await fetch('http://localhost/emarket_bolivia/backend/public/usuarios/registro', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          });
          const result = await response.json();
          console.log('Respuesta:', result);
          document.getElementById('resultado').innerHTML =
            `<div class="alert alert-success">Usuario Registrado con éxito 🎉</div>`;
        } catch (error) {
          console.error('Error:', error);
          document.getElementById('resultado').innerHTML =
            `<div class="alert alert-danger">Hubo un error al conectar con la API</div>`;
        }
      });
    }
  });
</script>
<script src="./js/global.js"></script>