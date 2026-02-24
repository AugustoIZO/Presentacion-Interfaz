<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';

// Procesar cambio de estado de producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    try {
        $idproducto = intval($_POST['idproducto'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';
        
        if ($idproducto <= 0) {
            throw new Exception('ID de producto inválido.');
        }
        
        if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            throw new Exception('Estado inválido.');
        }
        
        $sql = "UPDATE PRODUCTOS SET ESTADO = ? WHERE IDPRODUCTO = ?";
        $db->query($sql, [$nuevoEstado, $idproducto]);
        
        $mensaje = "Estado del producto actualizado correctamente.";
        
    } catch (Exception $e) {
        $error = "Error al cambiar estado: " . $e->getMessage();
    }
}

// Procesar edición de producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_producto'])) {
    try {
        $idproducto = intval($_POST['idproducto'] ?? 0);
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        $preciocompra = floatval($_POST['preciocompra'] ?? 0);
        $precioventa = floatval($_POST['precioventa'] ?? 0);
        $idcategoria = intval($_POST['idcategoria'] ?? 0);
        
        // Validaciones
        if ($idproducto <= 0) {
            throw new Exception('ID de producto inválido.');
        }
        
        if (empty($nombre)) {
            throw new Exception('El nombre del producto es requerido.');
        }
        
        if ($stock < 0) {
            throw new Exception('El stock no puede ser negativo.');
        }
        
        if ($preciocompra < 0 || $precioventa < 0) {
            throw new Exception('Los precios no pueden ser negativos.');
        }
        
        if ($precioventa <= $preciocompra) {
            throw new Exception('El precio de venta debe ser mayor al precio de compra.');
        }
        
        // Verificar si el código ya existe en otro producto
        if (!empty($codigo)) {
            $sqlCheck = "SELECT COUNT(*) FROM PRODUCTOS WHERE CODIGO = ? AND IDPRODUCTO != ? AND ESTADO = 'Activo'";
            $count = $db->query($sqlCheck, [$codigo, $idproducto])->fetchColumn();
            if ($count > 0) {
                throw new Exception('Ya existe otro producto con ese código.');
            }
        }
        
        $sql = "UPDATE PRODUCTOS SET 
                CODIGO = ?, NOMBRE = ?, DESCRIPCION = ?, STOCK = ?, 
                PRECIOCOMPRA = ?, PRECIOVENTA = ?, IDCATEGORIA = ?
                WHERE IDPRODUCTO = ?";
        
        $db->query($sql, [
            $codigo, $nombre, $descripcion, $stock, 
            $preciocompra, $precioventa, $idcategoria, $idproducto
        ]);
        
        $mensaje = "Producto actualizado correctamente.";
        
    } catch (Exception $e) {
        $error = "Error al editar producto: " . $e->getMessage();
    }
}

