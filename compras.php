<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';

// Procesar nueva compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_compra'])) {
    try {
        $tipodocumento = $_POST['tipodocumento'] ?? '';
        $numerodocumento = $_POST['numerodocumento'] ?? '';
        $idproveedor = intval($_POST['idproveedor'] ?? 0);
        $idformapago = intval($_POST['idformapago'] ?? 0);
        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        $precioscompra = $_POST['precioscompra'] ?? [];
        $preciosventas = $_POST['preciosventas'] ?? [];
        
        if (empty($productos) || $idproveedor == 0) {
            throw new Exception('Debe seleccionar un proveedor y agregar al menos un producto.');
        }
        
        // Calcular total y validar datos
        $montototal = 0;
        $detalles = [];
        
        foreach ($productos as $index => $idproducto) {
            if (empty($cantidades[$index]) || $cantidades[$index] <= 0) continue;
            
            $cantidad = intval($cantidades[$index]);
            $preciocompra = floatval($precioscompra[$index] ?? 0);
            $precioventa = floatval($preciosventas[$index] ?? 0);
            
            if ($preciocompra <= 0) {
                throw new Exception("El precio de compra debe ser mayor a 0.");
            }
            
            $subtotal = $preciocompra * $cantidad;
            $montototal += $subtotal;
            
            $detalles[] = [
                'idproducto' => $idproducto,
                'cantidad' => $cantidad,
                'preciocompra' => $preciocompra,
                'precioventa' => $precioventa,
                'subtotal' => $subtotal
            ];
        }
        
        if (empty($detalles)) {
            throw new Exception('Debe agregar al menos un producto con cantidad válida.');
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
        
        // Insertar detalles y actualizar stock/precios
        foreach ($detalles as $detalle) {
            // Insertar detalle
            $sqlDetalle = "INSERT INTO DETALLECOMPRAS (PRECIOVENTA, PRECIOCOMPRA, CANTIDAD, MONTOTOTAL, FECHAREGISTRO, IDCOMPRA, IDPRODUCTO)
                          VALUES (?, ?, ?, ?, NOW(), ?, ?)";
            
            $db->query($sqlDetalle, [
                $detalle['precioventa'],
                $detalle['preciocompra'],
                $detalle['cantidad'],
                $detalle['subtotal'],
                $idcompra,
                $detalle['idproducto']
            ]);
            
            // Actualizar stock y precios del producto
            $sqlUpdate = "UPDATE PRODUCTOS SET 
                         STOCK = STOCK + ?, 
                         PRECIOCOMPRA = ?, 
                         PRECIOVENTA = ?
                         WHERE IDPRODUCTO = ?";
            $db->query($sqlUpdate, [
                $detalle['cantidad'], 
                $detalle['preciocompra'],
                $detalle['precioventa'], 
                $detalle['idproducto']
            ]);
        }
        
        $db->getConnection()->commit();
        $mensaje = "Compra registrada exitosamente. Total: $" . number_format($montototal, 2);
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        $error = "Error al registrar la compra: " . $e->getMessage();
    }
}

// Obtener datos para el formulario
$sqlProveedores = "SELECT * FROM PROVEEDORES WHERE ESTADO = 'Activo' ORDER BY RAZONSOCIAL";
$proveedores = $db->query($sqlProveedores)->fetchAll();

$sqlFormasPago = "SELECT * FROM FORMAS_PAGO ORDER BY TIPOPAGO";
$formasPago = $db->query($sqlFormasPago)->fetchAll();

$sqlProductos = "SELECT p.*, c.DESCRIPCION as categoria_nombre 
                FROM PRODUCTOS p 
                LEFT JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
                WHERE p.ESTADO = 'Activo' 
                ORDER BY p.NOMBRE";
