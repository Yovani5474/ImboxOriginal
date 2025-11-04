<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>
            <i class="fas fa-box-open"></i>
            IMBOX Admin
        </h2>
    </div>
    
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo ($current_page ?? '') == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="menu-section">Gestión</div>
        
        <a href="clientes.php" class="menu-item <?php echo ($current_page ?? '') == 'clientes' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Clientes</span>
        </a>
        
        <a href="proveedores.php" class="menu-item <?php echo ($current_page ?? '') == 'proveedores' ? 'active' : ''; ?>">
            <i class="fas fa-truck"></i>
            <span>Proveedores</span>
        </a>
        
        <a href="empleados.php" class="menu-item <?php echo ($current_page ?? '') == 'empleados' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i>
            <span>Empleados</span>
        </a>
        
        <a href="deudas.php" class="menu-item <?php echo ($current_page ?? '') == 'deudas' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Deudas</span>
        </a>
        
        <div class="menu-section">Reportes</div>
        
        <a href="estadisticas.php" class="menu-item <?php echo ($current_page ?? '') == 'estadisticas' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Estadísticas</span>
        </a>
    </nav>
</aside>
