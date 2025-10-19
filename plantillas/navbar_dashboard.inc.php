<?php
// navbar_dashboard.inc.php
?>
<!-- Barra de navegación superior -->
<nav class="admin-navbar">
    <div class="nav-container">
        <!-- Logo y toggle móvil -->
        <div style="display: flex; align-items: center;">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-logo">
                <img src="<?php echo RUTA_IMG ?>horizontal.png" alt="notJustPrint" class="brand-logo-nav">
            </div>
        </div>
        
        <!-- Menú de usuario -->
        <div class="nav-user">
            <span class="user-welcome">Bienvenido, Admin</span>
            <a href="<?php echo RUTA_LOGOUT ?>" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <ul class="sidebar-menu">
        <li class="menu-item <?php echo ($menu_activo === 'dashboard') ? 'active' : ''; ?>">
            <a href="<?php echo RUTA_DASHBOARD ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item <?php echo ($menu_activo === 'productos') ? 'active' : ''; ?>">
            <a href="<?php echo RUTA_PRODUCTOS ?>">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($menu_activo === 'categorias') ? 'active' : ''; ?>">
            <a href="<?php echo RUTA_CATEGORIAS ?>">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo ($menu_activo === 'servicios') ? 'active' : ''; ?>">
            <a href="<?php echo RUTA_SERVICIOS ?>">
                <i class="fas fa-tags"></i>
                <span>Servicios</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo ($menu_activo === 'ofertas') ? 'active' : ''; ?>">
            <a href="<?php echo RUTA_OFERTAS ?>">
                <i class="fas fa-tags"></i>
                <span>Ofertas</span>
            </a>
        </li>
        
    </ul>
</aside>

<script>
// Toggle del menú móvil
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    
    if (mobileMenuToggle && adminSidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('mobile-open');
        });
        
        // Mostrar toggle en móvil
        if (window.innerWidth <= 768) {
            mobileMenuToggle.style.display = 'block';
        }
        
        // Actualizar en resize
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                mobileMenuToggle.style.display = 'block';
            } else {
                mobileMenuToggle.style.display = 'none';
                adminSidebar.classList.remove('mobile-open');
            }
        });
    }
});
</script>