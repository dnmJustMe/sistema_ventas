<?php
$titulo = 'notJustPrint - Dashboard';
$menu_activo = 'dashboard'; // Define qué menú está activo

include_once 'plantillas/html_declaracion.inc.php';
?>

<div class="dashboard-body">
    <!-- Incluir la barra de navegación -->
    <?php include_once 'plantillas/navbar_dashboard.inc.php'; ?>

    <!-- Contenido principal -->
    <div class="dashboard-container">
        <!-- Contenido del dashboard -->
        <main class="dashboard-content">
            <div class="content-header">
                <h1>Dashboard</h1>
                <p>Resumen general del sistema</p>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <!-- Tarjeta 1 -->
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>152</h3>
                        <p>Ventas Hoy</p>
                    </div>
                </div>

                <!-- Tarjeta 2 -->
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3>45</h3>
                        <p>Productos Activos</p>
                    </div>
                </div>

                <!-- Tarjeta 3 -->
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$3,240</h3>
                        <p>Ingresos del Mes</p>
                    </div>
                </div>

                <!-- Tarjeta 4 -->
                <div class="stat-card">
                    <div class="stat-icon categories">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3>8</h3>
                        <p>Categorías</p>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <a href="<?php echo RUTA_PRODUCTOS ?>" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h4>Agregar Producto</h4>
                        <p>Crear nuevo producto en el catálogo</p>
                    </a>

                    <a href="<?php echo RUTA_CATEGORIAS ?>" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-folder-plus"></i>
                        </div>
                        <h4>Gestionar Categorías</h4>
                        <p>Administrar categorías de productos</p>
                    </a>

                    <a href="#" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Ver Reportes</h4>
                        <p>Análisis detallado de ventas</p>
                    </a>

                    <a href="#" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Clientes</h4>
                        <p>Gestionar base de clientes</p>
                    </a>
                </div>
            </div>

            <!-- Gráfico simple (placeholder) -->
            <div class="chart-section">
                <h2>Ventas de los últimos 7 días</h2>
                <div class="chart-placeholder">
                    <div class="chart-bars">
                        <div class="bar" style="height: 60%"></div>
                        <div class="bar" style="height: 80%"></div>
                        <div class="bar" style="height: 45%"></div>
                        <div class="bar" style="height: 90%"></div>
                        <div class="bar" style="height: 70%"></div>
                        <div class="bar" style="height: 85%"></div>
                        <div class="bar" style="height: 95%"></div>
                    </div>
                    <div class="chart-labels">
                        <span>Lun</span>
                        <span>Mar</span>
                        <span>Mié</span>
                        <span>Jue</span>
                        <span>Vie</span>
                        <span>Sáb</span>
                        <span>Dom</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
include_once 'plantillas/html_cierre.inc.php';
?>