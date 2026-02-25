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
$filtroProveedor = $_GET['proveedor'] ?? '';
$filtroDocumento = $_GET['documento'] ?? '';

// Construcción de la consulta con filtros
$whereClauses = [];
$params = [];

if (!empty($filtroFechaInicio)) {
    $whereClauses[] = "DATE(c.FECHAREGISTRO) >= ?";
    $params[] = $filtroFechaInicio;
}

if (!empty($filtroFechaFin)) {
    $whereClauses[] = "DATE(c.FECHAREGISTRO) <= ?";
    $params[] = $filtroFechaFin;
}

if (!empty($filtroProveedor)) {
    $whereClauses[] = "p.RAZONSOCIAL LIKE ?";
    $params[] = "%$filtroProveedor%";
}

if (!empty($filtroDocumento)) {
    $whereClauses[] = "c.NUMERODOCUMENTO LIKE ?";
    $params[] = "%$filtroDocumento%";
}

$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Determinar si hay filtros activos
$hayFiltros = !empty($filtroFechaInicio) || !empty($filtroFechaFin) || !empty($filtroProveedor) || !empty($filtroDocumento);

// Límite de resultados: 5 si no hay filtros, sin límite si hay filtros
$limit = $hayFiltros ? "" : "LIMIT 5";

// Obtener todas las compras con filtros
$sqlCompras = "SELECT c.*, p.RAZONSOCIAL as proveedor_nombre, u.NOMBRECOMPLETO as usuario_nombre, fp.TIPOPAGO
              FROM COMPRAS c 
              LEFT JOIN PROVEEDORES p ON c.IDPROVEEDOR = p.IDPROVEEDOR
              INNER JOIN USUARIOS u ON c.IDUSUARIO = u.IDUSUARIO
              LEFT JOIN FORMAS_PAGO fp ON c.IDFORMAPAGO = fp.IDFORMAPAGO
              $whereSQL
              ORDER BY c.FECHAREGISTRO DESC
              $limit";

$compras = $db->query($sqlCompras, $params)->fetchAll();

// Función para obtener detalles de una compra
function obtenerDetallesCompra($db, $idCompra) {
    $sql = "SELECT dc.*, p.NOMBRE as producto_nombre, p.CODIGO as producto_codigo, c.DESCRIPCION as categoria
            FROM DETALLECOMPRAS dc
            INNER JOIN PRODUCTOS p ON dc.IDPRODUCTO = p.IDPRODUCTO
            LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA
            WHERE dc.IDCOMPRA = ?
            ORDER BY p.NOMBRE";
    return $db->query($sql, [$idCompra])->fetchAll();
}

