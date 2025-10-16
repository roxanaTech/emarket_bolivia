<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-market Bolivia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/variables.css">
    <link rel="stylesheet" href="./css/estilos.css">
    <link rel="stylesheet" href="./css/custom.css">
</head>

<body>
    <!-- Carrusel de Ofertas -->
    <section class="container pb-5">
        <div id="offersCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#offersCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#offersCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#offersCarousel" data-bs-slide-to="2"></button>
            </div>

            <div class="carousel-inner rounded">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1607082350899-7e105aa886ae?auto=format&fit=crop&w=2000&q=80"
                        class="d-block w-100 h-100" alt="Ofertas de tecnología">
                    <div class="carousel-caption d-none d-md-block">
                        <h3>Ofertas de Tecnología</h3>
                        <p>Descuentos increíbles en laptops, smartphones y más</p>
                        <a href="#" class="btn btn-danger">Ver Ofertas</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1605733513597-a8f8341084e6?auto=format&fit=crop&w=2000&q=80"
                        class="d-block w-100 h-100" alt="Moda y accesorios">
                    <div class="carousel-caption d-none d-md-block">
                        <h3>Moda y Accesorios</h3>
                        <p>Las mejores tendencias con envío gratis</p>
                        <a href="#" class="btn btn-danger">Comprar Ahora</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=2000&q=80"
                        class="d-block w-100 h-100" alt="Hogar y muebles">
                    <div class="carousel-caption d-none d-md-block">
                        <h3>Hogar y Muebles</h3>
                        <p>Renueva tu hogar con las mejores ofertas</p>
                        <a href="#" class="btn btn-danger">Descubrir</a>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#offersCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#offersCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </section>

    <!-- CATEGORÍAS POPULARES -->
    <section class="container my-5 ">
        <div class="row align-items-center mb-4 ">
            <div class="col">
                <h2 class="section-title">Categorías Populares</h2>
            </div>
            <div class="col-auto">
                <a href="#" class="btn btn-outline-primary btn-view-more">Ver más categorías →</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-6 col-md-3 text-center">
                <div class="category-circle">
                    <img src="./img/electronicos.jpg" alt="Electrónica" class="cursor-pointer hover:shadow-lg hover:scale-105 transition duration-300">
                </div>
                <div class="category-name">Electrónica</div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="category-circle">
                    <img src="./img/moda.jpg" alt="Moda" class="cursor-pointer hover:shadow-lg hover:scale-105 transition duration-300">
                </div>
                <div class="category-name">Moda</div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="category-circle">
                    <img src="./img/hogar.jpg" alt="Hogar" class="cursor-pointer hover:shadow-lg hover:scale-105 transition duration-300">
                </div>
                <div class="category-name">Hogar</div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="category-circle">
                    <img src="./img/deporte.jpg" alt="Deportes" class="cursor-pointer hover:shadow-lg hover:scale-105 transition duration-300">
                </div>
                <div class="category-name">Deportes</div>
            </div>
        </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <div class="container my-8">
        <h2 class="section-title">Productos Destacados</h2>
        <div id="productos-destacados" class="row g-4">
        </div>
    </div>
    <script src="./js/index.js"></script>
    <script src="./js/global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>