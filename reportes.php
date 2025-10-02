<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

// Obtener estadísticas para reportes
$stats = [];

// Total de ventas
$stats['total_ventas'] = $db->query("SELECT COUNT(*) as total FROM VENTAS")->fetch()['total'];

// Total de ingresos
$stats['total_ingresos'] = $db->query("SELECT COALESCE(SUM(MONTOTOTAL), 0) as total FROM VENTAS")->fetch()['total'];

// Productos más vendidos
$productosVendidos = $db->query("
    SELECT p.NOMBRE, SUM(dv.CANTIDAD) as total_vendido, SUM(dv.SUBTOTAL) as total_ingresos
    FROM DETALLEVENTAS dv 
    INNER JOIN PRODUCTOS p ON dv.IDPRODUCTO = p.IDPRODUCTO 
    GROUP BY p.IDPRODUCTO, p.NOMBRE 
    ORDER BY total_vendido DESC 
    LIMIT 10
")->fetchAll();

// Ventas por mes (últimos 6 meses)
$ventasPorMes = $db->query("
    SELECT 
        DATE_FORMAT(FECHAREGISTRO, '%Y-%m') as mes,
        COUNT(*) as cantidad_ventas,
        SUM(MONTOTOTAL) as total_ingresos
    FROM VENTAS 
    WHERE FECHAREGISTRO >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(FECHAREGISTRO, '%Y-%m')
    ORDER BY mes DESC
")->fetchAll();

// Productos con stock bajo
$stockBajo = $db->query("
    SELECT NOMBRE, STOCK, CODIGO 
    FROM PRODUCTOS 
    WHERE STOCK < 10 AND ESTADO = 'Activo' 
    ORDER BY STOCK ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Reportes - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1>📊 Reportes - Alisbook</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="main.php" style="color: white; text-decoration: none;">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <!-- Estadísticas generales -->
        <div class="resumen">
            <h2>Estadísticas Generales</h2>
            <div class="cards">
                <div class="card">
                    <h4>Total Ventas</h4>
                    <span class="stat-number"><?php echo $stats['total_ventas']; ?></span>
                </div>
                <div class="card">
                    <h4>Ingresos Totales</h4>
                    <span class="stat-number">$<?php echo number_format($stats['total_ingresos'], 2); ?></span>
                </div>
                <div class="card">
                    <h4>Promedio por Venta</h4>
                    <span class="stat-number">
                        $<?php echo $stats['total_ventas'] > 0 ? number_format($stats['total_ingresos'] / $stats['total_ventas'], 2) : '0.00'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Productos más vendidos -->
        <div class="tabla-container">
            <h3>Productos Más Vendidos</h3>
            <?php if (!empty($productosVendidos)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad Vendida</th>
                            <th>Ingresos Generados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productosVendidos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['NOMBRE']); ?></td>
                                <td><?php echo $producto['total_vendido']; ?></td>
                                <td>$<?php echo number_format($producto['total_ingresos'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay datos de ventas disponibles.</p>
            <?php endif; ?>
        </div>

        <!-- Ventas por mes -->
        <div class="tabla-container">
            <h3>Ventas por Mes (Últimos 6 meses)</h3>
            <?php if (!empty($ventasPorMes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Cantidad de Ventas</th>
                            <th>Total Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventasPorMes as $mes): ?>
                            <tr>
                                <td><?php echo date('F Y', strtotime($mes['mes'] . '-01')); ?></td>
                                <td><?php echo $mes['cantidad_ventas']; ?></td>
                                <td>$<?php echo number_format($mes['total_ingresos'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay datos de ventas mensuales.</p>
            <?php endif; ?>
        </div>

        <!-- Stock bajo -->
        <div class="tabla-container">
            <h3>⚠️ Productos con Stock Bajo (menos de 10 unidades)</h3>
            <?php if (!empty($stockBajo)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Stock Actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockBajo as $producto): ?>
                            <tr style="background-color: <?php echo $producto['STOCK'] == 0 ? '#ffebee' : '#fff3e0'; ?>">
                                <td><?php echo htmlspecialchars($producto['CODIGO']); ?></td>
                                <td><?php echo htmlspecialchars($producto['NOMBRE']); ?></td>
                                <td style="color: <?php echo $producto['STOCK'] == 0 ? 'red' : 'orange'; ?>; font-weight: bold;">
                                    <?php echo $producto['STOCK']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: green;">✓ Todos los productos tienen stock suficiente.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <button onclick="location.href='main.php'" style="background: #354edb; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Volver al Menú Principal
            </button>
        </div>
    </main>
</body>
</html>