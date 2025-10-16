<?php require('header.php'); ?>
<div class="container">
    <div class="view">
        <img src="./img/logoh.png" alt="logo">
    </div>
    <div class="row">
        <div class="register-container">

            <div class="subtitle">
                Crea tu cuenta
            </div>

            <form id="registerForm">

                <!-- Campo id oculto para la actualizacion -->
                <input type="hidden" id="id_usuario" name="id_usuario" value="">

                <input type="text" id="nombres" name="nombres" placeholder="Nombre completo" required>

                <input type="email" id="email" name="email" placeholder="Correo electrónico" required>

                <input type="password" id="password" name="password" placeholder="Contraseña" required>

                <button type="submit" id="submitBtn" class="btn-register">REGISTRARSE</button>
            </form>

            <!-- Div para mostrar mensajes de resultado -->
            <div id="result"></div>
            <div id="debug"></div>
        </div>
    </div>
</div>
<!-- Script JavaScript para manejar registro -->
<script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const resultDiv = document.getElementById('result');
        const debugDiv = document.getElementById('debug');
        const submitBtn = document.getElementById('submitBtn');
        const ip = "10.163.234.110";

        // Mostrar estado de carga
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registrando...';
        resultDiv.style.display = 'none';
        debugDiv.innerHTML = '';

        const data = {
            nombres: document.getElementById('nombres').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
        };

        // Debug: Mostrar datos que se van a enviar
        debugDiv.innerHTML = '<strong>Datos enviados:</strong>\n' + JSON.stringify(data, null, 2);

        try {
            console.log('Enviando datos:', data);

            // Variable para almacenar la URL base de la API
            let apiUrl;

            // Obtener el hostname de la URL actual.
            // Esto funcionará tanto para localhost como para cualquier IP de red local (192.168.x.x, 10.x.x.x, etc.)
            const host = window.location.hostname;

            // Construir la URL base de la API usando el hostname actual
            apiUrl = `http://${host}/emarket_bolivia/backend/public`;

            // Dentro de tu función de registro
            const response = await fetch(`http://${host}/emarket-bolivia/registrar_usuario.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
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

            const result = await response.json();
            console.log('Response data:', result);

            // Mostrar resultado
            resultDiv.style.display = 'block';

            if (result.success || result.status === 'success') {
                resultDiv.className = 'success';
                resultDiv.innerHTML = '<strong>¡Éxito!</strong><br>' + (result.mensaje || result.message || 'Usuario Ingresado');
                alert("¡Registro exitoso! Por favor, inicia sesión ahora.");

                // Detectamos si es la app WebView o navegador normal
                const isAppWebView = window.navigator.userAgent.includes("MyAppWebView");

                if (isAppWebView) {
                    // Redirige a la app nativa
                    window.location.href = "myapp://auth?email=" + encodeURIComponent(data.email);
                } else {
                    // Redirige al login web
                    window.location.href = '/emarket_bolivia/frontend/login.html';
                }

            } else {
                resultDiv.className = 'error';
                resultDiv.innerHTML = '<strong>Error:</strong><br>' + (result.mensaje || result.message || result.error || 'Error desconocido');
            }

            // Mostrar debug de respuesta
            debugDiv.innerHTML += '\n\n<strong>Respuesta del servidor:</strong>\n' + JSON.stringify(result, null, 2);

        } catch (error) {
            console.error('Error completo:', error);

            resultDiv.style.display = 'block';
            resultDiv.className = 'error';
            resultDiv.innerHTML = '<strong>Error de conexión:</strong><br>' + error.message;

            // Debug más detallado del error
            debugDiv.innerHTML += '\n\n<strong>Error capturado:</strong>\n' + error.toString();

            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                debugDiv.innerHTML += '\n\n<strong>Posibles causas:</strong>\n';
                debugDiv.innerHTML += '- Problema de CORS\n';
                debugDiv.innerHTML += '- Servidor no disponible\n';
                debugDiv.innerHTML += '- URL incorrecta\n';
            }
        } finally {
            // Restaurar botón
            submitBtn.disabled = false;
            submitBtn.textContent = 'Registrar Usuario';
        }
    });
</script>