// Obtener productos (mostrar activos e inactivos para gestión completa)
$sqlEstado = $_GET['mostrar_inactivos'] ?? 'no';
if ($sqlEstado === 'si') {
    $sql = "SELECT p.*, c.DESCRIPCION as categoria_nombre 
            FROM PRODUCTOS p 
            LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
            ORDER BY p.ESTADO DESC, p.NOMBRE";
} else {
    $sql = "SELECT p.*, c.DESCRIPCION as categoria_nombre 
            FROM PRODUCTOS p 
            LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
            WHERE p.ESTADO = 'Activo' 
            ORDER BY p.NOMBRE";
}
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
        <h1><a href="main.php" class="logo-link">📦 Inventario - Alisbook</a></h1>
        <div class="header-flex">
            <a href="main.php" class="header-link">🏠 Inicio</a>
            <a href="perfil.php" style="color: white; text-decoration: none;" title="Ver mi perfil">
                👤 <?php echo htmlspecialchars($user['nombre']); ?>
            </a>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <!-- Mensajes de feedback -->
        <?php if ($mensaje): ?>
            <div class="mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="tabla-container">
            <h2>Lista de Productos en Inventario</h2>
            <p class="info-inventario">
                ℹ️ Mostrando los últimos 5 productos agregados. Usa los filtros para buscar productos específicos. Los productos se agregan automáticamente al inventario cuando realizas una compra en el módulo de <a href="compras.php" style="color: #4a90e2; text-decoration: underline;">Compras</a>. Puedes gestionar las <a href="categorias.php" style="color: #4a90e2; text-decoration: underline; font-weight: bold;">Categorías</a> para organizar tus productos.
            </p>
            
            <!-- Controles adicionales -->
            <div class="controles-tabla">
                <div class="filtro-estado">
                    <label>
                        <input type="checkbox" id="mostrarInactivos" 
                               <?php echo ($_GET['mostrar_inactivos'] ?? 'no') === 'si' ? 'checked' : ''; ?>>
                        Mostrar productos inactivos
                    </label>
                </div>
            </div>
            
            <!-- Buscador de productos -->
            <div class="filtros-inventario">
                <div class="filtros-inventario-container">
                    <div class="filtro-busqueda">
                        <input type="text" id="buscadorProductos" placeholder="🔍 Buscar por nombre, código, descripción o categoría...">
                    </div>
                    <div class="filtro-categoria">
                        <select id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            <?php 
                            $categoriasFiltro = array_unique(array_column($productos, 'categoria_nombre'));
                            foreach ($categoriasFiltro as $categoria): 
                                if ($categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro-stock">
                        <select id="filtroStock">
                            <option value="">Todo el stock</option>
                            <option value="disponible">Disponible (>0)</option>
                            <option value="agotado">Agotado (0)</option>
                            <option value="bajo">Stock bajo (≤5)</option>
                        </select>
                    </div>
                    <button onclick="limpiarFiltros()" class="btn-limpiar-filtros">
                        Limpiar
                    </button>
                </div>
                <div id="resultadosBusqueda" class="resultados-busqueda"></div>
            </div>
            
            <?php if (empty($productos)): ?>
                <p>No hay productos registrados.</p>
            <?php else: ?>
                <table id="tablaProductos">
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
                            <th>Disponibilidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductosBody">
                        <?php foreach ($productos as $producto): ?>
                            <tr class="<?php echo $producto['ESTADO'] === 'Inactivo' ? 'producto-inactivo' : ''; ?>">
                                <td><?php echo htmlspecialchars($producto['IDPRODUCTO']); ?></td>
                                <td><?php echo htmlspecialchars($producto['CODIGO']); ?></td>
                                <td><?php echo htmlspecialchars($producto['NOMBRE']); ?></td>
                                <td><?php echo htmlspecialchars($producto['DESCRIPCION'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                <td>$<?php echo number_format($producto['PRECIOCOMPRA'], 2); ?></td>
                                <td>$<?php echo number_format($producto['PRECIOVENTA'], 2); ?></td>
                                <td>
                                    <span class="stock-<?php echo $producto['STOCK'] > 5 ? 'bueno' : ($producto['STOCK'] > 0 ? 'regular' : 'critico'); ?>">
                                        <?php echo $producto['STOCK']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($producto['STOCK'] > 0): ?>
                                        <span class="disponible">✓ Disponible</span>
                                    <?php else: ?>
                                        <span class="agotado">✗ Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="estado-<?php echo strtolower($producto['ESTADO']); ?>">
                                        <?php echo $producto['ESTADO']; ?>
                                    </span>
                                </td>
                                <td class="acciones-celda">
                                    <button onclick="editarProducto(<?php echo $producto['IDPRODUCTO']; ?>)" 
                                            class="btn-accion btn-editar" title="Editar producto">
                                        ✏️
                                    </button>
                                    
                                    <?php if ($producto['ESTADO'] === 'Activo'): ?>
                                        <button onclick="cambiarEstado(<?php echo $producto['IDPRODUCTO']; ?>, 'Inactivo')" 
                                                class="btn-accion btn-inactivar" title="Inactivar producto">
                                            🚫
                                        </button>
                                    <?php else: ?>
                                        <button onclick="cambiarEstado(<?php echo $producto['IDPRODUCTO']; ?>, 'Activo')" 
                                                class="btn-accion btn-activar" title="Activar producto">
                                            ✅
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Modal para editar producto -->
        <div id="modalEditarProducto" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✏️ Editar Producto</h3>
                    <span class="modal-close" onclick="cerrarModal()">&times;</span>
                </div>
                <form method="POST" id="formEditarProducto">
                    <input type="hidden" id="editIdProducto" name="idproducto">
                    
                    <div class="form-grid-2">
                        <div>
                            <label class="form-label">Código del Producto:</label>
                            <input type="text" id="editCodigo" name="codigo" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Nombre del Producto: *</label>
                            <input type="text" id="editNombre" name="nombre" required class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group-full">
                        <label class="form-label">Descripción:</label>
                        <textarea id="editDescripcion" name="descripcion" rows="3" class="form-textarea"></textarea>
                    </div>
                    
                    <div class="form-grid-3">
                        <div>
                            <label class="form-label">Categoría: *</label>
                            <select id="editCategoria" name="idcategoria" required class="form-select">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['IDCATEGORIA']; ?>">
                                        <?php echo htmlspecialchars($categoria['DESCRIPCION']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Stock: *</label>
                            <input type="number" id="editStock" name="stock" min="0" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Precio de Compra: *</label>
                            <input type="number" id="editPrecioCompra" name="preciocompra" step="0.01" min="0" required class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group-full">
                        <label class="form-label">Precio de Venta: *</label>
                        <input type="number" id="editPrecioVenta" name="precioventa" step="0.01" min="0" required class="form-input">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" name="editar_producto" class="btn-success">
                            💾 Guardar Cambios
                        </button>
                        <button type="button" onclick="cerrarModal()" class="btn-clean">
                            ❌ Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Formularios ocultos para cambios de estado -->
        <form id="formCambiarEstado" method="POST" style="display: none;">
            <input type="hidden" id="estadoIdProducto" name="idproducto">
            <input type="hidden" id="nuevoEstado" name="nuevo_estado">
            <input type="hidden" name="cambiar_estado" value="1">
        </form>
            
            <div class="contenedor-volver">
                <button onclick="location.href='categorias.php'" class="btn-primary" style="margin-right: 10px;">
                    📁 Gestionar Categorías
                </button>
                <button onclick="location.href='main.php'" class="btn-volver-main">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>

    <script>
        // Datos de productos desde PHP
        const productos = <?php echo json_encode($productos); ?>;
        let productosFiltrados = [...productos];
        let hayFiltrosActivos = false;

        // Función de filtrado
        function filtrarProductos() {
            const textoBusqueda = document.getElementById('buscadorProductos').value.toLowerCase();
            const categoriaSeleccionada = document.getElementById('filtroCategoria').value;
            const stockSeleccionado = document.getElementById('filtroStock').value;
            
            // Detectar si hay algún filtro activo
            hayFiltrosActivos = textoBusqueda !== '' || categoriaSeleccionada !== '' || stockSeleccionado !== '';
            
            productosFiltrados = productos.filter(producto => {
                // Filtro por texto (nombre, código, descripción, categoría)
                const coincideTexto = !textoBusqueda || 
                    (producto.NOMBRE && producto.NOMBRE.toLowerCase().includes(textoBusqueda)) ||
                    (producto.CODIGO && producto.CODIGO.toLowerCase().includes(textoBusqueda)) ||
                    (producto.DESCRIPCION && producto.DESCRIPCION.toLowerCase().includes(textoBusqueda)) ||
                    (producto.categoria_nombre && producto.categoria_nombre.toLowerCase().includes(textoBusqueda));
                
                // Filtro por categoría
                const coincideCategoria = !categoriaSeleccionada || 
                    producto.categoria_nombre === categoriaSeleccionada;
                
                // Filtro por stock
                let coincideStock = true;
                if (stockSeleccionado === 'disponible') {
                    coincideStock = producto.STOCK > 0;
                } else if (stockSeleccionado === 'agotado') {
                    coincideStock = producto.STOCK == 0;
                } else if (stockSeleccionado === 'bajo') {
                    coincideStock = producto.STOCK <= 5;
                }
                
                return coincideTexto && coincideCategoria && coincideStock;
            });
            
            renderizarTabla(productosFiltrados);
        }

        // Actualizar contador de resultados
        function actualizarResultados(cantidad) {
            const totalProductos = productos.length;
            const resultadosDiv = document.getElementById('resultadosBusqueda');
            
            if (!hayFiltrosActivos) {
                resultadosDiv.textContent = `Mostrando los últimos 5 de ${totalProductos} productos`;
            } else if (cantidad === totalProductos) {
                resultadosDiv.textContent = `Mostrando todos los ${totalProductos} productos`;
            } else {
                resultadosDiv.textContent = `Mostrando ${cantidad} de ${totalProductos} productos`;
            }
        }

        // Limpiar filtros
        function limpiarFiltros() {
            document.getElementById('buscadorProductos').value = '';
            document.getElementById('filtroCategoria').value = '';
            document.getElementById('filtroStock').value = '';
            hayFiltrosActivos = false;
            productosFiltrados = [...productos];
            renderizarTabla();
        }

        // Función renderizar tabla
        function renderizarTabla(productosAMostrar = productosFiltrados) {
            const tbody = document.getElementById('tablaProductosBody');
            
            // Si no hay filtros activos, limitar a 5 productos
            let productosLimitados = productosAMostrar;
            if (!hayFiltrosActivos) {
                productosLimitados = productosAMostrar.slice(0, 5);
            }
            
            if (productosLimitados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="sin-resultados">No se encontraron productos que coincidan con los filtros.</td></tr>';
                actualizarResultados(0);
                return;
            }
            
            tbody.innerHTML = productosLimitados.map(producto => `
                <tr class="${producto.ESTADO === 'Inactivo' ? 'producto-inactivo' : ''}">
                    <td>${producto.IDPRODUCTO}</td>
                    <td>${producto.CODIGO || ''}</td>
                    <td>${producto.NOMBRE || ''}</td>
                    <td>${producto.DESCRIPCION || 'N/A'}</td>
                    <td>${producto.categoria_nombre || 'Sin categoría'}</td>
                    <td>$${parseFloat(producto.PRECIOCOMPRA || 0).toFixed(2)}</td>
                    <td>$${parseFloat(producto.PRECIOVENTA || 0).toFixed(2)}</td>
                    <td>
                        <span class="stock-${producto.STOCK > 5 ? 'bueno' : (producto.STOCK > 0 ? 'regular' : 'critico')}">
                            ${producto.STOCK}
                        </span>
                    </td>
                    <td>
                        ${producto.STOCK > 0 
                            ? '<span class="disponible">✓ Disponible</span>' 
                            : '<span class="agotado">✗ Agotado</span>'
                        }
                    </td>
                    <td>
                        <span class="estado-${producto.ESTADO.toLowerCase()}">
                            ${producto.ESTADO}
                        </span>
                    </td>
                    <td class="acciones-celda">
                        <button onclick="editarProducto(${producto.IDPRODUCTO})" 
                                class="btn-accion btn-editar" title="Editar producto">
                            ✏️
                        </button>
                        ${producto.ESTADO === 'Activo' 
                            ? `<button onclick="cambiarEstado(${producto.IDPRODUCTO}, 'Inactivo')" 
                                       class="btn-accion btn-inactivar" title="Inactivar producto">
                                    🚫
                               </button>`
                            : `<button onclick="cambiarEstado(${producto.IDPRODUCTO}, 'Activo')" 
                                       class="btn-accion btn-activar" title="Activar producto">
                                    ✅
                               </button>`
                        }
                    </td>
                </tr>
            `).join('');
            
            actualizarResultados(productosLimitados.length);
        }
        // Función para editar producto
        function editarProducto(id) {
            // Buscar el producto en los datos de JavaScript
            let producto = null;
            for (let p of productos) {
                if (p.IDPRODUCTO == id) {
                    producto = p;
                    break;
                }
            }
            
            if (!producto) {
                alert('Error: No se encontró el producto.');
                return;
            }
            
            // Llenar el formulario modal
            document.getElementById('editIdProducto').value = producto.IDPRODUCTO;
            document.getElementById('editCodigo').value = producto.CODIGO || '';
            document.getElementById('editNombre').value = producto.NOMBRE || '';
            document.getElementById('editDescripcion').value = producto.DESCRIPCION || '';
            document.getElementById('editStock').value = producto.STOCK || 0;
            document.getElementById('editPrecioCompra').value = parseFloat(producto.PRECIOCOMPRA || 0).toFixed(2);
            document.getElementById('editPrecioVenta').value = parseFloat(producto.PRECIOVENTA || 0).toFixed(2);
            document.getElementById('editCategoria').value = producto.IDCATEGORIA || '';
            
            // Mostrar modal
            document.getElementById('modalEditarProducto').style.display = 'block';
        }

        // Función para cambiar estado de producto
        function cambiarEstado(id, nuevoEstado) {
            const accion = nuevoEstado === 'Activo' ? 'activar' : 'inactivar';
            
            if (confirm(`¿Está seguro de ${accion} este producto?`)) {
                document.getElementById('estadoIdProducto').value = id;
                document.getElementById('nuevoEstado').value = nuevoEstado;
                document.getElementById('formCambiarEstado').submit();
            }
        }

        // Función para cerrar modal
        function cerrarModal() {
            document.getElementById('modalEditarProducto').style.display = 'none';
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar tabla inicial
            renderizarTabla();
            
            // Configurar event listeners para filtros
            document.getElementById('buscadorProductos').addEventListener('input', filtrarProductos);
            document.getElementById('filtroCategoria').addEventListener('change', filtrarProductos);
            document.getElementById('filtroStock').addEventListener('change', filtrarProductos);

            // Validación del formulario de edición
            document.getElementById('formEditarProducto').addEventListener('submit', function(e) {
                const nombre = document.getElementById('editNombre').value.trim();
                const categoria = document.getElementById('editCategoria').value;
                const stock = parseInt(document.getElementById('editStock').value) || 0;
                const precioCompra = parseFloat(document.getElementById('editPrecioCompra').value) || 0;
                const precioVenta = parseFloat(document.getElementById('editPrecioVenta').value) || 0;

                if (!nombre) {
                    e.preventDefault();
                    alert('El nombre del producto es requerido.');
                    return false;
                }

                if (!categoria) {
                    e.preventDefault();
                    alert('Debe seleccionar una categoría.');
                    return false;
                }

                if (stock < 0) {
                    e.preventDefault();
                    alert('El stock no puede ser negativo.');
                    return false;
                }

                if (precioCompra < 0 || precioVenta < 0) {
                    e.preventDefault();
                    alert('Los precios no pueden ser negativos.');
                    return false;
                }

                if (precioVenta <= precioCompra) {
                    e.preventDefault();
                    alert('El precio de venta debe ser mayor al precio de compra.');
                    return false;
                }

                return confirm('¿Está seguro de guardar los cambios en este producto?');
            });

            // Manejar checkbox de mostrar inactivos
            document.getElementById('mostrarInactivos').addEventListener('change', function() {
                const url = new URL(window.location);
                if (this.checked) {
                    url.searchParams.set('mostrar_inactivos', 'si');
                } else {
                    url.searchParams.delete('mostrar_inactivos');
                }
                window.location.href = url.toString();
            });

            // Cerrar modal al hacer clic fuera de él
            window.onclick = function(event) {
                const modal = document.getElementById('modalEditarProducto');
                if (event.target == modal) {
                    cerrarModal();
                }
            }
        });
    </script>
</body>
</html>