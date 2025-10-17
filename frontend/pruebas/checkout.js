// Archivo: checkout.js

// URL base de tu API de backend (asegúrate de que sea la correcta)
const API_URL = 'http://localhost/emarket_bolivia/backend/public/ventas';

// Clave pública de Stripe (obtenida de tu panel de control de Stripe)
// ¡Importante! En un entorno real, esta clave se obtendría del backend.
let stripe;

let elements;
let clientSecret;
let idVenta;
const token = localStorage.getItem('token');


// Función para obtener la clave pública de Stripe
async function fetchStripePublicKey() {
    try {
        const response = await fetch(`${API_URL}/stripe-key`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const responseText = await response.text();
        console.log('Respuesta raw de stripe-key:', responseText);

        let responseData;
        try {
            responseData = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parsing JSON:', parseError);
            throw new Error(`Respuesta inválida del servidor: ${responseText.substring(0, 200)}...`);
        }

        if (!response.ok) {
            throw new Error(responseData.message || `Error al obtener la clave de Stripe (status: ${response.status})`);
        }

        return responseData.stripe_public_key;
    } catch (error) {
        console.error('Error al obtener la clave de Stripe:', error);
        throw error;
    }
}
function obtenerMetodoDePagoSeleccionado() {
    const radios = document.querySelectorAll('input[name="payment-method"]');
    for (const radio of radios) {
        if (radio.checked) return radio.value;
    }
    return null;
}


document.getElementById('submit-button').addEventListener('click', manejarPedido);

async function manejarPedido() {
    const metodoPago = obtenerMetodoDePagoSeleccionado();
    const submitButton = document.getElementById('submit-button');
    submitButton.disabled = true;
    submitButton.textContent = 'Procesando...';

    try {
        if (metodoPago === 'Stripe') {
            await flujoStripe();
        } else {
            await flujoManual(metodoPago);
        }
    } catch (error) {
        showMessage(error.message || "Error inesperado.", "error");
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Hacer Pedido';
    }
}
document.getElementById('confirm-button').addEventListener('click', async () => {
    const confirmButton = document.getElementById('confirm-button');
    confirmButton.disabled = true;
    confirmButton.textContent = 'Confirmando...';

    const { error, paymentIntent } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: `http://localhost/emarket_bolivia/backend/public/ventas/confirmar-pago`
        },
        redirect: "if_required"
    });

    if (error) {
        showMessage(error.message, "error");
        confirmButton.disabled = false;
        confirmButton.textContent = 'Confirmar Pago';
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
        await confirmarPagoEnBackend(paymentIntent.id);
        confirmButton.disabled = false;
        confirmButton.textContent = 'Confirmar Pago';
    } else {
        showMessage("Pago en estado inesperado: " + paymentIntent.status, "error");
        confirmButton.disabled = false;
        confirmButton.textContent = 'Confirmar Pago';
    }
});

async function flujoManual(tipoPago) {
    const { id_venta } = await crearVentaYObtenerClientSecret(tipoPago);
    idVenta = id_venta;

    if (!idVenta) throw new Error("No se pudo registrar la venta.");

    if (tipoPago === 'transferencia') {
        showMessage("Tu pedido ha sido registrado. Realiza la transferencia y envía el comprobante al vendedor.", "success");
    } else if (tipoPago === 'efectivo') {
        showMessage("Tu pedido ha sido registrado. El pago se realizará en efectivo al momento de la entrega.", "success");
    }
}

async function flujoStripe() {
    const stripePublicKey = await fetchStripePublicKey();
    stripe = Stripe(stripePublicKey);

    const { id_venta, client_secret } = await crearVentaYObtenerClientSecret('Stripe');
    idVenta = id_venta;
    clientSecret = client_secret;

    if (!clientSecret) throw new Error("No se pudo obtener el client_secret.");

    elements = stripe.elements({ clientSecret });

    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    const form = document.getElementById('payment-form');
    form.classList.remove('hidden');

    const confirmButton = document.getElementById('confirm-button');
    confirmButton.classList.remove('hidden');

    form.addEventListener('submit', manejarEnvioDeFormulario);
}


async function crearVentaYObtenerClientSecret(tipoPago) {
    const dataVenta = {
        id_vendedor: 6,
        id_comprador: 40,
        tipo_pago: tipoPago,
        tipo_entrega: 'envio',
        productos: [
            {
                id_producto: 8,
                cantidad: 4,
                precio_unit: 4500.00
            }
        ]
    };

    const response = await fetch(`${API_URL}/registrar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(dataVenta)
    });

    const responseText = await response.text();
    console.log('Respuesta raw del servidor:', responseText);

    let responseData;
    try {
        responseData = JSON.parse(responseText);
    } catch (parseError) {
        throw new Error(`Respuesta inválida del servidor: ${responseText.substring(0, 200)}...`);
    }

    if (!response.ok) {
        throw new Error(responseData.message || "Error al crear la venta en el backend.");
    }

    return responseData.data;
}


/**
 * Maneja el envío del formulario de pago.
 * @param {Event} e El evento de submit del formulario.
 */
async function manejarEnvioDeFormulario(e) {
    e.preventDefault();

    // Deshabilita el botón de pago para evitar múltiples clics
    const submitButton = document.getElementById('submit-button');
    submitButton.disabled = true;
    submitButton.textContent = 'Procesando...';

    // Confirma el pago con Stripe
    const { error, paymentIntent } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: `http://localhost/emarket_bolivia/backend/public/ventas/confirmar-pago`
        },
        redirect: "if_required" // Solo redirige si es necesario (e.g., 3DS)
    });

    if (error) {
        // Muestra el mensaje de error de Stripe en la página
        showMessage(error.message, "error");
        submitButton.disabled = false;
        submitButton.textContent = 'Pagar ahora';
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
        // Pago exitoso sin redirect: confirma en backend vía POST
        await confirmarPagoEnBackend(paymentIntent.id);
        submitButton.disabled = false;
        submitButton.textContent = 'Pagar ahora';
    } else {
        // Otros estados (raro, pero maneja)
        showMessage("Pago en estado inesperado: " + paymentIntent.status, "error");
        submitButton.disabled = false;
        submitButton.textContent = 'Pagar ahora';
    }
}

/**
 * Llama al backend para confirmar el pago (usado para casos sin redirect).
 * @param {string} paymentIntentId El ID del payment intent.
 */
async function confirmarPagoEnBackend(paymentIntentId) {
    if (!paymentIntentId) {
        showMessage("No se encontró el ID de la intención de pago.", "error");
        return;
    }

    const response = await fetch(`${API_URL}/confirmar-pago`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            payment_intent_id: paymentIntentId
        })
    });

    const responseData = await response.json();
    console.log(responseData);

    if (response.ok) {
        showMessage("¡Pago exitoso! Tu compra ha sido confirmada.", "success");
    } else {
        showMessage(responseData.message || "Error al confirmar el pago en el backend.", "error");
    }
}

/**
 * Muestra un mensaje en la página.
 * @param {string} message El mensaje a mostrar.
 * @param {string} type El tipo de mensaje ('success' o 'error').
 */
function showMessage(message, type) {
    const messageContainer = document.getElementById('payment-message');
    messageContainer.textContent = message;
    messageContainer.className = type;
}
