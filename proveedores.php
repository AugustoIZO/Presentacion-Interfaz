<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';

// Procesar nuevo proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_proveedor'])) {
    try {
        $documento = trim($_POST['documento'] ?? '');
        $razonsocial = trim($_POST['razonsocial'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        // Validaciones
        if (empty($documento)) {
            throw new Exception('El documento es obligatorio.');
        }
        
        if (empty($razonsocial)) {
            throw new Exception('La razón social es obligatoria.');
        }
        
        // Verificar si el documento ya existe
        $sqlVerificar = "SELECT COUNT(*) as total FROM PROVEEDORES WHERE DOCUMENTO = ?";
        $existe = $db->query($sqlVerificar, [$documento])->fetch();
        
        if ($existe['total'] > 0) {
            throw new Exception('Ya existe un proveedor con ese documento.');
        }
        
        // Insertar proveedor
        $sqlInsert = "INSERT INTO PROVEEDORES (DOCUMENTO, RAZONSOCIAL, CORREO, TELEFONO, ESTADO, FECHAREGISTRO) 
                     VALUES (?, ?, ?, ?, 'Activo', NOW())";
        
        $db->query($sqlInsert, [
            $documento,
            $razonsocial,
            $correo,
            $telefono
        ]);
        
        $mensaje = "Proveedor registrado exitosamente: " . htmlspecialchars($razonsocial);
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener proveedores
$sql = "SELECT * FROM PROVEEDORES WHERE ESTADO = 'Activo' ORDER BY RAZONSOCIAL";
$proveedores = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Proveedores - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">🏭 Proveedores - Alisbook</a></h1>
        <div class="header-nav">
            <a href="main.php">🏠 Inicio</a>
            <a href="perfil.php" style="color: white; text-decoration: none;" title="Ver mi perfil">
                👤 <?php echo htmlspecialchars($user['nombre']); ?>
            </a>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <?php if ($mensaje): ?>
            <div class="mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Formulario de Nuevo Proveedor -->
        <div class="form-container">
            <h2>Registrar Nuevo Proveedor</h2>
            <p style="margin-bottom: 20px; color: #666; background: #e8f4f8; padding: 12px; border-radius: 5px; border-left: 4px solid #354edb;">
                ℹ️ <strong>Información:</strong> Los proveedores registrados aquí estarán disponibles automáticamente en el módulo de <strong>Compras</strong> para realizar pedidos.
            </p>
            <form method="POST" class="form-registro">
                <div class="form-row">
                    <div class="form-group">
                        <label for="documento">Documento / RUC: *</label>
                        <input type="text" id="documento" name="documento" 
                               value="<?php echo htmlspecialchars($_POST['documento'] ?? ''); ?>" 
                               required maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="razonsocial">Razón Social: *</label>
                        <input type="text" id="razonsocial" name="razonsocial" 
                               value="<?php echo htmlspecialchars($_POST['razonsocial'] ?? ''); ?>" 
                               required maxlength="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" 
                               value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" 
                               maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" 
                               value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>" 
                               maxlength="20">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="registrar_proveedor" class="btn-primary">
                        ✅ Registrar Proveedor
                    </button>
                </div>
            </form>
        </div>
        
        <div class="tabla-container">
            <h2>Lista de Proveedores (<?php echo count($proveedores); ?> activos)</h2>
            
            <?php if (empty($proveedores)): ?>
                <p>No hay proveedores registrados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Documento</th>
                            <th>Razón Social</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($proveedor['IDPROVEEDOR']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['DOCUMENTO']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['RAZONSOCIAL']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['CORREO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['TELEFONO'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="estado-<?php echo strtolower($proveedor['ESTADO']); ?>">
                                        <?php echo $proveedor['ESTADO']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($proveedor['FECHAREGISTRO'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="contenedor-volver">
                <button onclick="location.href='compras.php'" class="btn-primary" style="margin-right: 10px;">
                    🛒 Ir a Compras
                </button>
                <button onclick="location.href='main.php'" class="btn-volver-main">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>
</body>
</html>
