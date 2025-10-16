<?php include "header_perfil.php"; ?>
<!-- Contenido -->
<div class="container">
    <div class="row mt-4">
        <!-- Columna izquierda -->
        <div class="col-md-8">
            <div class="contact-card">
                <h5>Información de Contacto</h5>
                <div class="d-flex align-items-center">
                    <img src="persona.jpg" class="rounded-circle me-3" width="60" height="60" alt="Contacto">
                    <div>
                        <strong><?php echo ""; ?></strong><br>
                        Representante de Ventas
                    </div>
                </div>
                <hr>
                <p><i class="bi bi-telephone"></i> Teléfono: <a href="#">Ver detalles</a></p>
                <p><i class="bi bi-phone"></i> Móvil: <a href="#">Ver detalles</a></p>
                <p><i class="bi bi-globe"></i> Sitio web: <a href="https://miempresa.com">miempresa.com</a></p>
            </div>

            <div class="location-card">
                <h5>Ubicación de la Empresa</h5>
                <p><i class="bi bi-geo-alt"></i> Calle Falsa 123, La Paz, Bolivia</p>
                <small class="text-muted">Verificado por SGS Group</small>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="col-md-4">
            <div class="sidebar-card">
                <h6>Póngase en contacto con el proveedor</h6>
                <p><strong>Mi Empresa S.A.</strong></p>
                <button class="btn btn-primary-custom w-100 mb-2">Contactar Ahora</button>
                <button class="btn btn-success-custom w-100">Solicitar Cotización</button>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>
<script src="./js/global.js"></script>