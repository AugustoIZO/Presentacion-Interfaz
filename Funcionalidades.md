## 🎯 DESCRIPCIÓN GENERAL

### Características Principales:
- ✅ Gestión completa de inventario
- ✅ Control de compras a proveedores
- ✅ Sistema de ventas con cálculo automático
- ✅ Registro automático de clientes
- ✅ Reportes y estadísticas
- ✅ Interfaz moderna y responsive
- ✅ Validaciones en tiempo real

---

## 🔐 AUTENTICACIÓN

### Login (login.php)

**Funcionalidad**: Sistema de autenticación simplificado basado en documento de identidad.

#### Credenciales de Acceso:
- **Usuario**: Documento de identidad del usuario
- **Contraseña**: `1234` (universal para todos los usuarios)

#### Proceso:
1. Ingresar número de documento
2. Ingresar contraseña "1234"
3. Click en "Ingresar"
4. Redirección automática al dashboard

#### Validaciones:
- ✅ Verificación de documento existente en base de datos
- ✅ Contraseña debe coincidir exactamente

#### Seguridad:
- Sesiones PHP con `session_start()`
- Cierre de sesión disponible en todas las páginas

---

## 🏠 DASHBOARD PRINCIPAL

### Panel Principal (main.php)

**Funcionalidad**: Centro de navegación con acceso rápido a todos los módulos.

#### Módulos Disponibles:
1. **📦 Inventario** - Gestión de productos
2. **🛒 Compras** - Registro de compras a proveedores
3. **💰 Ventas** - Procesamiento de ventas
4. **📋 Detalles de ventas** - Ver historial y detalles de ventas
5. **👥 Clientes** - Gestión de clientes
6. **📊 Reportes** - Estadísticas y análisis
7. **👤 Usuarios** - Gestionar empleados del sistema

#### Características:
- ✅ Logo clickeable que retorna al dashboard desde cualquier módulo
- ✅ Información de usuario actual en header
- ✅ Botón de cierre de sesión

---

## 🛒 MÓDULO DE COMPRAS

### Registro de Compras (compras.php)

**Funcionalidad**: Sistema dinámico para registrar compras y crear productos automáticamente.

#### Proceso de Compra:

##### 1. Datos de la Compra:
- **Tipo de Documento**: Factura, Boleta, Recibo
- **Número de Documento**: Identificador único
- **Proveedor**: Selección de proveedor activo (requerido)
- **Forma de Pago**: Método de pago utilizado (requerido)

##### 2. Agregar Productos:
```
Click en botón "➕ Agregar Producto"
  ↓
Aparece nueva fila con campos:
  - Nombre del Producto (requerido)
  - Categoría (requerido)
  - Cantidad (requerido, > 0)
  - Precio de Compra (requerido, > 0)
  - Precio de Venta (requerido, > 0)
  - Subtotal (calculado automáticamente)
  - Botón Eliminar 🗑️
```

##### 3. Múltiples Productos:
- ✅ Agregar tantos productos como se necesite
- ✅ Eliminar productos con botón 🗑️
- ✅ Cálculo automático de subtotales
- ✅ Total general de la compra actualizado en tiempo real

#### Historial:
- Muestra últimas 10 compras registradas
- Información: ID, Tipo Doc, Proveedor, Total, Forma Pago, Usuario, Fecha

---

## 📦 MÓDULO DE INVENTARIO

### Gestión de Inventario (inventario.php)

**Funcionalidad**: Visualización y gestión de productos existentes (solo lectura para nuevos productos).

#### Características Principales:

##### 1. Mensaje Informativo:
```
ℹ️ Los productos se agregan automáticamente al inventario 
cuando realizas una compra en el módulo de Compras.
```

##### 2. Buscador Avanzado:
- **Input de búsqueda**: Busca por nombre, código, descripción o categoría
- **Filtro por categoría**: Todas las categorías disponibles
- **Filtro por stock**: 
  - Todos
  - Con stock (> 0)
  - Sin stock (= 0)
  - Stock bajo (< 10)

##### 3. Lista de Productos:

**Columnas mostradas:**
- Código
- Nombre
- Categoría
- Stock actual
- Precio de Compra
- Precio de Venta
- Estado (Activo/Inactivo)
- Acciones

##### 4. Acciones Disponibles:

