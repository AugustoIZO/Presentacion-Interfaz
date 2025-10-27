<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';

// Procesar nueva compra con creación automática de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_compra'])) {
    try {
        $tipodocumento = $_POST['tipodocumento'] ?? '';
        $numerodocumento = $_POST['numerodocumento'] ?? '';
        $idproveedor = intval($_POST['idproveedor'] ?? 0);
        $idformapago = intval($_POST['idformapago'] ?? 0);
        
        // Datos de productos (arrays)
        $nombres_productos = $_POST['nombres_productos'] ?? [];
        $categorias_productos = $_POST['categorias_productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        $precioscompra = $_POST['precioscompra'] ?? [];
        $preciosventas = $_POST['preciosventas'] ?? [];
        
        if ($idproveedor == 0) {
            throw new Exception('Debe seleccionar un proveedor.');
        }
        
        if (empty($nombres_productos)) {
            throw new Exception('Debe agregar al menos un producto.');
        }
        
        // Calcular total y validar datos
        $montototal = 0;
        $detalles = [];
        
        foreach ($nombres_productos as $index => $nombre_producto) {
            $nombre_producto = trim($nombre_producto);
            if (empty($nombre_producto)) continue;
            
            $idcategoria = intval($categorias_productos[$index] ?? 0);
            $cantidad = intval($cantidades[$index] ?? 0);
            $preciocompra = floatval($precioscompra[$index] ?? 0);
            $precioventa = floatval($preciosventas[$index] ?? 0);
            
            if ($cantidad <= 0) {
                throw new Exception("La cantidad debe ser mayor a 0 para el producto: $nombre_producto");
            }
            
            if ($preciocompra <= 0) {
                throw new Exception("El precio de compra debe ser mayor a 0 para el producto: $nombre_producto");
            }
            
            if ($precioventa <= 0) {
                throw new Exception("El precio de venta debe ser mayor a 0 para el producto: $nombre_producto");
            }
            
            if ($idcategoria <= 0) {
                throw new Exception("Debe seleccionar una categoría para el producto: $nombre_producto");
            }
            
            $subtotal = $preciocompra * $cantidad;
            $montototal += $subtotal;
            
            $detalles[] = [
                'nombre' => $nombre_producto,
                'idcategoria' => $idcategoria,
                'cantidad' => $cantidad,
                'preciocompra' => $preciocompra,
                'precioventa' => $precioventa,
                'subtotal' => $subtotal
            ];
        }
        
        if (empty($detalles)) {
            throw new Exception('Debe agregar al menos un producto con datos válidos.');
        }
        
        // Iniciar transacción
        $db->getConnection()->beginTransaction();
        
        // Insertar compra
        $sqlCompra = "INSERT INTO COMPRAS (TIPODOCUMENTO, NUMERODOCUMENTO, MONTOTOTAL, FECHAREGISTRO, IDUSUARIO, IDPROVEEDOR, IDFORMAPAGO) 
                     VALUES (?, ?, ?, NOW(), ?, ?, ?)";
        
        $db->query($sqlCompra, [
            $tipodocumento,
            $numerodocumento,
            $montototal,
            $user['id'],
            $idproveedor,
            $idformapago
        ]);
        
        $idcompra = $db->getConnection()->lastInsertId();
        
        // Procesar cada producto
        foreach ($detalles as $detalle) {
            // Buscar si el producto ya existe (por nombre y categoría)
            $sqlBuscar = "SELECT IDPRODUCTO, STOCK FROM PRODUCTOS WHERE NOMBRE = ? AND IDCATEGORIA = ? AND ESTADO = 'Activo'";
            $productoExistente = $db->query($sqlBuscar, [$detalle['nombre'], $detalle['idcategoria']])->fetch();
            
            if ($productoExistente) {
                // Producto existe: actualizar stock y precios
                $idproducto = $productoExistente['IDPRODUCTO'];
                
                $sqlUpdate = "UPDATE PRODUCTOS SET 
                             STOCK = STOCK + ?, 
                             PRECIOCOMPRA = ?, 
                             PRECIOVENTA = ?
                             WHERE IDPRODUCTO = ?";
                $db->query($sqlUpdate, [
                    $detalle['cantidad'], 
                    $detalle['preciocompra'],
                    $detalle['precioventa'], 
                    $idproducto
                ]);
            } else {
                // Producto NO existe: crear nuevo
                $sqlInsert = "INSERT INTO PRODUCTOS (NOMBRE, DESCRIPCION, STOCK, PRECIOCOMPRA, PRECIOVENTA, ESTADO, FECHAREGISTRO, IDCATEGORIA) 
                             VALUES (?, 'Producto agregado desde compra', ?, ?, ?, 'Activo', NOW(), ?)";
                
                $db->query($sqlInsert, [
                    $detalle['nombre'],
                    $detalle['cantidad'],
                    $detalle['preciocompra'],
                    $detalle['precioventa'],
                    $detalle['idcategoria']
                ]);
                
                $idproducto = $db->getConnection()->lastInsertId();
            }
            
            // Insertar detalle de compra
            $sqlDetalle = "INSERT INTO DETALLECOMPRAS (PRECIOVENTA, PRECIOCOMPRA, CANTIDAD, MONTOTOTAL, FECHAREGISTRO, IDCOMPRA, IDPRODUCTO)
                          VALUES (?, ?, ?, ?, NOW(), ?, ?)";
            
            $db->query($sqlDetalle, [
                $detalle['precioventa'],
                $detalle['preciocompra'],
                $detalle['cantidad'],
                $detalle['subtotal'],
                $idcompra,
                $idproducto
            ]);
        }
        
        $db->getConnection()->commit();
        $mensaje = "Compra registrada exitosamente. Total: $" . number_format($montototal, 2) . ". Los productos han sido agregados al inventario.";
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (Exception $e) {
        if ($db->getConnection()->inTransaction()) {
            $db->getConnection()->rollBack();
        }
        $error = "Error al registrar la compra: " . $e->getMessage();
    }
}

// Obtener datos para el formulario
$sqlProveedores = "SELECT * FROM PROVEEDORES WHERE ESTADO = 'Activo' ORDER BY RAZONSOCIAL";
$proveedores = $db->query($sqlProveedores)->fetchAll();

$sqlFormasPago = "SELECT * FROM FORMAS_PAGO ORDER BY TIPOPAGO";
$formasPago = $db->query($sqlFormasPago)->fetchAll();

$sqlCategorias = "SELECT * FROM CATEGORIAS WHERE ESTADO = 'Activo' ORDER BY DESCRIPCION";
$categorias = $db->query($sqlCategorias)->fetchAll();

// Obtener compras recientes
$sqlCompras = "SELECT c.*, p.RAZONSOCIAL as proveedor_nombre, u.NOMBRECOMPLETO as usuario_nombre, fp.TIPOPAGO
              FROM COMPRAS c 
              LEFT JOIN PROVEEDORES p ON c.IDPROVEEDOR = p.IDPROVEEDOR
              LEFT JOIN USUARIOS u ON c.IDUSUARIO = u.IDUSUARIO 
              LEFT JOIN FORMAS_PAGO fp ON c.IDFORMAPAGO = fp.IDFORMAPAGO
              ORDER BY c.FECHAREGISTRO DESC 
              LIMIT 10";
$compras = $db->query($sqlCompras)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Compras - Alisbook</title>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">🛒 Compras - Alisbook</a></h1>
        <div class="header-nav">
            <a href="main.php">🏠 Inicio</a>
            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="login.php?logout=1" class="logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="main-content">
        <?php if ($mensaje): ?>
            <div class="mensaje-exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Formulario de Nueva Compra -->
        <div class="compra-form">
            <h2>Registrar Nueva Compra</h2>
            <p style="margin-bottom: 20px; color: #666;">
                ℹ️ Ingresa los productos que estás comprando. Si el producto no existe, se creará automáticamente en el inventario.
            </p>
            <form method="POST" id="compraForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipodocumento">Tipo de Documento:</label>
                        <select name="tipodocumento" id="tipodocumento" required>
                            <option value="">Seleccionar</option>
                            <option value="Factura">Factura</option>
                            <option value="Boleta">Boleta</option>
                            <option value="Recibo">Recibo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="numerodocumento">Número de Documento:</label>
                        <input type="text" name="numerodocumento" id="numerodocumento" placeholder="Ej: 001-001-0001">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="idproveedor">Proveedor: *</label>
                        <select name="idproveedor" id="idproveedor" required>
                            <option value="">Seleccionar proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['IDPROVEEDOR']; ?>">
                                    <?php echo htmlspecialchars($proveedor['RAZONSOCIAL']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="idformapago">Forma de Pago: *</label>
                        <select name="idformapago" id="idformapago" required>
                            <option value="">Seleccionar forma de pago</option>
                            <?php foreach ($formasPago as $forma): ?>
                                <option value="<?php echo $forma['IDFORMAPAGO']; ?>">
                                    <?php echo htmlspecialchars($forma['TIPOPAGO']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="productos-section">
                    <h3>Productos a Comprar <button type="button" onclick="agregarFilaProducto()" class="btn-add-producto">➕ Agregar Producto</button></h3>
                    <div class="productos-header">
                        <span style="flex: 2;">Nombre del Producto</span>
                        <span style="flex: 1;">Categoría</span>
                        <span>Cantidad</span>
                        <span>Precio Compra</span>
                        <span>Precio Venta</span>
                        <span>Subtotal</span>
                        <span>Acción</span>
                    </div>
                    <div id="productosContainer">
                        <!-- Las filas de productos se agregan dinámicamente con JS -->
                    </div>
                </div>
                
                <div class="total-compra">
                    Total de la Compra: $<span id="totalCompra">0.00</span>
                </div>
                                
                <div class="contenedor-boton-volver">
                    <button type="submit" name="registrar_compra" class="btn-primary">Registrar Compra</button>
                    <button type="button" onclick="limpiarFormulario()" class="btn-secondary">Limpiar</button>
                </div>
            </form>
        </div>
        
        <!-- Historial de Compras Recientes -->
        <div class="tabla-container">
            <h2>Compras Recientes</h2>
            
            <?php if (empty($compras)): ?>
                <p>No hay compras registradas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo Doc.</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Forma Pago</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($compra['IDCOMPRA']); ?></td>
                                <td><?php echo htmlspecialchars($compra['TIPODOCUMENTO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($compra['proveedor_nombre'] ?? 'N/A'); ?></td>
                                <td class="precio-destacado">
                                    $<?php echo number_format($compra['MONTOTOTAL'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($compra['TIPOPAGO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($compra['usuario_nombre']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($compra['FECHAREGISTRO'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="contenedor-boton-volver">
                <button onclick="location.href='main.php'" class="btn-volver-menu">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>

    <script>
        // Datos de categorías para el select dinámico
        const categorias = <?php echo json_encode($categorias); ?>;
        let contadorProductos = 0;

        // Agregar una fila de producto
        function agregarFilaProducto() {
            contadorProductos++;
            const container = document.getElementById('productosContainer');
            
            const fila = document.createElement('div');
            fila.className = 'producto-item-dinamico';
            fila.id = 'producto-' + contadorProductos;
            
            fila.innerHTML = `
                <input type="text" name="nombres_productos[]" placeholder="Nombre del producto" required style="flex: 2;">
                <select name="categorias_productos[]" required style="flex: 1;">
                    <option value="">Categoría</option>
                    ${categorias.map(cat => `<option value="${cat.IDCATEGORIA}">${cat.DESCRIPCION}</option>`).join('')}
                </select>
                <input type="number" name="cantidades[]" min="1" placeholder="Cant." required onchange="calcularTotal()">
                <input type="number" name="precioscompra[]" step="0.01" min="0.01" placeholder="P. Compra" required onchange="calcularTotal()">
                <input type="number" name="preciosventas[]" step="0.01" min="0.01" placeholder="P. Venta" required onchange="calcularTotal()">
                <span class="subtotal">$0.00</span>
                <button type="button" onclick="eliminarProducto(${contadorProductos})" class="btn-eliminar">🗑️</button>
            `;
            
            container.appendChild(fila);
        }

        // Eliminar fila de producto
        function eliminarProducto(id) {
            const fila = document.getElementById('producto-' + id);
            if (fila) {
                fila.remove();
                calcularTotal();
            }
        }

        // Calcular total
        function calcularTotal() {
            let total = 0;
            const filas = document.querySelectorAll('.producto-item-dinamico');
            
            filas.forEach(fila => {
                const cantidad = parseFloat(fila.querySelector('input[name="cantidades[]"]').value) || 0;
                const precioCompra = parseFloat(fila.querySelector('input[name="precioscompra[]"]').value) || 0;
                const subtotalSpan = fila.querySelector('.subtotal');
                
                const subtotal = cantidad * precioCompra;
                subtotalSpan.textContent = '$' + subtotal.toFixed(2);
                total += subtotal;
            });
            
            document.getElementById('totalCompra').textContent = total.toFixed(2);
        }

        // Limpiar formulario
        function limpiarFormulario() {
            document.getElementById('compraForm').reset();
            document.getElementById('productosContainer').innerHTML = '';
            contadorProductos = 0;
            document.getElementById('totalCompra').textContent = '0.00';
        }

        // Validación del formulario
        document.getElementById('compraForm').addEventListener('submit', function(e) {
            const filas = document.querySelectorAll('.producto-item-dinamico');
            
            if (filas.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto para la compra.');
                return false;
            }

            let hayErrores = false;
            let mensajeError = '';

            filas.forEach((fila, index) => {
                const nombre = fila.querySelector('input[name="nombres_productos[]"]').value.trim();
                const categoria = fila.querySelector('select[name="categorias_productos[]"]').value;
                const cantidad = parseFloat(fila.querySelector('input[name="cantidades[]"]').value) || 0;
                const precioCompra = parseFloat(fila.querySelector('input[name="precioscompra[]"]').value) || 0;
                const precioVenta = parseFloat(fila.querySelector('input[name="preciosventas[]"]').value) || 0;

                if (!nombre) {
                    hayErrores = true;
                    mensajeError = `Producto ${index + 1}: El nombre es requerido.`;
                }
                if (!categoria) {
                    hayErrores = true;
                    mensajeError = `Producto ${index + 1}: Debe seleccionar una categoría.`;
                }
                if (cantidad <= 0) {
                    hayErrores = true;
                    mensajeError = `Producto ${index + 1}: La cantidad debe ser mayor a 0.`;
                }
                if (precioCompra <= 0) {
                    hayErrores = true;
                    mensajeError = `Producto ${index + 1}: El precio de compra debe ser mayor a 0.`;
                }
                if (precioVenta <= 0) {
                    hayErrores = true;
                    mensajeError = `Producto ${index + 1}: El precio de venta debe ser mayor a 0.`;
                }
            });

            if (hayErrores) {
                e.preventDefault();
                alert(mensajeError);
                return false;
            }

            return confirm('¿Está seguro de registrar esta compra? Los productos se agregarán al inventario.');
        });

        // Agregar primera fila automáticamente
        window.addEventListener('DOMContentLoaded', function() {
            agregarFilaProducto();
        });
    </script>
</body>
</html>