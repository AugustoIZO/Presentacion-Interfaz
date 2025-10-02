# Guía de Instalación - Alisbook

## Requisitos previos
- XAMPP (Apache + MySQL + PHP) instalado
- phpMyAdmin funcionando

## Pasos de instalación:

### 1. Configurar la base de datos
1. Abre phpMyAdmin (normalmente en http://localhost/phpmyadmin)
2. Crea una nueva base de datos llamada `ALISBOOK_BD`
3. Selecciona la base de datos `ALISBOOK_BD`
4. Ve a la pestaña "SQL"
5. Copia y pega el contenido del archivo `database/alisbook.sql`
6. Ejecuta el script

### 2. Configurar la conexión
1. Abre el archivo `config/database.php`
2. Modifica las siguientes líneas según tu configuración:
   ```php
   define('DB_HOST', 'localhost');      // Servidor (normalmente localhost)
   define('DB_USERNAME', 'root');       // Usuario de MySQL (normalmente root)
   define('DB_PASSWORD', '');           // Contraseña de MySQL (normalmente vacía en XAMPP)
   define('DB_NAME', 'ALISBOOK_BD');    // Nombre de la base de datos
   ```

### 3. Mover archivos
1. Copia todos los archivos del proyecto a la carpeta `htdocs` de XAMPP
   (normalmente en `C:\xampp\htdocs\alisbook\`)
2. Asegúrate de que la estructura sea:
   ```
   htdocs/alisbook/
   ├── config/
   ├── includes/
   ├── database/
   ├── CSS/
   ├── HTML/
   ├── resources/
   ├── login.php
   ├── main.php
   ├── inventario.php
   ├── clientes.php
   ├── ventas.php
   ├── reportes.php
   └── index.php
   ```

### 4. Iniciar servicios
1. Abre el Panel de Control de XAMPP
2. Inicia Apache
3. Inicia MySQL

### 5. Acceder a la aplicación
1. Abre tu navegador
2. Ve a: `http://localhost/alisbook/`
3. Usa las credenciales de prueba:
   - Documento: `12345678` / Contraseña: `password` (Administrador)
   - Documento: `87654321` / Contraseña: `password` (Empleado)

## Estructura de la Base de Datos:
- **USUARIOS**: Gestión de usuarios del sistema
- **ROLES**: Definición de roles (Administrador/Empleado)
- **PRODUCTOS**: Inventario de productos
- **CATEGORIAS**: Categorización de productos
- **CLIENTES**: Base de datos de clientes
- **VENTAS**: Registro de ventas
- **DETALLEVENTAS**: Detalle de productos vendidos
- **COMPRAS**: Registro de compras a proveedores
- **DETALLECOMPRAS**: Detalle de productos comprados
- **PROVEEDORES**: Base de datos de proveedores
- **FORMAS_PAGO**: Métodos de pago disponibles
- **PERMISOS**: Sistema de permisos por rol

## Funcionalidades implementadas:
- ✅ Sistema de login con autenticación
- ✅ Dashboard con estadísticas en tiempo real
- ✅ Gestión de inventario
- ✅ Listado de clientes
- ✅ Historial de ventas
- ✅ Reportes y estadísticas
- ✅ Sistema de roles y permisos

## Usuarios de prueba incluidos:
- **Administrador** - Documento: `12345678` - password: `password`
- **Empleado** - Documento: `87654321` - password: `password`

## Próximos pasos:
1. Cambiar las contraseñas por defecto
2. Crear más usuarios según sea necesario
3. Agregar más productos y categorías
4. Personalizar la aplicación según tus requisitos

## Solución de problemas:
- Si ves errores de conexión, verifica que MySQL esté ejecutándose
- Si no puedes acceder a la aplicación, verifica que Apache esté funcionando
- Si hay errores de permisos, asegúrate de que los archivos estén en la carpeta correcta
- Si el login no funciona, verifica que la base de datos `ALISBOOK_BD` exista y tenga datos