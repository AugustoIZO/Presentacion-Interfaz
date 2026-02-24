# 📚 Alisbook - Sistema de Gestión de Librería

Sistema web completo para la gestión de una librería, desarrollado en PHP con MySQL. Incluye módulos de inventario, compras, ventas, clientes, proveedores y reportes estadísticos.

## 📋 Tabla de Contenidos

- [Características Principales](#características-principales)
- [Tecnologías Utilizadas](#tecnologías-utilizadas)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Módulos del Sistema](#módulos-del-sistema)
- [Credenciales de Acceso](#credenciales-de-acceso)
- [Flujo de Trabajo](#flujo-de-trabajo)
- [Base de Datos](#base-de-datos)

---

## 🚀 Características Principales

- ✅ **Sistema de autenticación** con sesiones PHP
- ✅ **Gestión de inventario** con control de stock en tiempo real
- ✅ **Módulo de compras** con creación automática de productos
- ✅ **Sistema de ventas** con registro automático de clientes
- ✅ **Gestión de proveedores** y clientes
- ✅ **Categorización de productos** (Libros, Útiles, etc.)
- ✅ **Reportes y estadísticas** con gráficos interactivos
- ✅ **Validaciones en tiempo real** con JavaScript
- ✅ **Búsquedas y filtros avanzados** en todos los módulos

---

## 💻 Tecnologías Utilizadas

**Backend:**
- PHP
- MySQL/MariaDB
- PDO (PHP Data Objects) para conexiones seguras

**Frontend:**
- HTML5
- CSS3
- JavaScript (vanilla)
- Chart.js (para gráficos en reportes)

**Servidor:**
- XAMPP (Apache + MySQL)

---

## ⚙️ Requisitos del Sistema

- **XAMPP** 8.x o superior
- **PHP** 8.0 o superior
- **MySQL/MariaDB** 10.4 o superior
- **Navegador web moderno** (Chrome, Firefox, Edge)

---

## 📦 Instalación

### 1. Preparar el entorno

```bash
# Instalar XAMPP desde https://www.apachefriends.org/
# Iniciar los servicios de Apache y MySQL desde el panel de control de XAMPP
```

### 2. Clonar/Copiar el proyecto

```bash
# Copiar la carpeta del proyecto a:
C:\xampp\htdocs\Presentacion-Interfaz
```

### 3. Crear la base de datos

1. Abrir **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Crear una nueva base de datos llamada `alisbook`
3. Importar el archivo SQL:
   - Ir a la pestaña "Importar"
   - Seleccionar el archivo: `database/alisbook.sql`
   - Click en "Continuar"

### 4. Configurar la conexión a la base de datos

El archivo `config/database.php` ya está configurado con los valores por defecto de XAMPP:

```php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'alisbook');
```

Si tu configuración es diferente, modifica estos valores.

### 5. Acceder al sistema

Abrir en el navegador:
```
http://localhost/Presentacion-Interfaz
```

La aplicación redirigirá automáticamente al login.

---

## 📂 Estructura del Proyecto

```
Presentacion-Interfaz/
│
├── index.php                    # Punto de entrada (redirige al login)
├── login.php                    # Página de autenticación
├── main.php                     # Dashboard principal
├── perfil.php                   # Perfil de usuario
├── actualizar_passwords.php     # Actualización de contraseñas
│
├── categorias.php               # Gestión de categorías
├── clientes.php                 # Gestión de clientes
├── compras.php                  # Módulo de compras
├── inventario.php               # Gestión de inventario
├── proveedores.php              # Gestión de proveedores
├── usuarios.php                 # Gestión de usuarios (admin)
├── ventas.php                   # Módulo de ventas
├── detalles_ventas.php          # Detalles de ventas realizadas
├── reportes.php                 # Reportes y estadísticas
│
├── config/
│   └── database.php             # Configuración de base de datos
│
├── includes/
│   └── auth.php                 # Clase de autenticación
│
├── CSS/
│   └── style.css                # Estilos de la aplicación
│
├── database/
│   └── alisbook.sql             # Script SQL de la base de datos
│
├── resources/                   # Recursos adicionales
│
├── Funcionalidades.md           # Documentación detallada de funcionalidades
└── README.md                    # Este archivo
```

---

## 🔐 Credenciales de Acceso

El sistema utiliza el **número de documento** de los usuarios registrados como credencial de acceso.

### Usuarios de Prueba:

| Rol          | Documento | Contraseña | Nombre            |
|--------------|-----------|------------|-------------------|
| Administrador| 12345678  | 1234       | Juan Pérez        |
| Vendedor     | 87654321  | 1234       | Ana García        |
| Almacenero   | (otro)    | 1234       | (según BD)        |

**Nota:** La contraseña es `1234` para todos los usuarios del sistema.

### Proceso de Login:

1. Ingresar el número de documento del usuario
2. Ingresar la contraseña: `1234`
3. Click en "Ingresar"
4. Redirección automática al dashboard

---

## 📱 Módulos del Sistema

### 🏠 Dashboard Principal (main.php)

Centro de navegación con acceso rápido a todos los módulos del sistema. Muestra:
- Nombre del usuario actual y su rol
- Grid de módulos disponibles según permisos
- Navegación intuitiva con iconos

---

### 🛒 Módulo de Compras (compras.php)

**Propósito:** Registrar las compras realizadas a proveedores y agregar productos al inventario automáticamente.

**Flujo de trabajo:**

1. **Seleccionar proveedor** (obligatorio)
2. **Elegir forma de pago** (Efectivo, Tarjeta, Transferencia)
3. **Ingresar datos del documento** (Tipo y Número)
4. **Agregar productos dinámicamente:**
   - Click en "➕ Agregar Producto"
   - Llenar: Nombre, Categoría, Cantidad, Precio Compra, Precio Venta
   - Subtotal calculado automáticamente
   - Opción de eliminar productos con 🗑️
5. **Visualizar total general** calculado en tiempo real
6. **Registrar compra** - Los productos se agregan automáticamente al inventario

**Características especiales:**
- ✅ Los productos nuevos se crean automáticamente en el inventario
- ✅ Si el producto ya existe, se suma al stock existente
- ✅ Validaciones de campos obligatorios y valores positivos
- ✅ Historial de las últimas 10 compras

---

### 📦 Módulo de Inventario (inventario.php)

**Propósito:** Visualizar y gestionar todos los productos del sistema.

**Características:**

- **Búsqueda avanzada:** Por nombre, código, descripción o categoría
- **Filtros:**
  - Por categoría
  - Por nivel de stock (todos, con stock, sin stock, stock bajo)
- **Visualización:** Lista completa con código, nombre, categoría, stock, precios
- **Acciones:**
  - ✏️ **Editar:** Modificar información del producto (modal interactivo)
  - 🔄 **Cambiar estado:** Activar/desactivar productos

**Nota importante:** No permite agregar productos manualmente. Los productos se crean automáticamente al registrar compras.

---

### 💰 Módulo de Ventas (ventas.php)

**Propósito:** Procesar ventas de productos con control de stock y registro automático de clientes.

**Flujo de trabajo:**

1. **Datos del documento:** Tipo (Boleta/Factura/Ticket) y número
2. **Datos del cliente:**
   - Si el documento existe → Asocia al cliente existente
   - Si NO existe → Crea cliente automáticamente con los datos ingresados
3. **Seleccionar productos:**
   - Buscador en tiempo real
   - Solo muestra productos activos con stock disponible
   - Marcar checkbox para seleccionar
   - Ingresar cantidad (máximo = stock disponible)
4. **Cálculo automático:**
   - Subtotal por producto
   - Total de la venta
   - Cambio (si se ingresa monto pagado)
5. **Procesar venta:**
   - Descontar stock automáticamente
   - Registrar venta en la base de datos
   - Generar detalles de venta

**Validaciones:**
- ✅ Stock suficiente antes de procesar
- ✅ Cantidad máxima = stock disponible
- ✅ Campos obligatorios validados

---

### 👥 Módulo de Clientes (clientes.php)

**Propósito:** Gestionar la información de los clientes.

**Funcionalidades:**
- Visualizar lista completa de clientes
- Agregar nuevos clientes manualmente
- Editar información de clientes existentes
- Activar/desactivar clientes
- Buscar clientes por documento o nombre

**Datos almacenados:**
- Documento de identidad (único)
- Nombre completo
- Correo electrónico
- Teléfono
- Estado (Activo/Inactivo)
- Fecha de registro

---

### 🏭 Módulo de Proveedores (proveedores.php)

**Propósito:** Gestionar los proveedores que surten productos a la librería.

**Funcionalidades:**
- Crear nuevos proveedores
- Editar información de proveedores
- Activar/desactivar proveedores
- Búsqueda de proveedores

**Datos almacenados:**
- RUC/Documento
- Razón social
- Correo
- Teléfono
- Dirección
- Estado

---

### 📁 Módulo de Categorías (categorias.php)

**Propósito:** Gestionar las categorías de productos.

**Funcionalidades:**
- Crear nuevas categorías
- Editar categorías existentes
- Activar/desactivar categorías
- Solo se eliminan lógicamente (no se borran de la BD)

**Categorías predeterminadas:**
- Libros
- Útiles
- (Otras personalizables)

---

### 📊 Módulo de Reportes (reportes.php)

**Propósito:** Visualizar estadísticas y análisis del negocio.

**Reportes disponibles:**
- 📈 **Ventas totales** del periodo
- 📉 **Compras totales** del periodo
- 💰 **Ganancias** (ventas - costo de productos vendidos)
- 📦 **Productos más vendidos**
- 👥 **Clientes frecuentes**
- 📊 **Gráficos interactivos** con Chart.js

**Filtros:**
- Por rango de fechas
- Por categoría de producto
- Por cliente/proveedor

---

### 👤 Módulo de Perfil (perfil.php)

**Propósito:** Gestionar la información del usuario actual.

**Funcionalidades:**
- Ver datos personales
- Cambiar contraseña
- Actualizar información de contacto

---

### 👨‍💼 Módulo de Usuarios (usuarios.php)

**Propósito:** Gestión de usuarios del sistema (solo administradores).

**Funcionalidades:**
- Crear nuevos usuarios
- Asignar roles (Administrador, Vendedor, Almacenero)
- Editar información de usuarios
- Activar/desactivar usuarios
- Resetear contraseñas

---

## 🔄 Flujo de Trabajo Recomendado

### Configuración Inicial:

1. **Crear Categorías** (Libros, Útiles, etc.)
2. **Registrar Proveedores**
3. **Crear Usuarios** del sistema (si eres admin)

### Operación Diaria:

1. **Registrar Compras** → Se agregan productos al inventario automáticamente
2. **Verificar Inventario** → Revisar stock disponible
3. **Procesar Ventas** → Se descuenta stock automáticamente y se registran clientes
4. **Revisar Reportes** → Analizar estadísticas del negocio

---

## 🗄️ Base de Datos

### Tablas Principales:

- **usuarios** - Usuarios del sistema con roles
- **categorias** - Categorías de productos
- **productos** - Inventario de productos
- **clientes** - Información de clientes
- **proveedores** - Proveedores de productos
- **compras** - Registro de compras
- **detallecompras** - Productos de cada compra
- **ventas** - Registro de ventas
- **detalleventas** - Productos de cada venta
- **formaspago** - Formas de pago disponibles

### Relaciones Importantes:

```
compras → proveedores (muchos a uno)
compras → detallecompras (uno a muchos)
ventas → clientes (muchos a uno)
ventas → detalleventas (uno a muchos)
productos → categorias (muchos a uno)
```

---

## 🔒 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()` de PHP
- ✅ Uso de **PDO con prepared statements** (prevención de SQL Injection)
- ✅ Validación de sesiones en todas las páginas protegidas
- ✅ Sanitización de entradas con `htmlspecialchars()`
- ✅ Control de acceso por roles
- ✅ Cierre de sesión seguro

---

## 🐛 Troubleshooting

### La página no carga:
- ✅ Verificar que XAMPP esté corriendo (Apache y MySQL)
- ✅ Comprobar que la ruta sea: `http://localhost/Presentacion-Interfaz`

### Error de conexión a base de datos:
- ✅ Verificar que la BD `alisbook` exista en phpMyAdmin
- ✅ Revisar credenciales en `config/database.php`
- ✅ Asegurarse de que el SQL se importó correctamente

### No puedo iniciar sesión:
- ✅ Verificar que existan usuarios en la tabla `usuarios`
- ✅ Usar la contraseña `1234` para usuarios de prueba
- ✅ Verificar que el documento ingresado exista en la BD

### Los productos no aparecen en ventas:
- ✅ Verificar que los productos tengan stock > 0
- ✅ Verificar que los productos estén en estado "Activo"
- ✅ Primero registrar compras para tener productos en inventario

---

## 📚 Documentación Adicional

Para información más detallada sobre cada funcionalidad, consultar el archivo:
- **[Funcionalidades.md](Funcionalidades.md)** - Documentación exhaustiva de todas las funcionalidades

---

## 📝 Licencia

Este proyecto es un sistema académico/educativo. Siéntete libre de usarlo y modificarlo según tus necesidades.

---

## 👨‍💻 Soporte

Para reportar bugs o solicitar nuevas funcionalidades, contactar al equipo de desarrollo.

---

**Desarrollado con ❤️ para la gestión eficiente de librerías**
