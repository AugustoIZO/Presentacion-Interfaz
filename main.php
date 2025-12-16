<?php
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Requiere estar logueado

$user = $auth->getCurrentUser();

// Procesar logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Alisbook - Principal</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">📚 Alisbook</a></h1>
        <div class="header-nav">
            <span>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?> 
                (<?php echo ucfirst($user['rol']); ?>)</span>
            <a href="?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content dashboard">
        <div class="cards">
            <div class="main-card" onclick="location.href='inventario.php'">
                <h3>📦 Inventario</h3>
                <p>Gestionar productos y controlar stock</p>
            </div>
            <div class="main-card" onclick="location.href='clientes.php'">
                <h3>👥 Clientes</h3>
                <p>Administrar información de clientes</p>
            </div>
            <div class="main-card" onclick="location.href='ventas.php'">
                <h3>💰 Ventas</h3>
                <p>Registrar y gestionar ventas</p>
            </div>
            <div class="main-card" onclick="location.href='detalles_ventas.php'">
                <h3>📋 Detalles de Ventas</h3>
                <p>Ver historial y detalles de ventas</p>
            </div>
            <div class="main-card" onclick="location.href='compras.php'">
                <h3>🛒 Compras</h3>
                <p>Comprar productos a proveedores</p>
            </div>
            <div class="main-card" onclick="location.href='reportes.php'">
                <h3>📊 Reportes</h3>
                <p>Estadísticas y análisis de datos</p>
            </div>
            <?php if ($user['rol'] === 'Administrador'): ?>
            <div class="main-card" onclick="location.href='usuarios.php'">
                <h3>👤 Usuarios</h3>
                <p>Gestionar empleados del sistema</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="resumen">
            <h2>📈 Dashboard</h2>
            <?php
            require_once 'config/database.php';
            $db = new Database();
            
            // Obtener estadísticas
            $totalVentas = $db->query("SELECT COUNT(*) as total FROM VENTAS")->fetch()['total'];
            $clientesActivos = $db->query("SELECT COUNT(*) as total FROM CLIENTES WHERE ESTADO = 'Activo'")->fetch()['total'];
            $productosStock = $db->query("SELECT COUNT(*) as total FROM PRODUCTOS WHERE STOCK > 0 AND ESTADO = 'Activo'")->fetch()['total'];
            $ingresosMes = $db->query("SELECT COALESCE(SUM(MONTOTOTAL), 0) as total FROM VENTAS WHERE MONTH(FECHAREGISTRO) = MONTH(CURRENT_DATE()) AND YEAR(FECHAREGISTRO) = YEAR(CURRENT_DATE())")->fetch()['total'];
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Ventas</h4>
                    <span class="stat-number"><?php echo $totalVentas; ?></span>
                </div>
                <div class="stat-card">
                    <h4>Clientes Activos</h4>
                    <span class="stat-number"><?php echo $clientesActivos; ?></span>
                </div>
                <div class="stat-card">
                    <h4>Productos en Stock</h4>
                    <span class="stat-number"><?php echo $productosStock; ?></span>
                </div>
                <div class="stat-card">
                    <h4>Ingresos del Mes</h4>
                    <span class="stat-number">$<?php echo number_format($ingresosMes, 2); ?></span>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Animaciones
        document.querySelectorAll('.main-card[onclick]').forEach(card => {
            card.style.cursor = 'pointer';
            
            // Animación suave al hacer hover
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
            
            // Efecto al hacer click
            card.addEventListener('mousedown', function() {
                this.style.transform = 'translateY(-4px) scale(0.98)';
            });
            
            card.addEventListener('mouseup', function() {
                this.style.transform = 'translateY(-8px)';
            });
        });

        // Animación de entrada para las estadísticas
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animación para las cards principales
            const mainCards = document.querySelectorAll('.main-card');
            mainCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>