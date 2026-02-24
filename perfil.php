<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';
$tipomensaje = '';

// Obtener datos completos del usuario
$sql = "SELECT u.*, r.DESCRIPCION as ROL_DESCRIPCION 
        FROM USUARIOS u 
        INNER JOIN ROLES r ON u.IDROL = r.IDROL 
        WHERE u.IDUSUARIO = ?";
$datosUsuario = $db->query($sql, [$user['id']])->fetch();

// Procesar actualización de datos personales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos'])) {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        
        if (empty($nombre)) {
            throw new Exception('El nombre completo es obligatorio.');
        }
        
        if (empty($documento)) {
            throw new Exception('El documento es obligatorio.');
        }
        
        // Verificar si el documento ya existe en otro usuario
        if ($documento !== $datosUsuario['DOCUMENTO']) {
            $sqlCheck = "SELECT COUNT(*) as total FROM USUARIOS WHERE DOCUMENTO = ? AND IDUSUARIO != ?";
            $count = $db->query($sqlCheck, [$documento, $user['id']])->fetch()['total'];
            
            if ($count > 0) {
                throw new Exception('El documento ya está registrado para otro usuario.');
            }
        }
        
        // Actualizar datos
        $sqlUpdate = "UPDATE USUARIOS SET NOMBRECOMPLETO = ?, CORREO = ?, DOCUMENTO = ? WHERE IDUSUARIO = ?";
        $db->query($sqlUpdate, [$nombre, $correo, $documento, $user['id']]);
        
        // Actualizar sesión
        $_SESSION['nombre'] = $nombre;
        $_SESSION['documento'] = $documento;
        
        // Recargar datos
        $datosUsuario = $db->query($sql, [$user['id']])->fetch();
        
        $mensaje = "Datos actualizados exitosamente.";
        $tipomensaje = 'success';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $tipomensaje = 'error';
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    try {
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirm = $_POST['password_confirmar'] ?? '';
        
        if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirm)) {
            throw new Exception('Todos los campos de contraseña son obligatorios.');
        }
        
        // Verificar contraseña actual
        $sqlPassword = "SELECT CLAVE FROM USUARIOS WHERE IDUSUARIO = ?";
        $claveActual = $db->query($sqlPassword, [$user['id']])->fetch()['CLAVE'];
        
        if (!password_verify($passwordActual, $claveActual)) {
            throw new Exception('La contraseña actual es incorrecta.');
        }
        
        // Verificar que las nuevas contraseñas coincidan
        if ($passwordNueva !== $passwordConfirm) {
            throw new Exception('Las contraseñas nuevas no coinciden.');
        }
        
        // Verificar longitud mínima
        if (strlen($passwordNueva) < 4) {
            throw new Exception('La contraseña debe tener al menos 4 caracteres.');
        }
        
        // Actualizar contraseña
        $passwordHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $sqlUpdatePass = "UPDATE USUARIOS SET CLAVE = ? WHERE IDUSUARIO = ?";
        $db->query($sqlUpdatePass, [$passwordHash, $user['id']]);
        
        $mensaje = "Contraseña actualizada exitosamente.";
        $tipomensaje = 'success';
        
        // Limpiar campos
        $_POST = [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $tipomensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Mi Perfil - Alisbook</title>
    <style>
        .perfil-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .perfil-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .perfil-card h3 {
            color: #354edb;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .perfil-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #354edb 0%, #5f78ff 100%);
            border-radius: 10px;
            color: white;
        }
        
        .perfil-avatar {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: #354edb;
            flex-shrink: 0;
        }
        
        .perfil-info-header {
            flex: 1;
        }
        
        .perfil-info-header h2 {
            margin: 0 0 5px 0;
            font-size: 1.8rem;
        }
        
        .perfil-info-header .rol-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #354edb;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #354edb;
            box-shadow: 0 0 0 2px rgba(53, 78, 219, 0.2);
        }
        
        .form-group input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-actualizar {
            background: #354edb;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-actualizar:hover {
            background: #2b3db8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(53, 78, 219, 0.3);
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #354edb;
        }
        
        .info-item label {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item .value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
        
        .password-requirements {
            background: #e8f4f8;
            border-left: 4px solid #4a90e2;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .password-requirements ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin: 3px 0;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .perfil-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">👤 Mi Perfil - Alisbook</a></h1>
        <div class="header-nav">
            <a href="main.php">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <div class="perfil-container">
            <!-- Cabecera del perfil -->
            <div class="perfil-header">
                <div class="perfil-avatar">
                    <?php echo strtoupper(substr($datosUsuario['NOMBRECOMPLETO'], 0, 1)); ?>
                </div>
                <div class="perfil-info-header">
                    <h2><?php echo htmlspecialchars($datosUsuario['NOMBRECOMPLETO']); ?></h2>
                    <div class="rol-badge">
                        <?php echo $datosUsuario['ROL_DESCRIPCION'] === 'Administrador' ? '👑 Administrador' : '👤 Empleado'; ?>
                    </div>
                </div>
            </div>

            <?php if ($mensaje): ?>
                <div class="mensaje-<?php echo $tipomensaje === 'success' ? 'exito' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mensaje-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Información de la cuenta -->
            <div class="perfil-card">
                <h3>📋 Información de la Cuenta</h3>
                <div class="info-item">
                    <label>Fecha de Registro</label>
                    <div class="value"><?php echo date('d/m/Y H:i', strtotime($datosUsuario['FECHAREGISTRO'])); ?></div>
                </div>
                <div class="info-item">
                    <label>Estado de la Cuenta</label>
                    <div class="value">
                        <span class="estado-<?php echo strtolower($datosUsuario['ESTADO']); ?>">
                            <?php echo $datosUsuario['ESTADO']; ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <label>Rol del Sistema</label>
                    <div class="value"><?php echo htmlspecialchars($datosUsuario['ROL_DESCRIPCION']); ?></div>
                </div>
            </div>

            <!-- Formulario de editar datos personales -->
            <div class="perfil-card">
                <h3>✏️ Editar Datos Personales</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo *</label>
                            <input type="text" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($datosUsuario['NOMBRECOMPLETO']); ?>" 
                                   required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="documento">Documento / DNI *</label>
                            <input type="text" id="documento" name="documento" 
                                   value="<?php echo htmlspecialchars($datosUsuario['DOCUMENTO']); ?>" 
                                   required maxlength="20">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" 
                               value="<?php echo htmlspecialchars($datosUsuario['CORREO'] ?? ''); ?>" 
                               maxlength="100">
                    </div>
                    
                    <button type="submit" name="actualizar_datos" class="btn-actualizar">
                        💾 Guardar Cambios
                    </button>
                </form>
            </div>

            <!-- Formulario de cambio de contraseña -->
            <div class="perfil-card">
                <h3>🔒 Cambiar Contraseña</h3>
                
                <div class="password-requirements">
                    <strong>ℹ️ Requisitos de contraseña:</strong>
                    <ul>
                        <li>Mínimo 4 caracteres</li>
                        <li>Debe ingresar su contraseña actual</li>
                        <li>La nueva contraseña debe coincidir en ambos campos</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="password_actual">Contraseña Actual *</label>
                        <input type="password" id="password_actual" name="password_actual" 
                               required placeholder="Ingrese su contraseña actual">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password_nueva">Nueva Contraseña *</label>
                            <input type="password" id="password_nueva" name="password_nueva" 
                                   required placeholder="Mínimo 4 caracteres" minlength="4">
                        </div>
                        <div class="form-group">
                            <label for="password_confirmar">Confirmar Nueva Contraseña *</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" 
                                   required placeholder="Repita la nueva contraseña" minlength="4">
                        </div>
                    </div>
                    
                    <button type="submit" name="cambiar_password" class="btn-actualizar">
                        🔐 Cambiar Contraseña
                    </button>
                </form>
            </div>

            <div class="contenedor-volver">
                <button onclick="location.href='main.php'" class="btn-volver-main">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>

    <script>
        // Validación en tiempo real de contraseñas
        const passwordNueva = document.getElementById('password_nueva');
        const passwordConfirmar = document.getElementById('password_confirmar');
        
        function validarPasswords() {
            if (passwordNueva.value && passwordConfirmar.value) {
                if (passwordNueva.value !== passwordConfirmar.value) {
                    passwordConfirmar.setCustomValidity('Las contraseñas no coinciden');
                } else {
                    passwordConfirmar.setCustomValidity('');
                }
            }
        }
        
        passwordNueva.addEventListener('input', validarPasswords);
        passwordConfirmar.addEventListener('input', validarPasswords);
    </script>
</body>
</html>
