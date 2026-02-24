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
        $correocliente = $_POST['correocliente'] ?? '';
        $telefonocliente = $_POST['telefonocliente'] ?? '';
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
        
        // Verificar si el cliente existe, si no, crearlo automáticamente
        $idcliente = null;
        if (!empty($documentocliente)) {
            $sqlCliente = "SELECT IDCLIENTE FROM CLIENTES WHERE DOCUMENTO = ? AND ESTADO = 'Activo'";
            $cliente = $db->query($sqlCliente, [$documentocliente])->fetch();
            
            if (!$cliente) {
                // Cliente no existe, registrarlo automáticamente
                $sqlInsertCliente = "INSERT INTO CLIENTES (DOCUMENTO, NOMBRECOMPLETO, CORREO, TELEFONO, ESTADO, FECHAREGISTRO) 
                                    VALUES (?, ?, ?, ?, 'Activo', NOW())";
                $db->query($sqlInsertCliente, [$documentocliente, $nombrecliente, $correocliente, $telefonocliente]);
                $idcliente = $db->getConnection()->lastInsertId();
                
                // Agregar mensaje informativo
                $mensaje .= "Cliente registrado automáticamente. ";
            } else {
                $idcliente = $cliente['IDCLIENTE'];
            }
        }
        
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
        $mensaje .= "Venta registrada exitosamente. Total: $" . number_format($montototal, 2) . 
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

// Generar el siguiente número de documento automáticamente
$sqlUltimoNumero = "SELECT NUMERODOCUMENTO FROM VENTAS ORDER BY IDVENTA DESC LIMIT 1";
$ultimaVenta = $db->query($sqlUltimoNumero)->fetch();

if ($ultimaVenta && !empty($ultimaVenta['NUMERODOCUMENTO'])) {
    // Extraer el número del formato (ej: V-0001 -> 0001)
    $ultimoNumero = $ultimaVenta['NUMERODOCUMENTO'];
    preg_match('/\d+$/', $ultimoNumero, $matches);
    $numero = isset($matches[0]) ? intval($matches[0]) + 1 : 1;
} else {
    $numero = 1;
}

$siguienteNumeroDocumento = 'V-' . str_pad($numero, 6, '0', STR_PAD_LEFT);

