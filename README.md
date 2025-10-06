# 📚 Alisbook - Sistema de Gestión de Librería

Un sistema completo de gestión para librerías desarrollado en PHP con interfaz moderna y responsiva.

## 🚀 Características Principales

### 📦 **Gestión de Inventario**
- Control completo de productos y stock
- Categorización de productos
- Alertas de stock bajo
- Búsqueda y filtrado avanzado
- Registro de precios de compra y venta

### 💰 **Sistema de Ventas**
- Registro de ventas con selección de productos
- Cálculo automático de totales
- Control de stock en tiempo real
- Diferentes formas de pago
- Cálculo de cambio automático

### 🛒 **Gestión de Compras**
- Compras a proveedores
- Registro de productos nuevos
- Actualización automática de inventario
- Control de costos
- Historial de compras

### 👥 **Administración de Clientes**
- Registro y gestión de clientes
- Estados activos/inactivos
- Información de contacto
- Historial de clientes

### 📊 **Reportes y Analytics**
- Dashboard con estadísticas en tiempo real
- Reportes de stock crítico
- Análisis de ventas mensuales
- Métricas de productos y clientes

### 🔐 **Sistema de Autenticación**
- Login seguro con hash de contraseñas
- Control de sesiones
- Diferentes roles de usuario
- Protección de rutas

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Servidor:** Apache (XAMPP)
- **Arquitectura:** MVC Pattern
- **Seguridad:** PDO para prevenir SQL Injection

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.3+
- Servidor web Apache
- Extensiones PHP requeridas:
  - PDO
  - PDO_MySQL
  - Session

## ⚙️ Instalación

### 1. Clonar el Repositorio
```bash
git clone https://github.com/AugustoIZO/Presentacion-Interfaz.git
cd Presentacion-Interfaz
```

### 2. Configurar XAMPP
1. Instalar XAMPP desde [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Iniciar Apache y MySQL desde el panel de control de XAMPP
3. Copiar la carpeta del proyecto a `C:\xampp\htdocs\`

### 3. Configurar Base de Datos
1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Crear una nueva base de datos llamada `alisbook`
3. Importar el archivo SQL: `database/alisbook.sql`

### 4. Configurar Conexión
El archivo `config/database.php` está configurado para XAMPP por defecto:
```php
$host = 'localhost';
$dbname = 'alisbook';
$username = 'root';
$password = '';
```

### 5. Configurar Usuarios Iniciales
1. Ejecutar: `http://localhost/Presentacion-Interfaz/setup_users.php`
2. Esto creará los usuarios por defecto del sistema

### 6. Acceder al Sistema
- URL: `http://localhost/Presentacion-Interfaz/`
- Usuario por defecto: `admin`
- Contraseña por defecto: `admin123`

## 👤 Usuarios por Defecto

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | admin123 | Administrador |
| vendedor | vendedor123 | Vendedor |
| gerente | gerente123 | Gerente |

## 📁 Estructura del Proyecto

```
Presentacion-Interfaz/
├── 📁 config/
│   └── database.php          # Configuración de base de datos
├── 📁 CSS/
│   └── style.css            # Estilos centralizados
├── 📁 database/
│   └── alisbook.sql         # Esquema de base de datos
├── 📁 includes/
│   └── auth.php             # Sistema de autenticación
├── 📁 resources/
│   └── ...                  # Recursos estáticos
├── clientes.php             # Gestión de clientes
├── compras.php              # Sistema de compras
├── inventario.php           # Gestión de inventario
├── login.php                # Página de login
├── main.php                 # Dashboard principal
├── reportes.php             # Reportes y analytics
├── setup_users.php          # Configuración inicial
└── ventas.php               # Sistema de ventas
```

## 🎨 Características de UI/UX

### Diseño Moderno
- Interfaz con gradientes y efectos glassmorphism
- Animaciones suaves y transiciones fluidas
- Cards interactivas con efectos hover
- Tipografía moderna con fuente Poppins

### Responsive Design
- Diseño mobile-first
- Adaptable a tablets y dispositivos móviles
- Grid layouts flexibles
- Touch-friendly para dispositivos táctiles

### Experiencia de Usuario
- Navegación intuitiva
- Logo clickeable para volver al dashboard
- Formularios con validación en tiempo real
- Feedback visual inmediato

## 🔒 Seguridad

- **Autenticación:** Sistema de login con hash de contraseñas
- **SQL Injection:** Prevención mediante PDO prepared statements
- **Sesiones:** Control seguro de sesiones de usuario
- **Validación:** Validación tanto en frontend como backend
- **Sanitización:** Escape de datos con htmlspecialchars()

## 🚀 Funcionalidades Avanzadas

### Dashboard Interactivo
- Estadísticas en tiempo real
- Gráficos de rendimiento
- Alertas de stock crítico
- Métricas de ventas mensuales

### Gestión de Stock
- Control automático de inventario
- Alertas de productos agotados
- Actualización en tiempo real
- Historial de movimientos

### Sistema de Búsqueda
- Búsqueda por múltiples criterios
- Filtros dinámicos
- Resultados en tiempo real
- Paginación eficiente

## 📱 Compatibilidad

- **Navegadores:** Chrome, Firefox, Safari, Edge
- **Dispositivos:** Desktop, Tablet, Mobile
- **Resoluciones:** Desde 320px hasta 4K
- **Sistemas:** Windows, macOS, Linux

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abrir un Pull Request

## 📝 Changelog

### v2.0.0 (Octubre 2025)
- ✨ Rediseño completo del dashboard
- 🎨 Refactorización de CSS con diseño moderno
- 📱 Mejoras en responsive design
- 🔗 Logo clickeable en todas las páginas
- 🎯 Mejoras en UX/UI

### v1.0.0 (Inicial)
- 📦 Sistema de inventario básico
- 💰 Módulo de ventas
- 🛒 Gestión de compras
- 👥 Administración de clientes
- 📊 Reportes básicos

## 🐛 Reportar Problemas

Si encuentras algún problema o tienes sugerencias:
1. Revisa los [issues existentes](https://github.com/AugustoIZO/Presentacion-Interfaz/issues)
2. Crea un nuevo issue con descripción detallada
3. Incluye pasos para reproducir el problema

## 📧 Contacto

- **Desarrollador:** AugustoIZO
- **Proyecto:** [Presentacion-Interfaz](https://github.com/AugustoIZO/Presentacion-Interfaz)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

## 🔧 Solución de Problemas Comunes

### MySQL no inicia en XAMPP
```bash
# En el puerto 3306
netstat -ano | findstr :3306
# Detener proceso si está en uso
taskkill /PID [ID_PROCESO] /F
```

### Error de conexión a base de datos
- Verificar que MySQL esté ejecutándose
- Confirmar credenciales en `config/database.php`
- Asegurar que la base de datos `alisbook` existe

### Problemas de permisos
- Verificar permisos de escritura en carpeta del proyecto
- Ejecutar XAMPP como administrador si es necesario

---

**¡Gracias por usar Alisbook! 📚✨**