<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';

// Procesar formulario de agregar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_producto'])) {
    try {
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        $preciocompra = floatval($_POST['preciocompra'] ?? 0);
        $precioventa = floatval($_POST['precioventa'] ?? 0);
        $idcategoria = intval($_POST['idcategoria'] ?? 0);
        
        // Validaciones
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
        
        // Verificar si el código ya existe
        if (!empty($codigo)) {
            $sqlCheck = "SELECT COUNT(*) FROM PRODUCTOS WHERE CODIGO = ? AND ESTADO = 'Activo'";
            $count = $db->query($sqlCheck, [$codigo])->fetchColumn();
            if ($count > 0) {
                throw new Exception('Ya existe un producto con ese código.');
            }
        }
        
        // Insertar producto
        $sqlInsert = "INSERT INTO PRODUCTOS (CODIGO, NOMBRE, DESCRIPCION, STOCK, PRECIOCOMPRA, PRECIOVENTA, ESTADO, FECHAREGISTRO, IDCATEGORIA) 
                     VALUES (?, ?, ?, ?, ?, ?, 'Activo', NOW(), ?)";
        
        $db->query($sqlInsert, [$codigo, $nombre, $descripcion, $stock, $preciocompra, $precioventa, $idcategoria]);
        
        $mensaje = "Producto agregado exitosamente: " . htmlspecialchars($nombre);
        
        // Limpiar formulario
        $_POST = [];
        
        // Recargar productos
        $sql = "SELECT p.*, c.DESCRIPCION as categoria_nombre 
                FROM PRODUCTOS p 
                LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
                WHERE p.ESTADO = 'Activo' 
                ORDER BY p.NOMBRE";
        $productos = $db->query($sql)->fetchAll();
        
    } catch (Exception $e) {
        $error = "Error al agregar el producto: " . $e->getMessage();
    }
} else {
    // Obtener productos con sus categorías
    $sql = "SELECT p.*, c.DESCRIPCION as categoria_nombre 
            FROM PRODUCTOS p 
            LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
            WHERE p.ESTADO = 'Activo' 
            ORDER BY p.NOMBRE";
    $productos = $db->query($sql)->fetchAll();
}

