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
        <h1>📚 Alisbook</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?> 
                (<?php echo ucfirst($user['rol']); ?>)</span>
            <a href="?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <div class="cards">
            <div class="card" onclick="location.href='inventario.php'">
                <h3>📦 Inventario</h3>
                <p>Gestionar productos y stock</p>
            </div>
            <div class="card" onclick="location.href='clientes.php'">
                <h3>👥 Clientes</h3>
                <p>Administrar clientes</p>
            </div>
            <div class="card" onclick="location.href='ventas.php'">
                <h3>💰 Ventas</h3>
                <p>Registrar y ver ventas</p>
            </div>
            <div class="card" onclick="location.href='reportes.php'">
                <h3>📊 Reportes</h3>
                <p>Estadísticas y reportes</p>
            </div>
        </div>

        <div class="resumen">
            <h2>Resumen</h2>
            <?php
            require_once 'config/database.php';
            $db = new Database();
            
            // Obtener estadísticas
            $totalVentas = $db->query("SELECT COUNT(*) as total FROM VENTAS")->fetch()['total'];
            $clientesActivos = $db->query("SELECT COUNT(*) as total FROM CLIENTES WHERE ESTADO = 'Activo'")->fetch()['total'];
            $productosStock = $db->query("SELECT COUNT(*) as total FROM PRODUCTOS WHERE STOCK > 0 AND ESTADO = 'Activo'")->fetch()['total'];
            $ingresosMes = $db->query("SELECT COALESCE(SUM(MONTOTOTAL), 0) as total FROM VENTAS WHERE MONTH(FECHAREGISTRO) = MONTH(CURRENT_DATE()) AND YEAR(FECHAREGISTRO) = YEAR(CURRENT_DATE())")->fetch()['total'];
            ?>
            
            <div class="card">
                <h4>Total Ventas</h4>
                <span class="stat-number"><?php echo $totalVentas; ?></span>
            </div>
            <div class="card">
                <h4>Clientes Activos</h4>
                <span class="stat-number"><?php echo $clientesActivos; ?></span>
            </div>
            <div class="card">
                <h4>Productos en Stock</h4>
                <span class="stat-number"><?php echo $productosStock; ?></span>
            </div>
            <div class="card">
                <h4>Ingresos Mensuales</h4>
                <span class="stat-number">$<?php echo number_format($ingresosMes, 2); ?></span>
            </div>
        </div>
    </main>

    <script>
        // Hacer las cards clickeables
        document.querySelectorAll('.card[onclick]').forEach(card => {
            card.style.cursor = 'pointer';
            card.style.transition = 'transform 0.2s';
            
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>