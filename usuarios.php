<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

// Solo administradores pueden acceder a este módulo
if ($user['rol'] !== 'Administrador') {
    header('Location: main.php');
    exit();
}

$mensaje = '';
$tipoMensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $documento = $_POST['documento'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $clave = $_POST['clave'] ?? '';
        $idrol = intval($_POST['idrol'] ?? 2);
        
        if (!empty($documento) && !empty($nombre) && !empty($clave) && $idrol > 0) {
            // Verificar si el documento ya existe
            $sqlCheck = "SELECT COUNT(*) as total FROM USUARIOS WHERE DOCUMENTO = ?";
            $count = $db->query($sqlCheck, [$documento])->fetch()['total'];
            
            if ($count > 0) {
                $mensaje = "El documento ya está registrado.";
                $tipoMensaje = 'error';
            } else {
                // Hashear la contraseña
                $claveHasheada = password_hash($clave, PASSWORD_DEFAULT);
                $sql = "INSERT INTO USUARIOS (DOCUMENTO, NOMBRECOMPLETO, CORREO, CLAVE, ESTADO, FECHAREGISTRO, IDROL) 
                        VALUES (?, ?, ?, ?, 'Activo', NOW(), ?)";
                if ($db->query($sql, [$documento, $nombre, $correo, $claveHasheada, $idrol])) {
                    $mensaje = "Usuario agregado exitosamente.";
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = "Error al agregar usuario.";
                    $tipoMensaje = 'error';
                }
            }
        } else {
            $mensaje = "Todos los campos obligatorios deben estar completos.";
            $tipoMensaje = 'error';
        }
    }
    
    if ($accion === 'editar') {
        $idUsuario = $_POST['id_usuario'] ?? '';
        $documento = $_POST['documento'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $clave = $_POST['clave'] ?? '';
        $idrol = intval($_POST['idrol'] ?? 2);
        
        if (!empty($idUsuario) && !empty($documento) && !empty($nombre) && $idrol > 0) {
            if (!empty($clave)) {
                // Hashear la nueva contraseña
                $claveHasheada = password_hash($clave, PASSWORD_DEFAULT);
                // Actualizar con nueva contraseña
                $sql = "UPDATE USUARIOS SET DOCUMENTO = ?, NOMBRECOMPLETO = ?, CORREO = ?, CLAVE = ?, IDROL = ? WHERE IDUSUARIO = ?";
                $params = [$documento, $nombre, $correo, $claveHasheada, $idrol, $idUsuario];
            } else {
                // Actualizar sin cambiar contraseña
                $sql = "UPDATE USUARIOS SET DOCUMENTO = ?, NOMBRECOMPLETO = ?, CORREO = ?, IDROL = ? WHERE IDUSUARIO = ?";
                $params = [$documento, $nombre, $correo, $idrol, $idUsuario];
            }
            
            if ($db->query($sql, $params)) {
                $mensaje = "Usuario actualizado exitosamente.";
                $tipoMensaje = 'success';
            } else {
                $mensaje = "Error al actualizar usuario.";
                $tipoMensaje = 'error';
            }
        }
    }
    
    if ($accion === 'eliminar') {
        $idUsuario = $_POST['id_usuario'] ?? '';
        
        if (!empty($idUsuario)) {
            // Cambiar estado a Inactivo en lugar de eliminar
            $sql = "UPDATE USUARIOS SET ESTADO = 'Inactivo' WHERE IDUSUARIO = ?";
            if ($db->query($sql, [$idUsuario])) {
                $mensaje = "Usuario desactivado exitosamente.";
                $tipoMensaje = 'success';
            } else {
                $mensaje = "Error al desactivar usuario.";
                $tipoMensaje = 'error';
            }
        }
    }
    
    if ($accion === 'activar') {
        $idUsuario = $_POST['id_usuario'] ?? '';
        
        if (!empty($idUsuario)) {
            $sql = "UPDATE USUARIOS SET ESTADO = 'Activo' WHERE IDUSUARIO = ?";
            if ($db->query($sql, [$idUsuario])) {
                $mensaje = "Usuario activado exitosamente.";
                $tipoMensaje = 'success';
            } else {
                $mensaje = "Error al activar usuario.";
                $tipoMensaje = 'error';
            }
        }
    }
}

// Obtener todos los roles disponibles
$sqlRoles = "SELECT * FROM ROLES ORDER BY DESCRIPCION";
$roles = $db->query($sqlRoles)->fetchAll();

// Obtener todos los usuarios (excluyendo administradores si es necesario, o mostrando todos)
$sql = "SELECT u.*, r.DESCRIPCION as ROL 
        FROM USUARIOS u 
        INNER JOIN ROLES r ON u.IDROL = r.IDROL 
        ORDER BY u.ESTADO DESC, u.NOMBRECOMPLETO";