// Calcular estadísticas
$totalCompras = count($compras);
$montoTotalGeneral = array_sum(array_column($compras, 'MONTOTOTAL'));
$promedioCompra = $totalCompras > 0 ? $montoTotalGeneral / $totalCompras : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Detalles de Compras - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">🛒 Detalles de Compras - Alisbook</a></h1>
        <div class="header-nav">
            <a href="compras.php">📦 Nueva Compra</a>
            <a href="main.php">🏠 Inicio</a>
            <a href="perfil.php" style="color: white; text-decoration: none;" title="Ver mi perfil">
                👤 <?php echo htmlspecialchars($user['nombre']); ?>
            </a>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <!-- Filtros -->
        <div class="filtros-container">
            <h3 style="margin-top: 0;">🔍 Filtros de Búsqueda</h3>
            <?php if (!$hayFiltros): ?>
            <div class="alert alert-info" style="background-color: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                ℹ️ Mostrando las últimas 5 compras. Use los filtros para buscar compras específicas.
            </div>
            <?php endif; ?>
            <form method="GET" class="filtros-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div>
                    <label>Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($filtroFechaInicio); ?>" class="form-control">
                </div>
                <div>
                    <label>Fecha Fin:</label>
                    <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($filtroFechaFin); ?>" class="form-control">
                </div>
                <div>
                    <label>Proveedor:</label>
                    <input type="text" name="proveedor" placeholder="Buscar proveedor..." value="<?php echo htmlspecialchars($filtroProveedor); ?>" class="form-control">
                </div>
                <div>
                    <label>N° Documento:</label>
                    <input type="text" name="documento" placeholder="Buscar documento..." value="<?php echo htmlspecialchars($filtroDocumento); ?>" class="form-control">
                </div>
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                    <a href="detalles_compras.php" class="btn btn-secondary">🔄 Limpiar</a>
                </div>
            </form>
        </div>

        <!-- Estadísticas -->
        <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;">
                <h4 style="margin: 0 0 10px 0;">Total Compras</h4>
                <span style="font-size: 2em; font-weight: bold;"><?php echo $totalCompras; ?></span>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px;">
                <h4 style="margin: 0 0 10px 0;">Monto Total</h4>
                <span style="font-size: 2em; font-weight: bold;">$<?php echo number_format($montoTotalGeneral, 2); ?></span>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px;">
                <h4 style="margin: 0 0 10px 0;">Promedio por Compra</h4>
                <span style="font-size: 2em; font-weight: bold;">$<?php echo number_format($promedioCompra, 2); ?></span>
            </div>
        </div>

        <!-- Listado de Compras -->
        <div class="compras-list">
            <h2 style="margin-top: 0;">📋 Historial de Compras</h2>
            
            <?php if (empty($compras)): ?>
                <div class="alert alert-warning" style="background-color: #fff3cd; padding: 15px; border-radius: 5px; text-align: center;">
                    ⚠️ No se encontraron compras con los filtros aplicados.
                </div>
            <?php else: ?>
                <?php foreach ($compras as $compra): ?>
                    <?php $detalles = obtenerDetallesCompra($db, $compra['IDCOMPRA']); ?>
                    <div class="compra-item" onclick="toggleDetalles(<?php echo $compra['IDCOMPRA']; ?>)">
                        <div class="compra-header">
                            <div>
                                <h3 style="margin: 0 0 10px 0; color: #333;">
                                    📄 Compra #<?php echo htmlspecialchars($compra['NUMERODOCUMENTO']); ?>
                                </h3>
                                <div class="compra-info-grid">
                                    <div class="info-item">
                                        <label>Proveedor:</label>
                                        <span><?php echo htmlspecialchars($compra['proveedor_nombre'] ?? 'Sin proveedor'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Fecha:</label>
                                        <span><?php echo date('d/m/Y H:i', strtotime($compra['FECHAREGISTRO'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Registrado por:</label>
                                        <span><?php echo htmlspecialchars($compra['usuario_nombre']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Forma de Pago:</label>
                                        <span><?php echo htmlspecialchars($compra['TIPOPAGO'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Productos:</label>
                                        <span class="productos-badge"><?php echo count($detalles); ?> producto<?php echo count($detalles) > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="compra-total-section">
                                <div class="total-box">
                                    <p style="margin: 0; font-size: 0.9em; opacity: 0.9;">Total</p>
                                    <p style="margin: 5px 0 0 0; font-size: 1.8em; font-weight: bold;">
                                        $<?php echo number_format($compra['MONTOTOTAL'], 2); ?>
                                    </p>
                                </div>
                                <span class="toggle-icon" id="icon-<?php echo $compra['IDCOMPRA']; ?>">▼</span>
                            </div>
                        </div>

                        <!-- Detalles de productos (desplegable) -->
                        <div class="detalles-productos" id="detalles-<?php echo $compra['IDCOMPRA']; ?>" id="detalles-<?php echo $compra['IDCOMPRA']; ?>">
                            <h4 style="margin: 15px 0 10px 0; color: #555;">📦 Productos Comprados:</h4>
                            <?php if (empty($detalles)): ?>
                                <p style="color: #999; font-style: italic;">No hay detalles disponibles</p>
                            <?php else: ?>
                                <div style="overflow-x: auto;">
                                    <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                        <thead style="background-color: #f8f9fa;">
                                            <tr>
                                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Código</th>
                                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Producto</th>
                                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Categoría</th>
                                                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Cantidad</th>
                                                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">P. Compra</th>
                                                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">P. Venta</th>
                                                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($detalles as $detalle): ?>
                                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                                    <td style="padding: 12px;"><?php echo htmlspecialchars($detalle['producto_codigo'] ?? 'N/A'); ?></td>
                                                    <td style="padding: 12px; font-weight: 500;"><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                                                    <td style="padding: 12px;"><?php echo htmlspecialchars($detalle['categoria'] ?? 'Sin categoría'); ?></td>
                                                    <td style="padding: 12px; text-align: center;">
                                                        <span style="background-color: #e3f2fd; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                                                            <?php echo $detalle['CANTIDAD']; ?>
                                                        </span>
                                                    </td>
                                                    <td style="padding: 12px; text-align: right;">$<?php echo number_format($detalle['PRECIOCOMPRA'], 2); ?></td>
                                                    <td style="padding: 12px; text-align: right;">$<?php echo number_format($detalle['PRECIOVENTA'], 2); ?></td>
                                                    <td style="padding: 12px; text-align: right; font-weight: 600; color: #667eea;">
                                                        $<?php echo number_format($detalle['MONTOTOTAL'], 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot style="background-color: #f8f9fa; font-weight: bold;">
                                            <tr>
                                                <td colspan="6" style="padding: 12px; text-align: right; border-top: 2px solid #dee2e6;">TOTAL:</td>
                                                <td style="padding: 12px; text-align: right; border-top: 2px solid #dee2e6; color: #667eea; font-size: 1.1em;">
                                                    $<?php echo number_format($compra['MONTOTOTAL'], 2); ?>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <style>
        .filtros-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .compra-item {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .compra-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .compra-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 20px;
        }

        .compra-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .info-item {
            font-size: 0.9em;
        }

        .info-item label {
            font-weight: 600;
            color: #555;
            margin-right: 5px;
        }

        .info-item span {
            color: #666;
        }

        .productos-badge {
            background-color: #667eea;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85em;
        }

        .compra-total-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .total-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            min-width: 150px;
        }

        .toggle-icon {
            font-size: 1.5em;
            color: #667eea;
            transition: transform 0.3s;
            user-select: none;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        .detalles-productos {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
            margin-top: 0;
        }

        .detalles-productos.show {
            max-height: 2000px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        @media (max-width: 768px) {
            .filtros-form {
                grid-template-columns: 1fr !important;
            }

            .stats-container {
                grid-template-columns: 1fr !important;
            }

            table {
                font-size: 0.9em;
            }

            th, td {
                padding: 8px !important;
            }
        }
    </style>

    <script>
        function toggleDetalles(idCompra) {
            const detallesDiv = document.getElementById('detalles-' + idCompra);
            const icon = document.getElementById('icon-' + idCompra);
            
            detallesDiv.classList.toggle('show');
            icon.classList.toggle('rotated');
        }

        // Animación de entrada para las compras
        document.addEventListener('DOMContentLoaded', function() {
            const compraItems = document.querySelectorAll('.compra-item');
            compraItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Animación para las estadísticas
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
        });
    </script>
</body>
</html>
