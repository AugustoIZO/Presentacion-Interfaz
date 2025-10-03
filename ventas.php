<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$user = $auth->getCurrentUser();

$mensaje = '';
$error = '';

// Procesar nueva venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_venta'])) {
    try {
        $tipodocumento = $_POST['tipodocumento'] ?? '';
        $numerodocumento = $_POST['numerodocumento'] ?? '';
        $documentocliente = $_POST['documentocliente'] ?? '';
        $nombrecliente = $_POST['nombrecliente'] ?? '';
        $montopago = floatval($_POST['montopago'] ?? 0);
        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        
        if (empty($productos) || empty($nombrecliente)) {
            throw new Exception('Debe completar todos los campos requeridos y agregar al menos un producto.');
        }
        
        // Calcular total y validar stock
        $montototal = 0;
        $detalles = [];
        
        foreach ($productos as $index => $idproducto) {
            if (empty($cantidades[$index]) || $cantidades[$index] <= 0) continue;
            
            $cantidad = intval($cantidades[$index]);
            
            // Obtener producto y verificar stock
            $sqlProducto = "SELECT * FROM PRODUCTOS WHERE IDPRODUCTO = ? AND ESTADO = 'Activo'";
            $producto = $db->query($sqlProducto, [$idproducto])->fetch();
            
            if (!$producto) {
                throw new Exception("Producto no encontrado o inactivo.");
            }
            
            if ($producto['STOCK'] < $cantidad) {
                throw new Exception("Stock insuficiente para el producto: " . $producto['NOMBRE']);
            }
            
            $subtotal = $producto['PRECIOVENTA'] * $cantidad;
            $montototal += $subtotal;
            
            $detalles[] = [
                'idproducto' => $idproducto,
                'cantidad' => $cantidad,
                'precioventa' => $producto['PRECIOVENTA'],
                'subtotal' => $subtotal
            ];
        }
        
        if (empty($detalles)) {
            throw new Exception('Debe agregar al menos un producto con cantidad válida.');
        }
        
        $montocambio = $montopago - $montototal;
        if ($montocambio < 0) {
            throw new Exception('El monto pagado es insuficiente.');
        }
        
        // Iniciar transacción
        $db->getConnection()->beginTransaction();
        
        // Insertar venta
        $sqlVenta = "INSERT INTO VENTAS (TIPODOCUMENTO, NUMERODOCUMENTO, DOCUMENTOCLIENTE, NOMBRECLIENTE, 
                     MONTOPAGO, MONTOCAMBIO, MONTOTOTAL, FECHAREGISTRO, IDUSUARIO) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $db->query($sqlVenta, [
            $tipodocumento,
            $numerodocumento,
            $documentocliente,
            $nombrecliente,
            $montopago,
            $montocambio,
            $montototal,
            $user['id']
        ]);
        
        $idventa = $db->getConnection()->lastInsertId();
        
        // Insertar detalles y actualizar stock
        foreach ($detalles as $detalle) {
            // Insertar detalle
            $sqlDetalle = "INSERT INTO DETALLEVENTAS (PRECIOVENTA, CANTIDAD, SUBTOTAL, FECHAREGISTRO, IDVENTA, IDPRODUCTO)
                          VALUES (?, ?, ?, NOW(), ?, ?)";
            
            $db->query($sqlDetalle, [
                $detalle['precioventa'],
                $detalle['cantidad'],
                $detalle['subtotal'],
                $idventa,
                $detalle['idproducto']
            ]);
            
            // Actualizar stock
            $sqlStock = "UPDATE PRODUCTOS SET STOCK = STOCK - ? WHERE IDPRODUCTO = ?";
            $db->query($sqlStock, [$detalle['cantidad'], $detalle['idproducto']]);
        }
        
        $db->getConnection()->commit();
        $mensaje = "Venta registrada exitosamente. Total: $" . number_format($montototal, 2) . 
                  " - Cambio: $" . number_format($montocambio, 2);
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        $error = "Error al registrar la venta: " . $e->getMessage();
    }
}