// Obtener categorías para el formulario
$sqlCategorias = "SELECT * FROM CATEGORIAS WHERE ESTADO = 'Activo' ORDER BY DESCRIPCION";
$categorias = $db->query($sqlCategorias)->fetchAll();
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
        <!-- Mensajes de feedback -->
        <?php if ($mensaje): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para agregar productos -->
        <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #354edb; display: flex; align-items: center; gap: 10px;">
                <span>➕ Agregar Nuevo Producto</span>
                <button type="button" id="toggleForm" style="background: #354edb; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px;">
                    Mostrar/Ocultar
                </button>
            </h3>
            
            <form method="POST" id="formAgregarProducto" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Código del Producto:</label>
                        <input type="text" name="codigo" placeholder="Ej: LIB001" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                        <small style="color: #666;">Opcional - Se puede dejar vacío</small>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Nombre del Producto: *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Cien años de soledad" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Descripción:</label>
                    <textarea name="descripcion" rows="3" placeholder="Descripción detallada del producto..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; resize: vertical; box-sizing: border-box;"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Categoría: *</label>
                        <select name="idcategoria" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['IDCATEGORIA']; ?>">
                                    <?php echo htmlspecialchars($categoria['DESCRIPCION']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Stock Inicial: *</label>
                        <input type="number" name="stock" min="0" required placeholder="0" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Precio de Compra: *</label>
                        <input type="number" name="preciocompra" step="0.01" min="0" required placeholder="0.00" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #354edb;">Precio de Venta: *</label>
                    <input type="number" name="precioventa" step="0.01" min="0" required placeholder="0.00" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                    <small style="color: #666;">Debe ser mayor al precio de compra</small>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" name="agregar_producto" style="background: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin-right: 10px;">
                        ➕ Agregar Producto
                    </button>
                    <button type="button" onclick="limpiarFormulario()" style="background: #6c757d; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        🗑️ Limpiar
                    </button>
                </div>
            </form>
        </div>

        <div class="tabla-container">
            <h2>Lista de Productos</h2>
            
            <!-- Buscador de productos -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <input type="text" id="buscadorProductos" placeholder="🔍 Buscar por nombre, código, descripción o categoría..." 
                               style="width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    </div>
                    <div>
                        <select id="filtroCategoria" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                            <option value="">Todas las categorías</option>
                            <?php 
                            $categorias = array_unique(array_column($productos, 'categoria_nombre'));
                            foreach ($categorias as $categoria): 
                                if ($categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select id="filtroStock" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                            <option value="">Todo el stock</option>
                            <option value="disponible">Disponible (>0)</option>
                            <option value="agotado">Agotado (0)</option>
                            <option value="bajo">Stock bajo (≤5)</option>
                        </select>
                    </div>
                    <button onclick="limpiarFiltros()" style="background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                        Limpiar
                    </button>
                </div>
                <div id="resultadosBusqueda" style="margin-top: 10px; font-size: 14px; color: #666;"></div>
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
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductosBody">
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

    <script>
        // Datos de productos desde PHP
        const productos = <?php echo json_encode($productos); ?>;
        let productosFiltrados = [...productos];

        // Renderizar tabla
        function renderizarTabla(productosAMostrar = productosFiltrados) {
            const tbody = document.getElementById('tablaProductosBody');
            
            if (productosAMostrar.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px; color: #666;">No se encontraron productos que coincidan con los filtros.</td></tr>';
                return;
            }
            
            tbody.innerHTML = productosAMostrar.map(producto => `
                <tr>
                    <td>${producto.IDPRODUCTO}</td>
                    <td>${producto.CODIGO || ''}</td>
                    <td>${producto.NOMBRE || ''}</td>
                    <td>${producto.DESCRIPCION || 'N/A'}</td>
                    <td>${producto.categoria_nombre || 'Sin categoría'}</td>
                    <td>$${parseFloat(producto.PRECIOCOMPRA || 0).toFixed(2)}</td>
                    <td>$${parseFloat(producto.PRECIOVENTA || 0).toFixed(2)}</td>
                    <td>
                        <span style="color: ${producto.STOCK > 5 ? 'green' : (producto.STOCK > 0 ? 'orange' : 'red')}">
                            ${producto.STOCK}
                        </span>
                    </td>
                    <td>
                        ${producto.STOCK > 0 
                            ? '<span style="color: green;">✓ Disponible</span>' 
                            : '<span style="color: red;">✗ Agotado</span>'
                        }
                    </td>
                </tr>
            `).join('');
            
            actualizarResultados(productosAMostrar.length);
        }

        // Función de filtrado
        function filtrarProductos() {
            const textoBusqueda = document.getElementById('buscadorProductos').value.toLowerCase();
            const categoriaSeleccionada = document.getElementById('filtroCategoria').value;
            const stockSeleccionado = document.getElementById('filtroStock').value;
            
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
            
            if (cantidad === totalProductos) {
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
            productosFiltrados = [...productos];
            renderizarTabla();
        }

        // Event listeners
        document.getElementById('buscadorProductos').addEventListener('input', filtrarProductos);
        document.getElementById('filtroCategoria').addEventListener('change', filtrarProductos);
        document.getElementById('filtroStock').addEventListener('change', filtrarProductos);

        // Permitir filtrado en tiempo real mientras se escribe
        document.getElementById('buscadorProductos').addEventListener('keyup', function(e) {
            // Filtrar después de un pequeño delay para mejor rendimiento
            clearTimeout(this.timer);
            this.timer = setTimeout(filtrarProductos, 300);
        });

        // Cargar tabla inicial
        document.addEventListener('DOMContentLoaded', function() {
            renderizarTabla();
        });

        // ==========================================
        // FUNCIONES PARA EL FORMULARIO DE PRODUCTOS
        // ==========================================

        // Toggle mostrar/ocultar formulario
        document.getElementById('toggleForm').addEventListener('click', function() {
            const form = document.getElementById('formAgregarProducto');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                this.textContent = 'Ocultar';
            } else {
                form.style.display = 'none';
                this.textContent = 'Mostrar';
            }
        });

        // Limpiar formulario
        function limpiarFormulario() {
            document.getElementById('formAgregarProducto').reset();
        }

        // Validación en tiempo real de precios
        document.querySelector('input[name="preciocompra"]').addEventListener('input', validarPrecios);
        document.querySelector('input[name="precioventa"]').addEventListener('input', validarPrecios);

        function validarPrecios() {
            const precioCompra = parseFloat(document.querySelector('input[name="preciocompra"]').value) || 0;
            const precioVenta = parseFloat(document.querySelector('input[name="precioventa"]').value) || 0;
            const submitBtn = document.querySelector('button[name="agregar_producto"]');
            const precioVentaInput = document.querySelector('input[name="precioventa"]');

            if (precioVenta > 0 && precioCompra > 0 && precioVenta <= precioCompra) {
                precioVentaInput.style.borderColor = '#dc3545';
                precioVentaInput.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.25)';
                submitBtn.disabled = true;
                submitBtn.style.background = '#6c757d';
                submitBtn.title = 'El precio de venta debe ser mayor al precio de compra';
            } else {
                precioVentaInput.style.borderColor = '#ddd';
                precioVentaInput.style.boxShadow = 'none';
                submitBtn.disabled = false;
                submitBtn.style.background = '#28a745';
                submitBtn.title = '';
            }
        }

        // Validación del formulario antes de enviar
        document.getElementById('formAgregarProducto').addEventListener('submit', function(e) {
            const nombre = document.querySelector('input[name="nombre"]').value.trim();
            const categoria = document.querySelector('select[name="idcategoria"]').value;
            const stock = parseInt(document.querySelector('input[name="stock"]').value) || 0;
            const precioCompra = parseFloat(document.querySelector('input[name="preciocompra"]').value) || 0;
            const precioVenta = parseFloat(document.querySelector('input[name="precioventa"]').value) || 0;

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

            if (precioCompra <= 0) {
                e.preventDefault();
                alert('El precio de compra debe ser mayor a 0.');
                return false;
            }

            if (precioVenta <= precioCompra) {
                e.preventDefault();
                alert('El precio de venta debe ser mayor al precio de compra.');
                return false;
            }

            return confirm('¿Está seguro de agregar este producto al inventario?');
        });
    </script>
</body>
</html>