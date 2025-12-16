<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

// Filtros
$filtroFechaInicio = $_GET['fecha_inicio'] ?? '';
$filtroFechaFin = $_GET['fecha_fin'] ?? '';
$filtroCliente = $_GET['cliente'] ?? '';
$filtroDocumento = $_GET['documento'] ?? '';

// Construcción de la consulta con filtros
$whereClauses = [];
$params = [];

if (!empty($filtroFechaInicio)) {
    $whereClauses[] = "DATE(v.FECHAREGISTRO) >= ?";
    $params[] = $filtroFechaInicio;
}

if (!empty($filtroFechaFin)) {
    $whereClauses[] = "DATE(v.FECHAREGISTRO) <= ?";
    $params[] = $filtroFechaFin;
}

if (!empty($filtroCliente)) {
    $whereClauses[] = "v.NOMBRECLIENTE LIKE ?";
    $params[] = "%$filtroCliente%";
}

if (!empty($filtroDocumento)) {
    $whereClauses[] = "v.NUMERODOCUMENTO LIKE ?";
    $params[] = "%$filtroDocumento%";
}

$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Determinar si hay filtros activos
$hayFiltros = !empty($filtroFechaInicio) || !empty($filtroFechaFin) || !empty($filtroCliente) || !empty($filtroDocumento);

// Límite de resultados: 5 si no hay filtros, sin límite si hay filtros
$limit = $hayFiltros ? "" : "LIMIT 5";

// Obtener todas las ventas con filtros
$sqlVentas = "SELECT v.*, u.NOMBRECOMPLETO as usuario_nombre
              FROM VENTAS v 
              INNER JOIN USUARIOS u ON v.IDUSUARIO = u.IDUSUARIO 
              $whereSQL
              ORDER BY v.FECHAREGISTRO DESC
              $limit";

$ventas = $db->query($sqlVentas, $params)->fetchAll();

// Función para obtener detalles de una venta
function obtenerDetallesVenta($db, $idVenta) {
    $sql = "SELECT dv.*, p.NOMBRE as producto_nombre, p.CODIGO as producto_codigo, c.DESCRIPCION as categoria
            FROM DETALLEVENTAS dv
            INNER JOIN PRODUCTOS p ON dv.IDPRODUCTO = p.IDPRODUCTO
            LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA
            WHERE dv.IDVENTA = ?
            ORDER BY p.NOMBRE";
    return $db->query($sql, [$idVenta])->fetchAll();
}

