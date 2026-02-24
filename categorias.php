<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$tipoMensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (!empty($descripcion)) {
            try {
                // Verificar si la categoría ya existe
                $sqlCheck = "SELECT COUNT(*) as total FROM CATEGORIAS WHERE DESCRIPCION = ? AND ESTADO = 'Activo'";
                $count = $db->query($sqlCheck, [$descripcion])->fetch()['total'];
                
                if ($count > 0) {
                    $mensaje = "Ya existe una categoría activa con ese nombre.";
                    $tipoMensaje = 'error';
                } else {
                    $sql = "INSERT INTO CATEGORIAS (DESCRIPCION, ESTADO, FECHAREGISTRO) 
                            VALUES (?, 'Activo', NOW())";
                    $db->query($sql, [$descripcion]);
                    $mensaje = "Categoría agregada exitosamente.";
                    $tipoMensaje = 'success';
                }
            } catch (Exception $e) {
                $mensaje = "Error al agregar categoría: " . $e->getMessage();
                $tipoMensaje = 'error';
            }
        } else {
            $mensaje = "La descripción de la categoría es obligatoria.";
            $tipoMensaje = 'error';
        }
    }
    
    if ($accion === 'editar') {
        $idcategoria = intval($_POST['idcategoria'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if ($idcategoria > 0 && !empty($descripcion)) {
            try {
                // Verificar si el nombre ya existe en otra categoría
                $sqlCheck = "SELECT COUNT(*) as total FROM CATEGORIAS WHERE DESCRIPCION = ? AND IDCATEGORIA != ? AND ESTADO = 'Activo'";
                $count = $db->query($sqlCheck, [$descripcion, $idcategoria])->fetch()['total'];
                
                if ($count > 0) {
                    $mensaje = "Ya existe otra categoría activa con ese nombre.";
                    $tipoMensaje = 'error';
                } else {
                    $sql = "UPDATE CATEGORIAS SET DESCRIPCION = ? WHERE IDCATEGORIA = ?";
                    $db->query($sql, [$descripcion, $idcategoria]);
                    $mensaje = "Categoría actualizada exitosamente.";
                    $tipoMensaje = 'success';
                }
            } catch (Exception $e) {
                $mensaje = "Error al actualizar categoría: " . $e->getMessage();
                $tipoMensaje = 'error';
            }
        }
    }
    
    if ($accion === 'cambiar_estado') {
        $idcategoria = intval($_POST['idcategoria'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';
        
        if ($idcategoria > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            try {
                // Si se va a desactivar, verificar que no haya productos activos con esta categoría
                if ($nuevoEstado === 'Inactivo') {
                    $sqlCheck = "SELECT COUNT(*) as total FROM PRODUCTOS WHERE IDCATEGORIA = ? AND ESTADO = 'Activo'";
                    $count = $db->query($sqlCheck, [$idcategoria])->fetch()['total'];
                    
                    if ($count > 0) {
                        $mensaje = "No se puede desactivar esta categoría porque tiene $count producto(s) activo(s) asociado(s).";
                        $tipoMensaje = 'error';
                    } else {
                        $sql = "UPDATE CATEGORIAS SET ESTADO = ? WHERE IDCATEGORIA = ?";
                        $db->query($sql, [$nuevoEstado, $idcategoria]);
                        $mensaje = "Categoría desactivada exitosamente.";
                        $tipoMensaje = 'success';
                    }
                } else {
                    $sql = "UPDATE CATEGORIAS SET ESTADO = ? WHERE IDCATEGORIA = ?";
                    $db->query($sql, [$nuevoEstado, $idcategoria]);
                    $mensaje = "Categoría activada exitosamente.";
                    $tipoMensaje = 'success';
                }
            } catch (Exception $e) {
                $mensaje = "Error al cambiar estado: " . $e->getMessage();
                $tipoMensaje = 'error';
            }
        }
    }
}

// Obtener categorías con conteo de productos
$sql = "SELECT c.*, 
        COUNT(p.IDPRODUCTO) as total_productos,
        COUNT(CASE WHEN p.ESTADO = 'Activo' THEN 1 END) as productos_activos
        FROM CATEGORIAS c 
        LEFT JOIN PRODUCTOS p ON c.IDCATEGORIA = p.IDCATEGORIA 
        GROUP BY c.IDCATEGORIA
        ORDER BY c.ESTADO DESC, c.DESCRIPCION";
$categorias = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Gestión de Categorías - Alisbook</title>
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
            margin: 10% auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }
        
        .close:hover,
        .close:focus {
            color: #000;
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
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #354edb;
            box-shadow: 0 0 0 2px rgba(53, 78, 219, 0.2);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-modal-primary {
            flex: 1;
            background-color: #354edb;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .btn-modal-primary:hover {
            background-color: #2b3db8;
        }
        
        .btn-modal-secondary {
            flex: 1;
            background-color: #6c757d;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .btn-modal-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-agregar {
            background-color: #354edb;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-agregar:hover {
            background-color: #2b3db8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(53, 78, 219, 0.3);
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: black;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background-color 0.3s ease;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background-color 0.3s ease;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background-color 0.3s ease;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .acciones {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .badge-productos {
            background-color: #e8f4f8;
            color: #354edb;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .modal h3 {
            color: #354edb;
            margin-top: 0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">📁 Gestión de Categorías - Alisbook</a></h1>
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
            <h2>Categorías de Productos</h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-<?php echo $tipoMensaje === 'success' ? 'exito' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <button class="btn-agregar" onclick="abrirModalAgregar()">➕ Agregar Nueva Categoría</button>
            
            <?php if (empty($categorias)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">No hay categorías registradas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Total Productos</th>
                            <th>Productos Activos</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($categoria['IDCATEGORIA']); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($categoria['DESCRIPCION']); ?></td>
                                <td>
                                    <span class="badge-productos">
                                        <?php echo $categoria['total_productos']; ?> producto(s)
                                    </span>
                                </td>
                                <td><?php echo $categoria['productos_activos']; ?> activo(s)</td>
                                <td>
                                    <span class="estado-<?php echo strtolower($categoria['ESTADO']); ?>">
                                        <?php echo $categoria['ESTADO']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($categoria['FECHAREGISTRO'])); ?></td>
                                <td>
                                    <div class="acciones">
                                        <button class="btn-warning" onclick='abrirModalEditar(<?php echo json_encode($categoria); ?>)'>
                                            ✏️ Editar
                                        </button>
                                        <?php if ($categoria['ESTADO'] === 'Activo'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de desactivar esta categoría?');">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="idcategoria" value="<?php echo $categoria['IDCATEGORIA']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="Inactivo">
                                                <button type="submit" class="btn-danger">❌ Desactivar</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="idcategoria" value="<?php echo $categoria['IDCATEGORIA']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="Activo">
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
            
            <div class="contenedor-volver" style="margin-top: 30px;">
                <button onclick="location.href='inventario.php'" class="btn-primary" style="margin-right: 10px;">
                    📦 Ir a Inventario
                </button>
                <button onclick="location.href='main.php'" class="btn-volver-main">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>

    <!-- Modal Agregar Categoría -->
    <div id="modalAgregar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalAgregar()">&times;</span>
            <h3>➕ Agregar Nueva Categoría</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="form-group">
                    <label for="descripcion">Nombre de la Categoría *</label>
                    <input type="text" id="descripcion" name="descripcion" 
                           placeholder="Ej: Libros, Útiles Escolares, Papelería..." 
                           required maxlength="100" autofocus>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn-modal-primary">✅ Guardar Categoría</button>
                    <button type="button" class="btn-modal-secondary" onclick="cerrarModalAgregar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Categoría -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalEditar()">&times;</span>
            <h3>✏️ Editar Categoría</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" id="edit_idcategoria" name="idcategoria">
                
                <div class="form-group">
                    <label for="edit_descripcion">Nombre de la Categoría *</label>
                    <input type="text" id="edit_descripcion" name="descripcion" 
                           required maxlength="100">
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn-modal-primary">💾 Actualizar</button>
                    <button type="button" class="btn-modal-secondary" onclick="cerrarModalEditar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalAgregar() {
            document.getElementById('modalAgregar').style.display = 'block';
            setTimeout(() => {
                document.getElementById('descripcion').focus();
            }, 100);
        }
        
        function cerrarModalAgregar() {
            document.getElementById('modalAgregar').style.display = 'none';
            document.getElementById('descripcion').value = '';
        }
        
        function abrirModalEditar(categoria) {
            document.getElementById('edit_idcategoria').value = categoria.IDCATEGORIA;
            document.getElementById('edit_descripcion').value = categoria.DESCRIPCION;
            document.getElementById('modalEditar').style.display = 'block';
            setTimeout(() => {
                document.getElementById('edit_descripcion').focus();
            }, 100);
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
        
        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModalAgregar();
                cerrarModalEditar();
            }
        });
    </script>
</body>
</html>
