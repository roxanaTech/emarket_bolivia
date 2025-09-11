async function registrarVendedor(datosVendedor) {
    try {
        const tokenAntiguo = localStorage.getItem('jwt_token');

        const response = await fetch('/api/vendedor/registrar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${tokenAntiguo}`
            },
            body: JSON.stringify(datosVendedor)
        });

        const resultado = await response.json();

        if (resultado.status === 'success') {
            // Accede al nuevo token desde la respuesta
            const nuevoToken = resultado.data.token;

            // Guarda el nuevo token en el localStorage, reemplazando el anterior
            localStorage.setItem('jwt_token', nuevoToken);

            // Muestra un mensaje de éxito al usuario
            console.log(resultado.mensaje);

            // Opcional: accede a los datos del usuario actualizado
            console.log('Rol de usuario actualizado a:', resultado.data.usuario.rol);

            //mostrar o habilitar el menu para el vendedor...

        } else {
            // Maneja errores (ej. validación fallida)
            console.error('Error al registrar al vendedor:', resultado.mensaje);
        }
    } catch (error) {
        console.error('Error de red:', error);
    }
}