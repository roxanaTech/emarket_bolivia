<?php 
session_start();
$usuario = $_SESSION['usuario'] ?? 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_perfil.css">
</head>
<body>
<!-- BARRA SUPERIOR -->
<div class="bg-bolivia"></div>

<header class="header-fixed">
    <nav class="navbar barra navbar-expand-lg">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-store fs-2 me-2 text-warning"></i>
                <span class="navbar-brand text-white fw-bold mb-0">e-Market Bolivia</span>
            </div>
            <div class="d-flex align-items-center text-white">
                <i class="fas fa-user-circle me-2 fs-5"></i>
                <span>Vendedor: <?= htmlspecialchars($usuario) ?></span>
            </div>
        </div>
    </nav>
</header>

<!-- CONTENIDO -->
<div class="container py-5 mt-4">

    <div class="row g-4">
        <!-- Sidebar -->
        <aside class="col-lg-3">
            <div class="sidebar-card">
                <h5 class="mb-3 text-center text-primary">Panel</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="available">
                            <i class="fas fa-box me-2"></i>Vista Previa
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="col-lg-9">
            <div class="company-info">
                <div id="formSection">
                    <h2 class="section-title mb-4"><i class="fas fa-cubes me-2"></i>Registrar Nuevo Producto</h2>

                    <form id="productForm" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subcategoría</label>
                                <select id="nombre_subcategoria" name="nombre_subcategoria" class="form-select">
                                    <option value="">Seleccionar subcategoría</option>                                    
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Marca</label>
                                <input type="text" id="marca" name="marca" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Precio (Bs.) *</label>
                                <input type="number" id="precio" name="precio" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cantidad *</label>
                                <input type="number" id="stock" name="stock" class="form-control" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                            </div>

                            <!-- Galería -->
                            <div class="col-12">
                                <label class="form-label">Imagen Principal</label>
                                <div class="d-flex flex-wrap gap-3 mb-3" id="mainImageGallery">
                                    <div class="add-image-btn" id="addMainImageBtn">
                                        <i class="fas fa-plus fs-4 mb-2"></i>
                                        <span class="small">Agregar imagen</span>
                                    </div>
                                </div>
                                <input type="file" id="mainProductImage" accept="image/*" class="d-none">
                            </div>

                            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-warning-custom" id="cancelBtn">Cancelar</button>
                                <button type="submit" class="btn btn-success-custom">Guardar Producto</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="availableProductsSection" class="d-none">
                    <h2 class="section-title mb-3"><i class="fas fa-box me-2"></i>Productos Disponibles</h2>
                    <div id="availableProductsList"></div>
                </div>
            </div>
        </main>
    </div>
</div>

<footer>
    <p>© 2025 e-Market Bolivia - Todos los derechos reservados</p>
</footer>

<!-- === SCRIPT === -->
<script>
const productForm = document.getElementById('productForm');
const mainProductImage = document.getElementById('mainProductImage');
const addMainImageBtn = document.getElementById('addMainImageBtn');
const mainImageGallery = document.getElementById('mainImageGallery');
const formSection = document.getElementById('formSection');
const availableProductsSection = document.getElementById('availableProductsSection');
let mainImageId = null;

// ==== MOSTRAR SECCIÓN ====
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        showSection(link.dataset.section);
    });
});

function showSection(section) {
    if (section === 'form') {
        formSection.classList.remove('d-none');
        availableProductsSection.classList.add('d-none');
    } else {
        formSection.classList.add('d-none');
        availableProductsSection.classList.remove('d-none');
    }
}

// ==== IMAGEN PRINCIPAL ====
addMainImageBtn.addEventListener('click', () => mainProductImage.click());
mainProductImage.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        mainImageGallery.innerHTML = `
            <div class="gallery-item">
                <img src="${ev.target.result}" alt="Imagen">
                <span class="remove" onclick="removeMainImage()">&times;</span>
                <span class="main-badge"><i class='fas fa-crown'></i> Principal</span>
            </div>
        `;
        mainImageId = "temp-image";
    }
    reader.readAsDataURL(file);
});

function removeMainImage() {
    mainImageGallery.innerHTML = `
        <div class="add-image-btn" id="addMainImageBtn">
            <i class="fas fa-plus fs-4 mb-2"></i>
            <span class="small">Agregar imagen</span>
        </div>
    `;
    mainImageId = null;
}

// ==== GUARDAR PRODUCTO ====
productForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(productForm);
    formData.append('imagen', mainProductImage.files[0]);

    const response = await fetch('backend/ProductoModel.php?registrarProducto', {
        method: 'POST',
        body: formData
    });

    const result = await response.text();
    alert(result || "Producto registrado correctamente");
    productForm.reset();
    mainImageGallery.innerHTML = `<div class="add-image-btn" id="addMainImageBtn"><i class="fas fa-plus fs-4 mb-2"></i><span class="small">Agregar imagen</span></div>`;
});
</script>
</body>
</html>
