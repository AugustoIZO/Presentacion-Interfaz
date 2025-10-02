<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

// Obtener productos con sus categorías
$sql = "SELECT p.*, c.DESCRIPCION as categoria_nombre 
        FROM PRODUCTOS p 
        LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
        WHERE p.ESTADO = 'Activo' 
        ORDER BY p.NOMBRE";
$productos = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Inventario - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1>📦 Inventario - Alisbook</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="main.php" style="color: white; text-decoration: none;">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <div class="tabla-container">
            <h2>Lista de Productos</h2>
            
            <?php if (empty($productos)): ?>
                <p>No hay productos registrados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['IDPRODUCTO']); ?></td>
                                <td><?php echo htmlspecialchars($producto['CODIGO']); ?></td>
                                <td><?php echo htmlspecialchars($producto['NOMBRE']); ?></td>
                                <td><?php echo htmlspecialchars($producto['DESCRIPCION'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                <td>$<?php echo number_format($producto['PRECIOCOMPRA'], 2); ?></td>
                                <td>$<?php echo number_format($producto['PRECIOVENTA'], 2); ?></td>
                                <td>
                                    <span style="color: <?php echo $producto['STOCK'] > 5 ? 'green' : ($producto['STOCK'] > 0 ? 'orange' : 'red'); ?>">
                                        <?php echo $producto['STOCK']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($producto['STOCK'] > 0): ?>
                                        <span style="color: green;">✓ Disponible</span>
                                    <?php else: ?>
                                        <span style="color: red;">✗ Agotado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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