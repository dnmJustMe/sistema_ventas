<?php
$titulo = 'notJustPrint - Inicio';

include_once 'plantillas/html_declaracion.inc.php';
?>

<div>
    <!-- Botón de administrador discreto -->
    <div class="admin-btn-container">
        <button class="btn-admin" onclick="location.href='<?php echo RUTA_LOGIN ?>'">
            <i class="fas fa-cog"></i>
        </button>
    </div>

    <!-- Banner principal -->
    <section class="hero-banner" style="background-image: url('<?php echo RUTA_IMG ?>banner.png');">
        <div class="overlay"></div>
        <div class="banner-content">
            <!-- Logo en lugar de texto -->
            <div class="logo-container">
                <img src="<?php echo RUTA_IMG ?>horizontal.png" alt="notJustPrint" class="brand-logo">
            </div>
            <p class="welcome-text">Bienvenido a tu solución creativa</p>
            <p class="slogan">No solo imprimimos, creamos experiencias</p>
            <button class="cta-button" onclick="scrollToServices()">Descubre nuestros servicios</button>
        </div>
    </section>

    <!-- Sección de servicios -->
    <section id="servicios" class="services-section">
        <div class="container">
            <h2 class="section-title">Nuestros Servicios</h2>
            <div class="row">
                <!-- Servicio 1 -->
                <div class="col-md-4 service-card">
                    <div class="card">
                        <div class="service-icon">
                            <i class="fas fa-print"></i>
                        </div>
                        <h3>Impresión Digital</h3>
                        <p>Impresiones de alta calidad en diferentes materiales y formatos.</p>
                        <button class="btn-ver-mas">Ver más</button>
                    </div>
                </div>
                <!-- Servicio 2 -->
                <div class="col-md-4 service-card">
                    <div class="card">
                        <div class="service-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h3>Sublimación</h3>
                        <p>Personalización de prendas y productos con diseños únicos.</p>
                        <button class="btn-ver-mas">Ver más</button>
                    </div>
                </div>
                <!-- Servicio 3 -->
                <div class="col-md-4 service-card">
                    <div class="card">
                        <div class="service-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3>Copias Audiovisuales</h3>
                        <p>Servicios de duplicación y edición de contenido multimedia.</p>
                        <button class="btn-ver-mas">Ver más</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de ofertas -->
    <section class="offers-section">
        <div class="container">
            <h2 class="section-title">Ofertas Especiales</h2>
            <div class="row">
                <!-- Oferta 1 -->
                <div class="col-md-6 offer-card">
                    <div class="card">
                        <div class="offer-badge">20% OFF</div>
                        <h3>Paquete Inicio de Negocio</h3>
                        <p>Tarjetas de presentación + volantes + banners por tiempo limitado.</p>
                        <button class="btn-ver-mas">Ver más</button>
                    </div>
                </div>
                <!-- Oferta 2 -->
                <div class="col-md-6 offer-card">
                    <div class="card">
                        <div class="offer-badge">15% OFF</div>
                        <h3>Sublimación Masiva</h3>
                        <p>Descuento en pedidos de más de 50 unidades del mismo producto.</p>
                        <button class="btn-ver-mas">Ver más</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <!-- Logo en el footer -->
                    <div class="footer-logo">
                        <img src="<?php echo RUTA_IMG ?>horizontal.png" alt="notJustPrint" class="brand-logo-footer">
                    </div>
                    <p class="footer-slogan">No solo imprimimos, creamos experiencias.</p>
                </div>
                <div class="col-md-4">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Dirección: [Tu dirección aquí]</p>
                    <p><i class="fas fa-phone"></i> Teléfono: [Tu teléfono]</p>
                    <p><i class="fas fa-envelope"></i> Email: [Tu email]</p>
                </div>
                <div class="col-md-4">
                    <h4>Síguenos</h4>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> notJustPrint. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        function scrollToServices() {
            document.getElementById('servicios').scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
</div>

<?php
include_once 'plantillas/html_cierre.inc.php';
?>