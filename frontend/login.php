<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --color-azul: #02187D;
            --gris-sutil: #fafafa;
        }

        body {
            background-color: var(--gris-sutil);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 25px 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .login-logo img {
            height: 50px;
            /* 🔽 Smaller logo */
            object-fit: contain;
        }

        .form-label {
            color: var(--color-azul);
            font-weight: 500;
            text-align: left;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            margin-bottom: 15px;
        }

        .form-control:focus {
            border-color: var(--color-azul);
            outline: none;
            box-shadow: 0 0 0 3px rgba(2, 24, 125, 0.1);
        }

        .forgot-password {
            color: var(--color-azul);
            font-size: 0.85rem;
            text-decoration: none;
            margin-top: 8px;
            display: block;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            background-color: var(--color-azul);
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-login:hover {
            background-color: #01125a;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #6c757d;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
            margin: 0 8px;
        }

        .btn-create-account {
            background: transparent;
            color: var(--color-azul);
            border: 1px solid var(--color-azul);
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-create-account:hover {
            background-color: rgba(2, 24, 125, 0.05);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-logo">
            <a href="principal.php"><img src="./img/logoh.png" alt="Logo e-market Bolivia"></a>
        </div>

        <!-- Formulario -->
        <form id="loginForm" method="post" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico o nombre de usuario</label>
                <input type="text" id="email" name="usuario" class="form-control" placeholder="" autocomplete="off" />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder=""
                    autocomplete="new-password" />
            </div>

            <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-login">Iniciar Sesión</button>
            </div>
        </form>

        <!-- Separador -->
        <div class="divider">¿Eres nuevo en e-market Bolivia?</div>

        <!-- Botón crear cuenta -->
        <div class="d-grid gap-2 mt-2">
            <button type="button" class="btn btn-create-account" onclick="window.location.href='registro.html'">
                Crear una cuenta
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('loginForm');
            if (!loginForm) {
                console.error('Formulario no encontrado');
                return;
            }

            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const host = window.location.hostname;
                const apiUrl = `http://${host}/emarket_bolivia/backend/public`;

                try {
                    const response = await fetch(`${apiUrl}/login`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email,
                            password
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.data?.token) {
                        localStorage.setItem('token', data.data.token);
                        window.location.href = 'perfilUsuario.php';
                    } else {
                        alert(data.mensaje || 'Credenciales incorrectas');
                    }
                } catch (error) {
                    console.error('Error de conexión:', error);
                    alert('No se pudo conectar con el servidor.');
                }
            });
        });
    </script>
    <script src="./js/global.js"></script>
</body>

</html>