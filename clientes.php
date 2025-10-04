<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

// Obtener clientes
$sql = "SELECT * FROM CLIENTES WHERE ESTADO = 'Activo' ORDER BY NOMBRECOMPLETO";
$clientes = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Clientes - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">👥 Clientes - Alisbook</a></h1>
        <div class="header-nav">
            <a href="main.php">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <div class="tabla-container">
            <h2>Lista de Clientes</h2>
            
            <?php if (empty($clientes)): ?>
                <p>No hay clientes registrados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Documento</th>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['IDCLIENTE']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['DOCUMENTO']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['NOMBRECOMPLETO']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['CORREO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['TELEFONO'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="estado-<?php echo strtolower($cliente['ESTADO']); ?>">
                                        <?php echo $cliente['ESTADO']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($cliente['FECHAREGISTRO'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="contenedor-volver">
                <button onclick="location.href='main.php'" class="btn-volver-main">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>
</body>
</html>