// Calcular estadísticas
$totalVentas = count($ventas);
$montoTotalGeneral = array_sum(array_column($ventas, 'MONTOTOTAL'));
$promedioVenta = $totalVentas > 0 ? $montoTotalGeneral / $totalVentas : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Detalles de Ventas - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">📋 Detalles de Ventas - Alisbook</a></h1>
        <div class="header-nav">
            <a href="ventas.php">💰 Nueva Venta</a>
            <a href="main.php">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <!-- Filtros -->
        <div class="filtros-container">
            <h3 style="margin-top: 0;">🔍 Filtros de Búsqueda</h3>
            <?php if (!$hayFiltros): ?>
                <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    ℹ️ Mostrando las últimas 5 ventas. Usa los filtros para buscar ventas específicas.
                </p>
            <?php endif; ?>
            <form method="GET" class="filtros-form">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo htmlspecialchars($filtroFechaInicio); ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo htmlspecialchars($filtroFechaFin); ?>">
                </div>
                
                <div class="form-group">
                    <label for="cliente">Cliente:</label>
                    <input type="text" name="cliente" id="cliente" placeholder="Nombre del cliente" value="<?php echo htmlspecialchars($filtroCliente); ?>">
                </div>
                
                <div class="form-group">
                    <label for="documento">N° Documento:</label>
                    <input type="text" name="documento" id="documento" placeholder="Ej: V-000001" value="<?php echo htmlspecialchars($filtroDocumento); ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-filtrar">Buscar</button>
                </div>
                
                <div class="form-group">
                    <a href="detalles_ventas.php" class="btn-limpiar" style="display: inline-block; text-align: center; text-decoration: none; line-height: 38px; padding: 0 20px;">Limpiar</a>
                </div>
            </form>
        </div>
        
        <!-- Estadísticas -->
        <?php if ($totalVentas > 0): ?>
        <div class="estadisticas">
            <div class="stat-box">
                <h3>Total de Ventas</h3>
                <p class="numero"><?php echo $totalVentas; ?></p>
            </div>
            <div class="stat-box">
                <h3>Monto Total</h3>
                <p class="numero">$<?php echo number_format($montoTotalGeneral, 2); ?></p>
            </div>
            <div class="stat-box">
                <h3>Promedio por Venta</h3>
                <p class="numero">$<?php echo number_format($promedioVenta, 2); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Listado de Ventas -->
        <div class="ventas-container">
            <?php if (empty($ventas)): ?>
                <div class="no-ventas">
                    <h3>📭 No se encontraron ventas</h3>
                    <p>No hay ventas que coincidan con los filtros aplicados.</p>
                    <?php if (!empty($filtroFechaInicio) || !empty($filtroFechaFin) || !empty($filtroCliente) || !empty($filtroDocumento)): ?>
                        <a href="detalles_ventas.php" style="color: #007bff; text-decoration: none; font-weight: 600;">Ver todas las ventas</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($ventas as $venta): ?>
                    <div class="venta-item" onclick="toggleDetalles(<?php echo $venta['IDVENTA']; ?>)">
                        <div class="venta-header">
                            <div>
                                <h3 style="margin: 0 0 5px 0;">
                                    Venta #<?php echo $venta['IDVENTA']; ?> 
                                    <span class="badge badge-<?php echo strtolower($venta['TIPODOCUMENTO'] ?? 'ticket'); ?>">
                                        <?php echo htmlspecialchars($venta['TIPODOCUMENTO'] ?? 'N/A'); ?>
                                    </span>
                                </h3>
                                <small style="color: #666;"><?php echo htmlspecialchars($venta['NUMERODOCUMENTO']); ?></small>
                            </div>
                            <div class="venta-total">
                                $<?php echo number_format($venta['MONTOTOTAL'], 2); ?>
                                <span class="toggle-icon" id="icon-<?php echo $venta['IDVENTA']; ?>">▼</span>
                            </div>
                        </div>
                        
                        <div class="venta-info">
                            <div class="venta-info-item">
                                <label>Cliente:</label>
                                <span><?php echo htmlspecialchars($venta['NOMBRECLIENTE']); ?></span>
                            </div>
                            <div class="venta-info-item">
                                <label>Documento Cliente:</label>
                                <span><?php echo htmlspecialchars($venta['DOCUMENTOCLIENTE'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="venta-info-item">
                                <label>Vendedor:</label>
                                <span><?php echo htmlspecialchars($venta['usuario_nombre']); ?></span>
                            </div>
                            <div class="venta-info-item">
                                <label>Fecha:</label>
                                <span><?php echo date('d/m/Y H:i', strtotime($venta['FECHAREGISTRO'])); ?></span>
                            </div>
                            <div class="venta-info-item">
                                <label>Monto Pagado:</label>
                                <span>$<?php echo number_format($venta['MONTOPAGO'], 2); ?></span>
                            </div>
                            <div class="venta-info-item">
                                <label>Cambio:</label>
                                <span>$<?php echo number_format($venta['MONTOCAMBIO'], 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="detalles-productos" id="detalles-<?php echo $venta['IDVENTA']; ?>">
                            <h4>📦 Productos Vendidos</h4>
                            <?php 
                            $detalles = obtenerDetallesVenta($db, $venta['IDVENTA']);
                            if (!empty($detalles)): 
                            ?>
                                <table class="detalles-table">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Precio Unit.</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalles as $detalle): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($detalle['producto_codigo'] ?? 'N/A'); ?></td>
                                                <td><strong><?php echo htmlspecialchars($detalle['producto_nombre']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($detalle['categoria'] ?? 'N/A'); ?></td>
                                                <td>$<?php echo number_format($detalle['PRECIOVENTA'], 2); ?></td>
                                                <td><?php echo $detalle['CANTIDAD']; ?></td>
                                                <td><strong>$<?php echo number_format($detalle['SUBTOTAL'], 2); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No se encontraron detalles para esta venta.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="contenedor-volver" style="margin-top: 20px;">
            <button onclick="location.href='main.php'" class="btn-volver-main">
                Volver al Menú Principal
            </button>
        </div>
    </main>

    <script>
        function toggleDetalles(idVenta) {
            const detallesDiv = document.getElementById('detalles-' + idVenta);
            const icon = document.getElementById('icon-' + idVenta);
            
            detallesDiv.classList.toggle('show');
            icon.classList.toggle('rotated');
        }
        
        // Establecer fecha de hoy por defecto si no hay filtros
        window.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            // Si ambos campos están vacíos, no establecer fechas por defecto
            // Esto permite ver todas las ventas sin restricción
        });
    </script>
</body>
</html>
