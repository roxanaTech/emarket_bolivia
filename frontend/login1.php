<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /*_________________________________________*/
    /**** LOGIN ****/
    /*_________________________________________*/

    * {
      margin: 0;
      padding: 0;
    }

    .card-center {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: #09185F;
      padding: 20px;
    }

    /* Card de login */
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
      padding: 40px 30px;
      width: 100%;
      max-width: 550px;
      /* Ajusta este valor */
    }

    .btn-danger {
      width: 100%;
      padding: 12px;
      background-color: #9ab515ff;
      border: none;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .btn-danger:hover {
      background-color: #1d8f04ff;
    }


    /* Logo */
    .login-logo img {
      width: 200px;
      height: auto;
      margin: 0 auto;
      display: block;
    }

    /* Título del login */
    .login-box-msg {
      text-align: center;
      font-weight: bold;
      color: #4a4747;
      margin-top: 15px;
      margin-bottom: 20px;
    }

    /* Input */
    .form-control {
      width: 100%;
      padding: 10px 15px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-size: 16px;
      margin-bottom: 15px;
    }


    /* Mensajes */
    .msg {
      margin-top: 15px;
      font-size: 14px;
      color: #721c24;
    }

    .success {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 4px;
    }

    .error {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 4px;
    }
  </style>
</head>

<body>
  <div class="card-center">
    <div class="login-box">
      <div class="card">
        <div class="login-logo">
          <img src="./img/logoh.png" alt="logo">
          <h3 class="login-box-msg">Iniciar Sesión</h3>
        </div>

        <div class="card-body login-card-body">
          <form id="loginForm" method="post" autocomplete="off">
            <div class="mb-3">
              <input type="text" id="email" name="usuario" class="form-control" placeholder="Nombre Usuario" autocomplete="off" />
            </div>
            <div class="mb-3">
              <input type="password" id="password" name="password" class="form-control" placeholder="Password" autocomplete="new-password" />
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-danger">INGRESAR</button>
            </div>
          </form>
        </div>

        <!-- mensaje de login -->
        <div class="msg" id="resultado">
          <?php
          if ($_SERVER["REQUEST_METHOD"] == "GET") {
            if (isset($_GET["mensaje"]) && !empty($_GET["mensaje"])) {
              $mensaje = htmlspecialchars($_GET["mensaje"]);
              echo "<div class='error text-center'>Mensaje: " . $mensaje . "</div>";
            }
          }
          ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    const loginForm = document.getElementById('loginForm');
    const resultado = document.getElementById('resultado');

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

        if (response.ok) {
          if (data.data && data.data.token) {
            localStorage.setItem('token', data.data.token);
            alert("Login exitoso");
            window.location.href = '/emarket_bolivia/frontend/perfilUsuario.html';
          } else {
            resultado.innerHTML = "<div class='error'>Respuesta inesperada del servidor.</div>";
          }
        } else {
          resultado.innerHTML = `<div class='error'>${data.mensaje || 'Error en el inicio de sesión.'}</div>`;
        }
      } catch (error) {
        resultado.innerHTML = "<div class='error'>No se pudo conectar con el servidor. Inténtalo más tarde.</div>";
      }
    });
  </script>
</body>

</html>