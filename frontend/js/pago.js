document.addEventListener('DOMContentLoaded', function () {
    // ========================
    // Gestión de tipo de entrega
    // ========================
    // Selecciona los radio buttons de tipo de entrega
    const deliveryRadios = document.querySelectorAll('input[name="deliveryType"]');
    const addressLabel = document.getElementById('addressLabel');
    const pickupInfo = document.getElementById('pickupInfo');

    // Función para generar HTML de direcciones de recogida por vendedor
    function generarDireccionesRecogida() {
        const grupos = window.datosAgrupados?.data?.grupos_vendedores || [];

        if (!grupos.length) {
            return '<p class="text-gray-700 dark:text-gray-300 text-sm">No hay vendedores con direcciones disponibles para recogida.</p>';
        }

        let htmlDirecciones = '<p class="text-gray-700 dark:text-gray-300 font-semibold mb-4">Puntos de Recogida por Vendedor</p>';
        grupos.forEach(grupo => {
            const { nombre_vendedor, direccion_negocio } = grupo;
            htmlDirecciones += `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
                    <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ${nombre_vendedor}</p>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p><span class="font-medium">Dirección:</span> ${direccion_negocio}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Puedes recoger tu pedido en esta ubicación una vez confirmada la orden.</p>
                </div>
            `;
        });
        return htmlDirecciones;
    }

    // Función para manejar el cambio de selección
    function handleDeliveryChange() {
        if (document.querySelector('input[name="deliveryType"]:checked').value === 'A Domicilio') {
            // Mostrar dirección, ocultar recogida
            addressLabel.classList.remove('hidden');
            pickupInfo.classList.add('hidden');
        } else {
            // Ocultar dirección, mostrar recogida con direcciones dinámicas
            addressLabel.classList.add('hidden');
            pickupInfo.classList.remove('hidden');
            pickupInfo.innerHTML = generarDireccionesRecogida();  // Cargar dinámicamente
        }
    }

    // Agrega listener a cada radio button
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', handleDeliveryChange);
    });
    // ========================
    // Gestión de Pestañas de Metodos de pago
    // ========================

    // Selecciona los radio buttons de método de pago
    const paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
    const cardForm = document.getElementById('cardForm');
    const transferInfo = document.getElementById('transferInfo');
    const cashInfo = document.getElementById('cashInfo');

    // Función para manejar el cambio de selección 
    function handlePaymentChange() {
        const selectedValue = document.querySelector('input[name="paymentMethod"]:checked').value;

        // Ocultar todos primero
        cardForm.classList.add('hidden');
        transferInfo.classList.add('hidden');
        cashInfo.classList.add('hidden');

        if (selectedValue === 'Tarjeta') {
            cardForm.classList.remove('hidden');
        } else if (selectedValue === 'Transferencia') {
            transferInfo.classList.remove('hidden');
            actualizarSeccionesPago();  // Actualizar dinámicamente si se selecciona transfer
        } else if (selectedValue === 'Efectivo') {
            cashInfo.classList.remove('hidden');
            actualizarSeccionesPago();  // Actualizar dinámicamente si se selecciona cash
        }
        // Para Efectivo, ya no necesita else (todo oculto por default arriba)
    }

    // Agrega listener a cada radio button
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', handlePaymentChange);
    });
    // ========================
    // Gestión de Pasos Dinámicos
    // ========================

    // Elementos de los pasos
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const line12 = document.getElementById('line1-2');
    const line23 = document.getElementById('line2-3');

    // Campos del formulario
    const phoneInput = document.getElementById('telefono-contacto');  // Usamos ID ahora
    const addressTextarea = document.getElementById('direccion-entrega');  // ID actualizado
    const cardNumber = document.getElementById('numero-tarjeta');  // ID
    const cardExpiry = document.getElementById('expiracion-tarjeta');  // ID
    const cardCvv = document.getElementById('cvv-tarjeta');  // ID

    let currentStep = 1;

    // Actualiza el estado visual de los pasos
    function updateStepUI() {
        // Resetear todos
        step1.classList.remove('text-primary');
        step1.querySelector('div').className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-gray-200 text-gray-500';
        step2.classList.remove('text-success');
        step2.querySelector('div').className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-gray-200 text-gray-500';
        step3.classList.remove('text-primary');
        step3.querySelector('div').className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-gray-300 text-gray-500';

        line12.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300';
        line23.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300';

        if (currentStep >= 1) {
            step1.classList.add('text-primary');
            step1.querySelector('div').className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-primary text-white';
        }
        if (currentStep >= 2) {
            step2.classList.add('text-success');
            step2.querySelector('div').className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-success text-white';
            line12.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-primary';
        }
        if (currentStep >= 3) {
            step3.classList.add('text-primary');
            step3.querySelector('div').className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-primary text-white';
            line23.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-success';
        }
    }

    // Validaciones por paso
    function isValidStep1() {
        const phone = phoneInput.value.trim();
        if (!phone) return false;

        const deliveryType = document.querySelector('input[name="deliveryType"]:checked')?.value;
        if (!deliveryType) return false;

        if (deliveryType === 'A Domicilio') {
            return addressTextarea.value.trim() !== '';
        }
        return true;
    }

    function isValidStep2() {
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value;
        if (!paymentMethod) return false;

        if (paymentMethod === 'Tarjeta') {
            const num = cardNumber.value.replace(/\s/g, '');
            const exp = cardExpiry.value;
            const cvv = cardCvv.value;
            // Validación básica: 16 dígitos, formato MM/AA, CVV de 3-4 dígitos
            return (
                num.length === 16 &&
                /^\d{16}$/.test(num) &&
                /^\d{2}\/\d{2}$/.test(exp) &&
                /^\d{3,4}$/.test(cvv)
            );
        }

        // Para Efectivo o Transferencia, basta con seleccionar el método
        return true;
    }

    // Verifica y avanza automáticamente
    function updateStepBasedOnValidation() {
        if (isValidStep1()) {
            if (isValidStep2()) {
                currentStep = 3; // Paso 2 completado → paso 3 activo
            } else {
                currentStep = 2; // Paso 1 OK, pero paso 2 incompleto
            }
        } else {
            currentStep = 1; // Ni siquiera el paso 1 está listo
        }
        updateStepUI();
    }

    // Escuchar cambios en los campos del Paso 1
    phoneInput.addEventListener('input', updateStepBasedOnValidation);
    addressTextarea.addEventListener('input', updateStepBasedOnValidation);
    deliveryRadios.forEach(radio => radio.addEventListener('change', updateStepBasedOnValidation));

    paymentRadios.forEach(radio => radio.addEventListener('change', updateStepBasedOnValidation));
    cardNumber.addEventListener('input', updateStepBasedOnValidation);
    cardExpiry.addEventListener('input', updateStepBasedOnValidation);
    cardCvv.addEventListener('input', updateStepBasedOnValidation);

    // Inicializar UI
    updateStepUI();

    // Ejecuta al cargar para el estado inicial (Tarjeta checked)
    handlePaymentChange();

    // ========================
    // NUEVA LÓGICA: Carga de Datos del Carrito Agrupado
    // ========================
    // Función para obtener el token (de global.js)
    function obtenerToken() {
        const token = localStorage.getItem('token');
        if (!token) {
            console.warn('Token no encontrado en localStorage');
            mostrarToast('Error: Debes iniciar sesión para proceder al pago', 'error');
            return null;
        }
        return token;
    }

    // Función para actualizar secciones de pago (cash/transfer) con datos de vendedores
    function actualizarSeccionesPago() {
        const cashContainer = document.getElementById('cashInfo');
        const transferContainer = document.getElementById('transferInfo');
        const grupos = window.datosAgrupados?.data?.grupos_vendedores || [];  // Datos globales cargados

        if (!grupos.length) return;  // Si no hay datos, no actualizar

        // Limpiar contenedores previos
        cashContainer.innerHTML = '<p class="text-gray-700 dark:text-gray-300 font-semibold mb-4">Instrucciones para Pago en Efectivo por Vendedor</p>';
        transferContainer.innerHTML = '<p class="text-gray-700 dark:text-gray-300 font-semibold mb-4">Detalles de Cuenta Bancaria por Vendedor</p>';

        grupos.forEach(grupo => {
            const { id_vendedor, nombre_vendedor, telefono, banco, cuenta_bancaria } = grupo;

            // Para Efectivo
            const cashBlock = document.createElement('div');
            cashBlock.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4';
            cashBlock.innerHTML = `
                <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ${nombre_vendedor}</p>
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <p>El vendedor se pondrá en contacto contigo dentro de las próximas 24 horas para coordinar el pago en efectivo y la entrega.</p>
                    <p><span class="font-medium">Contacto:</span> +591 ${telefono} (WhatsApp disponible)</p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Asegúrate de tener el efectivo listo en el momento acordado.</p>
            `;
            cashContainer.appendChild(cashBlock);

            // Para Transferencia (sin SWIFT)
            const transferBlock = document.createElement('div');
            transferBlock.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4';
            transferBlock.innerHTML = `
                <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ${nombre_vendedor}</p>
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <p><span class="font-medium">Banco:</span> ${banco}</p>
                    <p><span class="font-medium">Número de Cuenta:</span> ${cuenta_bancaria}</p>
                    <p><span class="font-medium">Titular:</span> ${nombre_vendedor}</p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Realiza la transferencia y envía el comprobante al vendedor una vez confirmada la orden.</p>
            `;
            transferContainer.appendChild(transferBlock);
        });
    }

    // Función para generar HTML de un grupo de vendedor
    function generarHtmlGrupo(grupo) {
        const { id_vendedor, nombre_vendedor, items, total_grupo } = grupo;
        let htmlItems = '';
        items.forEach(item => {
            const { nombre, subtotal, imagen_principal } = item;
            const imgUrl = `https://picsum.photos/600/400?random=${imagen_principal}`;
            const subtotalFormateado = parseFloat(subtotal).toFixed(2);
            htmlItems += `
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>${nombre}</span>
                    <span>Bs. ${subtotalFormateado}</span>
                </div>
            `;
        });

        const totalFormateado = parseFloat(total_grupo).toFixed(2);
        return `
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <p class="font-bold text-gray-800 dark:text-gray-200 mb-3">Vendedor: ${nombre_vendedor}</p>
                <div class="space-y-2">
                    ${htmlItems}
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3 flex justify-between font-semibold text-gray-800 dark:text-gray-200">
                    <span>Subtotal</span>
                    <span>Bs. ${totalFormateado}</span>
                </div>
            </div>
        `;
    }

    // Función principal para cargar datos agrupados
    async function cargarDatosAgrupados() {
        console.log('Debug: Cargando datos agrupados del carrito...');
        const token = obtenerToken();
        if (!token) return;

        try {
            const respuesta = await fetch(`${apiUrl}/carrito/agrupado`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!respuesta.ok) {
                throw new Error(`Error del servidor: ${respuesta.status}`);
            }

            const resultado = await respuesta.json();
            console.log('Debug: Respuesta del endpoint agrupado:', resultado);

            if (resultado.status !== 'success') {
                throw new Error(resultado.mensaje || 'Error al cargar datos agrupados');
            }

            // Guardar datos globalmente para reutilizar (ej: en actualizarSeccionesPago)
            window.datosAgrupados = resultado;

            const contenedorGrupos = document.getElementById('contenedor-grupos-vendedores');
            const grupos = resultado.data.grupos_vendedores || [];
            const totalGeneral = resultado.data.total_general || 0;

            if (grupos.length === 0) {
                contenedorGrupos.innerHTML = '<p class="text-center text-gray-500 py-8">No hay items en el carrito para procesar.</p>';
                actualizarTotales(0);
                return;
            }

            let htmlGrupos = '';
            grupos.forEach(grupo => {
                htmlGrupos += generarHtmlGrupo(grupo);
            });
            contenedorGrupos.innerHTML = htmlGrupos;

            // Actualizar totales
            actualizarTotales(totalGeneral);

            // Actualizar secciones de pago si están visibles (llama a handlePaymentChange si es necesario)
            actualizarSeccionesPago();

            console.log('Debug: Datos agrupados cargados exitosamente con', grupos.length, 'grupos');
            mostrarToast('Datos de pago cargados correctamente', 'success');

        } catch (error) {
            console.error('Debug: Error en cargarDatosAgrupados:', error);
            mostrarToast(`Error al cargar datos: ${error.message}`, 'error');
            document.getElementById('contenedor-grupos-vendedores').innerHTML = '<p class="text-center text-red-500 py-8">Error al cargar el carrito. <a href="carrito.php" class="underline">Volver al carrito</a></p>';
        }
    }

    // Función para actualizar subtotal y total
    function actualizarTotales(totalGeneral) {
        const subtotalSpan = document.getElementById('subtotal-general');
        const totalSpan = document.getElementById('total-pagar');
        const totalFormateado = parseFloat(totalGeneral).toFixed(2);

        if (subtotalSpan) subtotalSpan.textContent = `Bs. ${totalFormateado}`;
        if (totalSpan) totalSpan.textContent = `Bs. ${totalFormateado}`;

        console.log(`Debug: Totales actualizados a Bs ${totalFormateado}`);
    }

    // Event listener para botón Volver al Carrito
    document.getElementById('btn-volver-carrito').addEventListener('click', () => {
        window.location.href = 'carrito.php';
    });

    // Event listener para botón Confirmar Compra
    document.getElementById('btn-confirmar-compra').addEventListener('click', async () => {
        const btn = document.getElementById('btn-confirmar-compra');
        btn.disabled = true;
        btn.textContent = 'Procesando...';

        // Validar todo (pasos 1 y 2)
        if (!isValidStep1() || !isValidStep2()) {
            mostrarToast('Por favor, completa todos los campos requeridos', 'error');
            btn.disabled = false;
            btn.textContent = 'Confirmar Compra y Generar Órdenes';
            return;
        }

        // Validación específica de tarjeta con Luhn si aplica
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        if (paymentMethod === 'Tarjeta') {
            if (!validarTarjetaLuhn()) {
                mostrarToast('Datos de tarjeta inválidos (verifica el número con algoritmo Luhn)', 'error');
                btn.disabled = false;
                btn.textContent = 'Confirmar Compra y Generar Órdenes';
                return;
            }
        }

        // Recopilar datos comunes
        const deliveryType = document.querySelector('input[name="deliveryType"]:checked').value;
        const direccion = deliveryType === 'A Domicilio' ? document.getElementById('direccion-entrega').value.trim() : null;
        const telefono = document.getElementById('telefono-contacto').value.trim();
        let datosPago = {};
        if (paymentMethod === 'Tarjeta') {
            // Enviar datos de PRUEBA aprobados si pasa validación
            datosPago = {
                numero_tarjeta: "4111111111111111",  // Número de prueba aprobado
                fecha_expiracion: "12/28",
                cvv: "123"
            };
        }

        const datosComunes = {
            tipo_pago: paymentMethod.toLowerCase(),
            tipo_entrega: deliveryType.toLowerCase(),
            direccion_entrega: direccion,
            telefono_contacto: telefono,
            ...datosPago  // Agrega si es tarjeta
        };

        const token = obtenerToken();
        if (!token) {
            mostrarToast('Error: Sesión expirada', 'error');
            btn.disabled = false;
            btn.textContent = 'Confirmar Compra y Generar Órdenes';
            return;
        }

        const grupos = window.datosAgrupados?.data?.grupos_vendedores || [];
        const ordenesCreadas = [];  // Array para resultados
        let errorOcurrido = false;

        console.log('Debug: Iniciando creación de órdenes para', grupos.length, 'vendedores...');

        // Loop secuencial: Una petición por vendedor/grupo
        for (const grupo of grupos) {
            const { items, id_vendedor, nombre_vendedor } = grupo;
            const payload = {
                ...datosComunes,
                items: items.map(item => ({
                    id_producto: item.id_producto,
                    cantidad: item.cantidad,
                    precio_unitario: parseFloat(item.precio_unitario)
                }))
            };

            try {
                const respuesta = await fetch(`${apiUrl}/ventas/crear`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (!respuesta.ok) {
                    throw new Error(`Error ${respuesta.status} para vendedor ${nombre_vendedor}`);
                }

                const resultado = await respuesta.json();
                console.log(`Debug: Respuesta para ${nombre_vendedor}:`, resultado);

                if (resultado.status !== 'success') {
                    throw new Error(resultado.mensaje || 'Error al crear venta');
                }

                // Asumir estructura: { "data": { "id_venta": 101, "numero_orden": "#101" } }
                ordenesCreadas.push({
                    numero_orden: resultado.data.numero_orden || `#${resultado.data.id_venta}`,
                    vendedor: nombre_vendedor,
                    total_grupo: grupo.total_grupo
                });

            } catch (error) {
                console.error(`Debug: Error creando orden para ${nombre_vendedor}:`, error);
                mostrarToast(`Error al crear orden para ${nombre_vendedor}: ${error.message}`, 'error');
                errorOcurrido = true;
                break;  // Detener si uno falla (o quita para parciales)
            }
        }

        if (errorOcurrido || ordenesCreadas.length === 0) {
            btn.disabled = false;
            btn.textContent = 'Confirmar Compra y Generar Órdenes';
            return;
        }

        // NUEVO: Limpiar carrito después de crear todas las órdenes
        try {
            console.log('Debug: Limpiando carrito...');
            const respuestaConvertir = await fetch(`${apiUrl}/carrito/convertir`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})  // Body vacío; ajusta si necesita params
            });

            if (!respuestaConvertir.ok) {
                throw new Error(`Error ${respuestaConvertir.status} al convertir carrito`);
            }

            const resultadoConvertir = await respuestaConvertir.json();
            console.log('Debug: Respuesta de /carrito/convertir:', resultadoConvertir);

            if (resultadoConvertir.status !== 'success') {
                throw new Error(resultadoConvertir.mensaje || 'Error al limpiar carrito');
            }

            console.log('Debug: Carrito limpiado exitosamente');

        } catch (error) {
            console.error('Debug: Error al limpiar carrito:', error);
            mostrarToast(`Órdenes creadas, pero error al limpiar carrito: ${error.message}`, 'error');
            // Continuar redirigiendo, ya que las ventas ya están hechas
        }

        // Éxito: Guardar en localStorage y redirigir
        localStorage.setItem('ordenesCreadas', JSON.stringify(ordenesCreadas));
        mostrarToast(`¡Éxito! Se crearon ${ordenesCreadas.length} órdenes y el carrito fue limpiado.`, 'success');
        setTimeout(() => {
            window.location.href = 'confirmacion.php';
        }, 2000);

        btn.disabled = false;
        btn.textContent = 'Confirmar Compra y Generar Órdenes';
    });

    // Nueva función: Validación Luhn para tarjeta
    function validarTarjetaLuhn() {
        const numero = document.getElementById('numero-tarjeta').value.replace(/\s/g, '');
        const exp = document.getElementById('expiracion-tarjeta').value;
        const cvv = document.getElementById('cvv-tarjeta').value;

        // Chequeos básicos
        if (numero.length !== 16 || !/^\d{16}$/.test(numero)) return false;
        if (!/^\d{2}\/\d{2}$/.test(exp)) return false;
        if (!/^\d{3,4}$/.test(cvv)) return false;

        // Algoritmo Luhn
        let suma = 0;
        let esPar = false;
        for (let i = numero.length - 1; i >= 0; i--) {
            let digito = parseInt(numero.charAt(i));
            if (esPar) {
                digito *= 2;
                if (digito > 9) digito = digito - 9;
            }
            suma += digito;
            esPar = !esPar;
        }
        return suma % 10 === 0;
    }

    // Cargar datos al inicializar
    cargarDatosAgrupados();
});