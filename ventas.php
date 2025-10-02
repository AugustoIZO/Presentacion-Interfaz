<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

// Obtener ventas con información del usuario
$sql = "SELECT v.*, u.NOMBRECOMPLETO as usuario_nombre 
        FROM VENTAS v 
        INNER JOIN USUARIOS u ON v.IDUSUARIO = u.IDUSUARIO 
        ORDER BY v.FECHAREGISTRO DESC 
        LIMIT 50";
$ventas = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Ventas - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1>💰 Ventas - Alisbook</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="main.php" style="color: white; text-decoration: none;">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <div class="tabla-container">
            <h2>Historial de Ventas</h2>
            
            <?php if (empty($ventas)): ?>
                <p>No hay ventas registradas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo Doc.</th>
                            <th>Número Doc.</th>
                            <th>Cliente</th>
                            <th>Documento Cliente</th>
                            <th>Total</th>
                            <th>Vendedor</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venta['IDVENTA']); ?></td>
                                <td><?php echo htmlspecialchars($venta['TIPODOCUMENTO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($venta['NUMERODOCUMENTO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($venta['NOMBRECLIENTE'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($venta['DOCUMENTOCLIENTE'] ?? 'N/A'); ?></td>
                                <td style="font-weight: bold; color: green;">
                                    $<?php echo number_format($venta['MONTOTOTAL'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($venta['usuario_nombre']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venta['FECHAREGISTRO'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; text-align: center;">
                    <?php 
                    $totalVentas = array_sum(array_column($ventas, 'MONTOTOTAL'));
                    ?>
                    <p><strong>Total mostrado: $<?php echo number_format($totalVentas, 2); ?></strong></p>
                    <p><em>Mostrando las últimas 50 ventas</em></p>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 20px; text-align: center;">
                <button onclick="location.href='main.php'" style="background: #354edb; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>
</body>
</html>