// Obtener ventas recientes con detalles de productos
$sqlVentas = "SELECT v.*, u.NOMBRECOMPLETO as usuario_nombre,
              GROUP_CONCAT(CONCAT(p.NOMBRE, ' (x', dv.CANTIDAD, ')') SEPARATOR ', ') as productos_vendidos
              FROM VENTAS v 
              INNER JOIN USUARIOS u ON v.IDUSUARIO = u.IDUSUARIO 
              LEFT JOIN DETALLEVENTAS dv ON v.IDVENTA = dv.IDVENTA
              LEFT JOIN PRODUCTOS p ON dv.IDPRODUCTO = p.IDPRODUCTO
              GROUP BY v.IDVENTA
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
</head>
<body class="main-body">
    <header class="top-bar">
        <h1><a href="main.php" class="logo-link">💰 Ventas - Alisbook</a></h1>
        <div class="header-nav">
            <a href="detalles_ventas.php">📋 Ver Detalles</a>
            <a href="main.php">🏠 Inicio</a>
            <a href="perfil.php" style="color: white; text-decoration: none;" title="Ver mi perfil">
                👤 <?php echo htmlspecialchars($user['nombre']); ?>
            </a>
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
                        <input type="text" name="numerodocumento" id="numerodocumento" 
                               value="<?php echo htmlspecialchars($siguienteNumeroDocumento); ?>" 
                               readonly style="background-color: #f0f0f0;">
                        <small style="color: #666; display: block; margin-top: 5px;">📋 Generado automáticamente</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombrecliente">Nombre del Cliente:</label>
                        <input type="text" name="nombrecliente" id="nombrecliente" required placeholder="Nombre completo del cliente">
                    </div>
                    <div class="form-group">
                        <label for="documentocliente">Documento del Cliente:</label>
                        <input type="text" name="documentocliente" id="documentocliente" placeholder="DNI, RUC, etc. (Si no existe, se registrará automáticamente)">
                        <small style="color: #666; font-size: 0.9em;">.</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="correocliente">Correo del Cliente:</label>
                        <input type="email" name="correocliente" id="correocliente" placeholder="correo@ejemplo.com (opcional)">
                    </div>
                    <div class="form-group">
                        <label for="telefonocliente">Teléfono del Cliente:</label>
                        <input type="tel" name="telefonocliente" id="telefonocliente" placeholder="123-456-7890 (opcional)">
                    </div>
                </div>
                
                <div class="productos-section">
                    <h3>Productos Disponibles para Venta</h3>
                    <?php if (empty($productos)): ?>
                        <div class="info-inventario" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left-color: #ffc107; color: #856404;">
                            ⚠️ No hay productos disponibles para vender. Para agregar productos al inventario, ve al módulo de <a href="compras.php" style="color: #856404; font-weight: 600;">Compras</a>.
                        </div>
                    <?php else: ?>
                    <!-- Buscador de productos -->
                    <div class="buscador-ventas">
                        <input type="text" id="buscadorProductosVentas" placeholder="🔍 Buscar producto por nombre o categoría..." onkeyup="filtrarProductosVentas()">
                        <small style="color: #666; margin-left: 10px;"><span id="mensajeProductos"><?php echo count($productos); ?> productos disponibles</span></small>
                    </div>
                    
                    <div id="productosContainer">
                        <?php foreach ($productos as $index => $producto): ?>
                            <div class="producto-item" data-nombre="<?php echo strtolower($producto['NOMBRE']); ?>" data-categoria="<?php echo strtolower($producto['categoria']); ?>" data-precio="<?php echo $producto['PRECIOVENTA']; ?>" data-index="<?php echo $index; ?>" style="<?php echo $index >= 5 ? 'display: none;' : ''; ?>">
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
                    </div>
                    <?php endif; ?>
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
                        <input type="text" id="montocambio" readonly placeholder="$0.00" class="campo-cambio">
                    </div>
                </div>
                
                <div class="contenedor-botones">
                    <button type="submit" name="registrar_venta" class="btn-primary">Registrar Venta</button>
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
                            <th>Productos Vendidos</th>
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
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($venta['productos_vendidos'] ?? 'N/A'); ?>">
                                    <?php echo htmlspecialchars($venta['productos_vendidos'] ?? 'N/A'); ?>
                                </td>
                                <td class="total-destacado">
                                    $<?php echo number_format($venta['MONTOTOTAL'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($venta['usuario_nombre']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venta['FECHAREGISTRO'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="contenedor-volver">
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
                    // Obtener el precio del atributo data-precio del contenedor
                    const precio = parseFloat(item.getAttribute('data-precio'));
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
            // Limpiar campos de cliente
            document.getElementById('correocliente').value = '';
            document.getElementById('telefonocliente').value = '';
            // Limpiar buscador
            const buscador = document.getElementById('buscadorProductosVentas');
            if (buscador) {
                buscador.value = '';
                filtrarProductosVentas();
            }
        }

        // Función de filtrado de productos
        function filtrarProductosVentas() {
            const buscador = document.getElementById('buscadorProductosVentas');
            const filtro = buscador.value.toLowerCase().trim();
            const items = document.querySelectorAll('#productosContainer .producto-item');
            const totalProductos = items.length;
            let contador = 0;
            const hayFiltro = filtro !== '';

            items.forEach(item => {
                const nombre = item.getAttribute('data-nombre') || '';
                const categoria = item.getAttribute('data-categoria') || '';
                const index = parseInt(item.getAttribute('data-index'));
                
                if (nombre.includes(filtro) || categoria.includes(filtro)) {
                    // Si hay filtro, mostrar todos los que coincidan
                    // Si no hay filtro, mostrar solo los primeros 5
                    if (hayFiltro || index < 5) {
                        item.style.display = '';
                        contador++;
                    } else {
                        item.style.display = 'none';
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Actualizar mensaje
            const mensajeSpan = document.getElementById('mensajeProductos');
            if (mensajeSpan) {
                if (!hayFiltro) {
                    mensajeSpan.textContent = `${totalProductos} productos disponibles`;
                } else {
                    mensajeSpan.textContent = `Mostrando ${contador} de ${totalProductos} productos`;
                }
            }
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
            
            if (!confirm('¿Está seguro de registrar esta venta?')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>