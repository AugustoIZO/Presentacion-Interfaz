# 📚 ALISBOOK - Descripción de Funcionalidades

## 🏢 Sistema de Gestión Integral para Librerías

Alisbook es un sistema completo de gestión, diseñado específicamente para librerías. Desarrollado en PHP con MySQL, ofrece una interfaz moderna para la administración integral del negocio.

---

## 🔐 SISTEMA DE AUTENTICACIÓN

### Login Simplificado
- **Acceso por documento**: Utiliza número de documento como usuario
- **Contraseña unificada**: Todos los usuarios usan "1234" como contraseña


---

## 🏠 DASHBOARD PRINCIPAL

### Panel de Control Moderno
- **Estadísticas en tiempo real**: Visualización de métricas clave del negocio
- **Diseño moderno**: Interface con gradientes y animaciones CSS
- **Navegación intuitiva**: Acceso rápido a todos los módulos
- **Información del usuario**: Identificación clara del usuario actual

### Tarjetas de Estadísticas
- **Total de Productos**: Contador de productos en inventario
- **Ventas del Día**: Resumen de ventas diarias
- **Stock Bajo**: Alertas de productos con poco inventario
- **Clientes Registrados**: Base de datos de clientes

---

## 📦 GESTIÓN DE INVENTARIO

### Administración Completa de Productos
- **CRUD completo**: Crear, leer, actualizar y eliminar productos
- **Categorización**: Organización por categorías (Libros, Útiles, etc.)
- **Control de stock**: Seguimiento preciso de inventario
- **Precios duales**: Precio de compra y precio de venta

### Funcionalidades Avanzadas
- **Búsqueda inteligente**: Filtrado por nombre, código, descripción, categoría
- **Filtros múltiples**: Por categoría, nivel de stock, estado
- **Edición modal**: Modulo para editar productos
- **Validaciones**: Control de precios, stock y datos requeridos
- **Alertas visuales**: Indicadores de stock (bueno/regular/crítico)

### Campos de Producto
- **Información básica**: Código, nombre, descripción
- **Clasificación**: Categoría asignada
- **Inventario**: Stock actual con alertas
- **Precios**: Precio de compra y venta con validaciones
- **Disponibilidad**: Indicador automático según stock

---

## 💰 SISTEMA DE VENTAS

### Procesamiento de Ventas Completo
- **Venta multiproduto**: Selección de múltiples productos en una sola venta
- **Cálculo automático**: Total, cambio y subtotales en tiempo real
- **Validación de stock**: Verificación automática de disponibilidad
- **Tipos de documento**: Boleta, Factura, Ticket
- **Control de inventario**: Actualización automática del stock

### Gestión de Clientes Integrada
- **Información completa**: Nombre, documento, correo, teléfono
- **Campos opcionales**: Correo y teléfono no son obligatorios
- **Integración directa**: Los datos aparecen inmediatamente en el módulo de clientes

### Funcionalidades de Venta
- **Control de cantidades**: Validación según stock disponible
- **Cálculo dinámico**: Actualización automática de totales
- **Gestión de pagos**: Monto pagado y cálculo de cambio
- **Historial**: Registro de todas las ventas realizadas

---

## 🛒 GESTIÓN DE COMPRAS

### Administración de Compras a Proveedores
- **Registro de compras**: Control de adquisiciones de inventario
- **Actualización de stock**: Incremento automático del inventario
- **Control de proveedores**: Gestión de información de proveedores
- **Tipos de documento**: Facturas, recibos, órdenes de compra
- **Historial completo**: Seguimiento de todas las compras

### Funcionalidades de Compra
- **Productos múltiples**: Compra de varios productos simultáneamente
- **Precios de compra**: Registro del costo real de adquisición
- **Actualización automática**: Stock se incrementa automáticamente

---

## 👥 ADMINISTRACIÓN DE CLIENTES

### Base de Datos Completa de Clientes
- **Registro completo**: Documento, nombre, correo, teléfono
- **Registro automático**: Creación durante ventas
- **Fechas de registro**: Control temporal de incorporación

### Información del Cliente
- **Datos de contacto**: Correo electrónico y teléfono
- **Historial**: Fecha de primera incorporación

---

## 📊 SISTEMA DE REPORTES

### Análisis y Estadísticas
- **Reportes de ventas**: Análisis de rendimiento de ventas
- **Estadísticas de productos**: Productos más vendidos
- **Análisis de inventario**: Control de rotación de stock
- **Reportes financieros**: Análisis de ingresos y costos

---

## 🎨 CARACTERÍSTICAS TÉCNICAS

### Tecnologías Utilizadas
- **Backend**: PHP
- **Base de Datos**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
---

## 🚀 CARACTERÍSTICAS DESTACADAS

### Automatización Inteligente
- **Registro automático de clientes**: Durante las ventas
- **Actualización de inventario**: En ventas y compras
- **Cálculos automáticos**: Totales, cambios, subtotales
- **Validaciones en tiempo real**: Precios, stock, datos

### Escalabilidad
- **Código modular**: Fácil mantenimiento y expansión
- **Base de datos normalizada**: Estructura eficiente