$productos = $db->query($sqlProductos)->fetchAll();

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
                        <label for="idproveedor">Proveedor:</label>
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
                        <label for="idformapago">Forma de Pago:</label>
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
                    <h3>Productos a Comprar</h3>
                    <div class="productos-header">
                        <span>Producto</span>
                        <span>Cantidad</span>
                        <span>Precio Compra</span>
                        <span>Precio Venta</span>
                        <span>Subtotal</span>
                    </div>
                    <div id="productosContainer">
                        <?php if (!empty($productos)): ?>
                            <?php foreach ($productos as $index => $producto): ?>
                                <div class="producto-item">
                                    <div>
                                        <label>
                                            <input type="checkbox" name="productos[]" value="<?php echo $producto['IDPRODUCTO']; ?>" onchange="toggleProducto(this)">
                                            <strong><?php echo htmlspecialchars($producto['NOMBRE']); ?></strong>
                                            <br><small><?php echo htmlspecialchars($producto['categoria_nombre']); ?> - Stock actual: <?php echo $producto['STOCK']; ?></small>
                                        </label>
                                    </div>
                                    <input type="number" name="cantidades[]" min="1" placeholder="Cant." disabled onchange="calcularTotal()">
                                    <input type="number" name="precioscompra[]" step="0.01" min="0" placeholder="0.00" disabled onchange="calcularTotal()" value="<?php echo $producto['PRECIOCOMPRA']; ?>">
                                    <input type="number" name="preciosventas[]" step="0.01" min="0" placeholder="0.00" disabled onchange="calcularTotal()" value="<?php echo $producto['PRECIOVENTA']; ?>">
                                    <span class="subtotal">$0.00</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay productos disponibles.</p>
                        <?php endif; ?>
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
        function toggleProducto(checkbox) {
            const item = checkbox.closest('.producto-item');
            const inputs = item.querySelectorAll('input[name="cantidades[]"], input[name="precioscompra[]"], input[name="preciosventas[]"]');
            
            inputs.forEach(input => {
                input.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    input.value = input.name === 'cantidades[]' ? '' : input.defaultValue;
                }
            });
            
            calcularTotal();
        }

        function calcularTotal() {
            let total = 0;
            const productosItems = document.querySelectorAll('.producto-item');
            
            productosItems.forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                const cantidadInput = item.querySelector('input[name="cantidades[]"]');
                const precioInput = item.querySelector('input[name="precioscompra[]"]');
                const subtotalSpan = item.querySelector('.subtotal');
                
                if (checkbox.checked && cantidadInput.value && precioInput.value) {
                    const cantidad = parseInt(cantidadInput.value);
                    const precio = parseFloat(precioInput.value);
                    const subtotal = precio * cantidad;
                    
                    subtotalSpan.textContent = '$' + subtotal.toFixed(2);
                    total += subtotal;
                } else {
                    subtotalSpan.textContent = '$0.00';
                }
            });
            
            document.getElementById('totalCompra').textContent = total.toFixed(2);
        }

        function limpiarFormulario() {
            document.getElementById('compraForm').reset();
            document.querySelectorAll('input[name="cantidades[]"], input[name="precioscompra[]"], input[name="preciosventas[]"]').forEach(input => {
                input.disabled = true;
            });
            document.querySelectorAll('.subtotal').forEach(span => {
                span.textContent = '$0.00';
            });
            document.getElementById('totalCompra').textContent = '0.00';
        }

        // Validación del formulario
        document.getElementById('compraForm').addEventListener('submit', function(e) {
            const productosSeleccionados = document.querySelectorAll('input[name="productos[]"]:checked');
            
            if (productosSeleccionados.length === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos un producto para la compra.');
                return false;
            }
            
            let hayErrores = false;
            productosSeleccionados.forEach(checkbox => {
                const item = checkbox.closest('.producto-item');
                const cantidadInput = item.querySelector('input[name="cantidades[]"]');
                const precioInput = item.querySelector('input[name="precioscompra[]"]');
                
                if (!cantidadInput.value || parseInt(cantidadInput.value) <= 0) {
                    hayErrores = true;
                }
                
                if (!precioInput.value || parseFloat(precioInput.value) <= 0) {
                    hayErrores = true;
                }
            });
            
            if (hayErrores) {
                e.preventDefault();
                alert('Todos los productos seleccionados deben tener cantidad y precio válidos.');
                return false;
            }
            
            return confirm('¿Está seguro de registrar esta compra?');
        });
    </script>
</body>
</html>