$usuarios = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Gestión de Usuarios - Alisbook</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .form-group select {
            cursor: pointer;
            background-color: white;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #354edb;
            box-shadow: 0 0 0 2px rgba(53, 78, 219, 0.2);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #45a049;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: black;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .mensaje.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .acciones {
            display: flex;
            gap: 5px;
        }
        
        .estado-activo {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .estado-inactivo {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .btn-agregar {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        .btn-agregar:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">👥 Gestión de Usuarios - Alisbook</a></h1>
        <div class="header-nav">
            <a href="main.php">🏠 Inicio</a>
            <a href="perfil.php" style="color: white; text-decoration: none;" title="Ver mi perfil">
                👤 <?php echo htmlspecialchars($user['nombre']); ?>
            </a>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <div class="tabla-container">
            <h2>Gestión de Usuarios del Sistema</h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo $tipoMensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <button class="btn-agregar" onclick="abrirModalAgregar()">➕ Agregar Nuevo Usuario</button>
            
            <?php if (empty($usuarios)): ?>
                <p>No hay usuarios registrados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Documento</th>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['IDUSUARIO']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['DOCUMENTO']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['NOMBRECOMPLETO']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['CORREO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($usuario['ROL']); ?></td>
                                <td>
                                    <span class="estado-<?php echo strtolower($usuario['ESTADO']); ?>">
                                        <?php echo $usuario['ESTADO']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($usuario['FECHAREGISTRO'])); ?></td>
                                <td>
                                    <div class="acciones">
                                        <button class="btn-warning" onclick='abrirModalEditar(<?php echo json_encode($usuario); ?>)'>
                                            ✏️ Editar
                                        </button>
                                        <?php if ($usuario['ESTADO'] === 'Activo'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de desactivar este usuario?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['IDUSUARIO']; ?>">
                                                <button type="submit" class="btn-danger">❌ Desactivar</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="accion" value="activar">
                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['IDUSUARIO']; ?>">
                                                <button type="submit" class="btn-success">✅ Activar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
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

    <!-- Modal Agregar Usuario -->
    <div id="modalAgregar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalAgregar()">&times;</span>
            <h3>Agregar Nuevo Usuario</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="form-group">
                    <label for="documento">Documento (DNI) *</label>
                    <input type="text" id="documento" name="documento" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo">
                </div>
                
                <div class="form-group">
                    <label for="idrol">Rol *</label>
                    <select id="idrol" name="idrol" required>
                        <option value="">Seleccionar rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['IDROL']; ?>" <?php echo $rol['IDROL'] == 2 ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rol['DESCRIPCION']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        ℹ️ <strong>Administrador:</strong> acceso completo | <strong>Empleado:</strong> acceso limitado
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="clave">Contraseña *</label>
                    <input type="password" id="clave" name="clave" required>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn-primary">Guardar</button>
                    <button type="button" class="btn-secondary" onclick="cerrarModalAgregar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalEditar()">&times;</span>
            <h3>Editar Usuario</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" id="edit_id_usuario" name="id_usuario">
                
                <div class="form-group">
                    <label for="edit_documento">Documento (DNI) *</label>
                    <input type="text" id="edit_documento" name="documento" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_nombre">Nombre Completo *</label>
                    <input type="text" id="edit_nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_correo">Correo Electrónico</label>
                    <input type="email" id="edit_correo" name="correo">
                </div>
                
                <div class="form-group">
                    <label for="edit_idrol">Rol *</label>
                    <select id="edit_idrol" name="idrol" required>
                        <option value="">Seleccionar rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['IDROL']; ?>">
                                <?php echo htmlspecialchars($rol['DESCRIPCION']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        ℹ️ <strong>Administrador:</strong> acceso completo | <strong>Empleado:</strong> acceso limitado
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="edit_clave">Nueva Contraseña (dejar vacío para no cambiar)</label>
                    <input type="password" id="edit_clave" name="clave">
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn-primary">Actualizar</button>
                    <button type="button" class="btn-secondary" onclick="cerrarModalEditar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalAgregar() {
            document.getElementById('modalAgregar').style.display = 'block';
        }
        
        function cerrarModalAgregar() {
            document.getElementById('modalAgregar').style.display = 'none';
        }
        
        function abrirModalEditar(usuario) {
            document.getElementById('edit_id_usuario').value = usuario.IDUSUARIO;
            document.getElementById('edit_documento').value = usuario.DOCUMENTO;
            document.getElementById('edit_nombre').value = usuario.NOMBRECOMPLETO;
            document.getElementById('edit_correo').value = usuario.CORREO || '';
            document.getElementById('edit_idrol').value = usuario.IDROL;
            document.getElementById('edit_clave').value = '';
            document.getElementById('modalEditar').style.display = 'block';
        }
        
        function cerrarModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modalAgregar = document.getElementById('modalAgregar');
            const modalEditar = document.getElementById('modalEditar');
            if (event.target == modalAgregar) {
                cerrarModalAgregar();
            }
            if (event.target == modalEditar) {
                cerrarModalEditar();
            }
        }
    </script>
</body>
</html>
