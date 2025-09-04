# 🛒 eMarket Bolivia

**eMarket Bolivia** es una plataforma digital que actúa como intermediaria entre múltiples vendedores —ya sea de productos físicos o servicios— y compradores, ofreciendo un espacio virtual compartido para facilitar transacciones, visibilidad y gestión comercial.

El objetivo del proyecto es brindar una solución escalable y accesible para emprendedores, negocios locales y consumidores, integrando funcionalidades de autenticación, gestión de perfiles, catálogo de productos y comunicación entre partes.

---

## 📁 Estructura del proyecto
```
emarket/ 
├── backend/ # Lógica PHP, rutas, controladores, conexión a base de datos 
├── frontend/ # Formularios de prueba y futura interfaz de usuario 
└── README.md # Documentación del proyecto
```

---

## 🚀 Clonar el repositorio (desde Visual Studio Code)

1. Abre **Visual Studio Code**.
2. Presiona `Ctrl + Shift + P` (o `Cmd + Shift + P` en Mac) para abrir la paleta de comandos.
3. Escribe `Git: Clone` y selecciona la opción.
4. Pega la URL del repositorio:
https://github.com/roxanaTech/emarket_bolivia
5. Elige una carpeta local donde se guardará el proyecto.
6. VS Code te preguntará si deseas abrir el proyecto: selecciona **"Sí"**.

---

## 🧰 Requisitos para el backend

- PHP 8+
- Composer
- Servidor local (XAMPP)

### 🔧 Instalación de dependencias

```bash
composer install
```
---
## 🔐 Configuración del entorno
Crea un archivo .env en la raíz de backend/ con tus variables de entorno. Ejemplo:
```env
DB_HOST=localhost
DB_NAME=emarket
DB_USER=root
DB_PASS=
JWT_SECRET=your-secret-key
```
---
## 🤝 Colaboradores
Este proyecto está abierto a contribuciones. Si formas parte del equipo **frontend**, puedes trabajar directamente en la carpeta **frontend/** y coordinar integraciones con el backend.

---
## 📄 Licencia
Este proyecto está bajo la licencia MIT.