// Obtener productos activos
$sqlProductos = "SELECT p.*, c.DESCRIPCION as categoria 
                FROM PRODUCTOS p 
                INNER JOIN CATEGORIAS c ON p.IDCATEGORIA = c.IDCATEGORIA 
                WHERE p.ESTADO = 'Activo' AND p.STOCK > 0
                ORDER BY p.NOMBRE";
$productos = $db->query($sqlProductos)->fetchAll();

// Obtener ventas recientes
$sqlVentas = "SELECT v.*, u.NOMBRECOMPLETO as usuario_nombre 
              FROM VENTAS v 
              INNER JOIN USUARIOS u ON v.IDUSUARIO = u.IDUSUARIO 
              ORDER BY v.FECHAREGISTRO DESC 
              LIMIT 10";
$ventas = $db->query($sqlVentas)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Ventas - Alisbook</title>
    <style>
        .venta-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #354edb;
        }
        .form-group input, .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .productos-section {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .producto-item {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .producto-item:last-child {
            border-bottom: none;
        }
        .btn-primary {
            background: #354edb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .mensaje-exito {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .mensaje-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .total-venta {
            font-size: 18px;
            font-weight: bold;
            color: #354edb;
            text-align: right;
            margin: 15px 0;
        }
    </style>
</head>
<body class="main-body">
    <header class="top-bar">
        <h1>💰 Ventas - Alisbook</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="main.php" style="color: white; text-decoration: none;">🏠 Inicio</a>
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
        
        <!-- Formulario de Nueva Venta -->
        <div class="venta-form">
            <h2>Registrar Nueva Venta</h2>
            <form method="POST" id="ventaForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipodocumento">Tipo de Documento:</label>
                        <select name="tipodocumento" id="tipodocumento" required>
                            <option value="">Seleccionar</option>
                            <option value="Boleta">Boleta</option>
                            <option value="Factura">Factura</option>
                            <option value="Ticket">Ticket</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="numerodocumento">Número de Documento:</label>
                        <input type="text" name="numerodocumento" id="numerodocumento" placeholder="Ej: 001-001-0001">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombrecliente">Nombre del Cliente:</label>
                        <input type="text" name="nombrecliente" id="nombrecliente" required placeholder="Nombre completo del cliente">
                    </div>
                    <div class="form-group">
                        <label for="documentocliente">Documento del Cliente:</label>
                        <input type="text" name="documentocliente" id="documentocliente" placeholder="DNI, RUC, etc.">
                    </div>
                </div>
                
                <div class="productos-section">
                    <h3>Productos a Vender</h3>
                    <div id="productosContainer">
                        <?php if (!empty($productos)): ?>
                            <?php foreach ($productos as $index => $producto): ?>
                                <div class="producto-item">
                                    <div>
                                        <label>
                                            <input type="checkbox" name="productos[]" value="<?php echo $producto['IDPRODUCTO']; ?>" onchange="toggleCantidad(this)">
                                            <strong><?php echo htmlspecialchars($producto['NOMBRE']); ?></strong>
                                            <br><small><?php echo htmlspecialchars($producto['categoria']); ?> - Stock: <?php echo $producto['STOCK']; ?> - Precio: $<?php echo number_format($producto['PRECIOVENTA'], 2); ?></small>
                                        </label>
                                    </div>
                                    <input type="number" name="cantidades[]" min="1" max="<?php echo $producto['STOCK']; ?>" placeholder="Cant." disabled onchange="calcularTotal()">
                                    <span class="subtotal">$0.00</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay productos disponibles para la venta.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="total-venta">
                    Total de la Venta: $<span id="totalVenta">0.00</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="montopago">Monto Pagado por el Cliente:</label>
                        <input type="number" name="montopago" id="montopago" step="0.01" min="0" required placeholder="0.00" onchange="calcularCambio()">
                    </div>
                    <div class="form-group">
                        <label>Cambio a Entregar:</label>
                        <input type="text" id="montocambio" readonly placeholder="$0.00" style="background: #f8f9fa;">
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="registrar_venta" class="btn-primary">Registrar Venta</button>
                    <button type="button" onclick="limpiarFormulario()" class="btn-secondary">Limpiar</button>
                </div>
            </form>
        </div>
        
        <!-- Historial de Ventas Recientes -->
        <div class="tabla-container">
            <h2>Ventas Recientes</h2>
            
            <?php if (empty($ventas)): ?>
                <p>No hay ventas registradas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo Doc.</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Vendedor</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venta['IDVENTA']); ?></td>
                                <td><?php echo htmlspecialchars($venta['TIPODOCUMENTO'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($venta['NOMBRECLIENTE'] ?? 'N/A'); ?></td>
                                <td style="font-weight: bold; color: green;">
                                    $<?php echo number_format($venta['MONTOTOTAL'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($venta['usuario_nombre']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venta['FECHAREGISTRO'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 20px; text-align: center;">
                <button onclick="location.href='main.php'" class="btn-primary">
                    Volver al Menú Principal
                </button>
            </div>
        </div>
    </main>

    <script>
        function toggleCantidad(checkbox) {
            const cantidadInput = checkbox.closest('.producto-item').querySelector('input[name="cantidades[]"]');
            cantidadInput.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                cantidadInput.value = '';
            }
            calcularTotal();
        }

        function calcularTotal() {
            let total = 0;
            const productosItems = document.querySelectorAll('.producto-item');
            
            productosItems.forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                const cantidadInput = item.querySelector('input[name="cantidades[]"]');
                const subtotalSpan = item.querySelector('.subtotal');
                
                if (checkbox.checked && cantidadInput.value) {
                    const precio = parseFloat(checkbox.closest('label').textContent.match(/\$(\d+\.?\d*)/)[1]);
                    const cantidad = parseInt(cantidadInput.value);
                    const subtotal = precio * cantidad;
                    
                    subtotalSpan.textContent = '$' + subtotal.toFixed(2);
                    total += subtotal;
                } else {
                    subtotalSpan.textContent = '$0.00';
                }
            });
            
            document.getElementById('totalVenta').textContent = total.toFixed(2);
            calcularCambio();
        }

        function calcularCambio() {
            const total = parseFloat(document.getElementById('totalVenta').textContent) || 0;
            const montoPago = parseFloat(document.getElementById('montopago').value) || 0;
            const cambio = montoPago - total;
            
            document.getElementById('montocambio').value = cambio >= 0 ? '$' + cambio.toFixed(2) : '$0.00';
            document.getElementById('montocambio').style.color = cambio >= 0 ? 'green' : 'red';
        }

        function limpiarFormulario() {
            document.getElementById('ventaForm').reset();
            document.querySelectorAll('input[name="cantidades[]"]').forEach(input => {
                input.disabled = true;
            });
            document.querySelectorAll('.subtotal').forEach(span => {
                span.textContent = '$0.00';
            });
            document.getElementById('totalVenta').textContent = '0.00';
            document.getElementById('montocambio').value = '$0.00';
        }

        // Validación del formulario
        document.getElementById('ventaForm').addEventListener('submit', function(e) {
            const productosSeleccionados = document.querySelectorAll('input[name="productos[]"]:checked');
            
            if (productosSeleccionados.length === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos un producto para la venta.');
                return false;
            }
            
            let hayErrores = false;
            productosSeleccionados.forEach(checkbox => {
                const item = checkbox.closest('.producto-item');
                const cantidadInput = item.querySelector('input[name="cantidades[]"]');
                
                if (!cantidadInput.value || parseInt(cantidadInput.value) <= 0) {
                    hayErrores = true;
                }
            });
            
            if (hayErrores) {
                e.preventDefault();
                alert('Todos los productos seleccionados deben tener una cantidad válida.');
                return false;
            }
            
            const total = parseFloat(document.getElementById('totalVenta').textContent) || 0;
            const montoPago = parseFloat(document.getElementById('montopago').value) || 0;
            
            if (montoPago < total) {
                e.preventDefault();
                alert('El monto pagado no puede ser menor al total de la venta.');
                return false;
            }
            
            return confirm('¿Está seguro de registrar esta venta?');
        });
    </script>
</body>
</html>