**✏️ Editar Producto** (Modal):
```
Campos editables:
- Código
- Nombre
- Descripción
- Categoría
- Stock
- Precio de Compra
- Precio de Venta

**🔄 Cambiar Estado**:
- Activar producto inactivo
- Desactivar producto activo
- Confirmación antes de cambiar

#### Restricciones:
- No permite agregar productos manualmente
- Los productos solo se crean desde Compras
- Permite editar productos existentes
- Permite activar/desactivar productos

---

## 💰 MÓDULO DE VENTAS

### Sistema de Ventas (ventas.php)

**Funcionalidad**: Procesamiento de ventas con registro automático de clientes y control de stock.

#### Proceso de Venta:

##### 1. Datos del Documento:
- **Tipo de Documento**: Boleta, Factura, Ticket
- **Número de Documento**: Identificador de venta

##### 2. Datos del Cliente:
- **Nombre del Cliente**: Requerido
- **Documento del Cliente**:
- **Correo**
- **Teléfono**

**Registro Automático de Clientes:**
```
Si el documento existe:
  → Asocia la venta al cliente existente

Si el documento NO existe:
  → Crea el cliente automáticamente
  → Guarda: Documento, Nombre, Correo, Teléfono
  → Estado = 'Activo'
  → Asocia la venta al nuevo cliente
```

##### 3. Buscador de Productos:

**Características:**
- ✅ Input de búsqueda en tiempo real
- ✅ Filtra por nombre o categoría
- ✅ Contador dinámico de productos visibles
- ✅ Placeholder: "Buscar producto por nombre o categoría..."

##### 4. Productos Disponibles:

**Solo muestra productos con:**
- Estado = 'Activo'
- Stock > 0

**Información mostrada por producto:**
- Checkbox de selección
- Nombre del producto
- Categoría
- Stock disponible
- Precio de venta

**Cuando NO hay productos:**
```
⚠️ No hay productos disponibles para vender. 
Para agregar productos al inventario, ve al módulo de Compras.
```

##### 5. Selección de Productos:
```
1. Marcar checkbox del producto
2. Campo de cantidad se habilita
3. Ingresar cantidad (máximo = stock disponible)
4. Subtotal se calcula automáticamente
5. Total general se actualiza
```

##### 6. Cálculos Automáticos:

**Subtotal por producto:**
```
Subtotal = Cantidad × Precio de Venta
```

**Total de la venta:**
```
Total = Σ Subtotales de productos seleccionados
```

**Cambio:**
```
Cambio = Monto Pagado - Total
```

##### 7. Validaciones de Stock:
- ✅ Cantidad máxima = Stock disponible
- ✅ Verificación antes de procesar venta
- ✅ Error si stock insuficiente
- ✅ Actualización automática de stock al vender

#### Funcionalidades JavaScript:

**toggleCantidad()**: Habilita/deshabilita input de cantidad
**calcularTotal()**: Calcula subtotales y total general
**calcularCambio()**: Calcula cambio a entregar
**filtrarProductosVentas()**: Filtra productos en tiempo real
**limpiarFormulario()**: Resetea el formulario completo

#### Historial de Ventas:
- Muestra últimas 10 ventas
- Información: ID, Tipo Doc, Cliente, Documento, Total, Pago, Cambio, Usuario, Fecha

---

📋 MÓDULO DE DETALLES DE VENTAS

### Historial y Detalles de Ventas (detalles_ventas.php)

**Funcionalidad**: Consulta avanzada del historial de ventas, con filtros, estadísticas y visualización detallada de los productos vendidos en cada operación.

**Características Principales**:

- ✅ Visualización de ventas recientes (últimas 5 sin filtros)
- ✅ Búsqueda avanzada con múltiples filtros
- ✅ Despliegue dinámico de productos por venta
- ✅ Estadísticas automáticas
- ✅ Acceso solo a usuarios autenticados

#### 1.Filtros de Búsqueda

Filtros disponibles:

- Fecha inicio
- Fecha fin
- Nombre del cliente
- Número de documento de venta

Comportamiento:

- Sin filtros → muestra las últimas 5 ventas
- Con filtros → muestra todas las ventas coincidentes
- Incluye botón Buscar y botón Limpiar filtros.

#### 2.Estadísticas de Ventas

Se calculan automáticamente según los resultados mostrados:

- Total de ventas
- Monto total vendido
- Promedio por venta

#### 3.Listado de Ventas

Cada venta se muestra en formato de tarjeta interactiva con:

Información resumida:
- ID de la venta
- Tipo de documento (Boleta / Factura / Ticket)
- Número de documento
- Monto total

Información detallada:
- Cliente
- Documento del cliente
- Vendedor
- Fecha y hora
- Monto pagado
- Cambio entregado

#### 4.Detalle de Productos Vendidos

Al hacer clic sobre una venta se despliega el detalle:

Columnas mostradas:
- Código del producto
- Nombre del producto
- Categoría
- Precio unitario
- Cantidad vendida
- Subtotal

Características:
- Animación de despliegue
- Ícono visual para indicar apertura/cierre
- Consulta dinámica a la tabla DETALLEVENTAS

Validaciones y Seguridad:
- Sesión activa obligatoria
- Protección contra accesos no autenticados
- Consultas con filtros preparados (PDO)

---

## 👥 MÓDULO DE CLIENTES

### Gestión de Clientes (clientes.php)

**Funcionalidad**: Administración de clientes registrados (manual o automáticamente desde ventas).

#### Características:

##### 1. Registro Manual de Clientes:
```
Formulario con campos:
- Documento (requerido, único)
- Nombre Completo (requerido)
- Correo (opcional, formato email)
- Teléfono (opcional)
- Estado (Activo por defecto)
```

##### 2. Registro Automático:
- Se crean automáticamente al realizar una venta
- Si el documento no existe, se registra con los datos ingresados

##### 3. Lista de Clientes:
**Columnas:**
- Documento
- Nombre Completo
- Correo
- Teléfono
- Estado
- Fecha de Registro
- Acciones

##### 4. Acciones:
- ✏️ **Editar**: Modificar datos del cliente
- 🔄 **Cambiar Estado**: Activar/Desactivar cliente

##### 5. Búsqueda y Filtros:
- Buscar por documento o nombre
- Filtrar por estado (Activo/Inactivo)

---

## 📊 MÓDULO DE REPORTES

### Reportes y Estadísticas (reportes.php)

**Funcionalidad**: Visualización de estadísticas y reportes del sistema.

#### Reportes Disponibles:

##### 1. Resumen General:
- Total de productos en inventario
- Total de ventas realizadas
- Total de compras registradas
- Total de clientes activos

##### 2. Productos Más Vendidos:
- Top 10 productos
- Cantidad vendida
- Total en dinero

##### 3. Productos con Stock Bajo:
- Productos con stock < 10
- Alerta visual
- Stock actual vs stock crítico

##### 4. Ventas por Período:
- Ventas del día
- Ventas del mes
- Ventas del año
- Gráficos y tablas

##### 5. Compras por Período:
- Similar a ventas
- Agrupado por proveedor

---

👤 MÓDULO DE USUARIOS

### Gestión de Usuarios / Empleados (usuarios.php)

**Funcionalidad**: Administración de usuarios empleados del sistema (solo accesible por administradores).

#### 1.Control de Acceso

- ✅ Solo usuarios con rol Administrador
- ❌ Usuarios comunes son redirigidos al dashboard

#### 2.Listado de Usuarios

Muestra únicamente usuarios con rol Empleado.

Columnas:

- ID
- Documento
- Nombre completo
- Correo electrónico
- Rol
- Estado (Activo / Inactivo)
- Fecha de registro
- Acciones

#### 3.Registro de Nuevos Usuarios

Modal “Agregar Nuevo Empleado”

Campos:

- Documento (obligatorio, único)
- Nombre completo (obligatorio)
- Correo electrónico (opcional)
- Contraseña (obligatoria)

Características:

- Contraseña hasheada con **password_hash()**
- Estado inicial: Activo
- Rol asignado automáticamente: Empleado

#### 4.Edición de Usuarios

Modal “Editar Empleado”

Permite:

- Modificar documento
- Modificar nombre
- Modificar correo
- Cambiar contraseña (opcional)
- Si la contraseña se deja vacía, no se modifica.

#### 5.Activar / Desactivar Usuarios

- ❌ No se eliminan usuarios físicamente
- ✅ Se cambia el estado a Inactivo
- ✅ Posibilidad de reactivar usuarios
- Confirmación previa para desactivar

#### 6.Mensajes del Sistema:

- ✅ Usuario agregado correctamente
- ❌ Documento duplicado
- ❌ Error al guardar cambios
- ✅ Usuario activado / desactivado

#### 7.Seguridad:

- Uso de sesiones PHP
- Control de roles
- Formularios protegidos
- Validaciones backend

---

## 🔧 TECNOLOGÍAS UTILIZADAS

### Backend:
- PHP 7.4+
- PDO para base de datos
- Sesiones PHP para autenticación
- Transacciones SQL para consistencia

### Base de Datos:
- MySQL / MariaDB

### Frontend:
- HTML5
- CSS3
- JavaScript
---
