# 🔐 Credenciales de Acceso - Alisbook

## 👤 Información de Login

**TODOS los usuarios tienen la contraseña: `1234`**

### Usuarios Disponibles:

| Documento | Nombre | Rol | Contraseña |
|-----------|---------|-----|------------|
| 12345678 | Administrador Principal | Administrador | 1234 |
| 87654321 | Juan Pérez | Empleado | 1234 |
| 11111111 | María González | Empleado | 1234 |
| 99999999 | Usuario Test | Empleado | 1234 |
| 88888888 | Usuario API | Empleado | 1234 |

## 🚀 Instrucciones de Acceso

1. **Abrir el sistema**: `http://localhost/Presentacion-Interfaz/`
2. **Login**: 
   - **Usuario**: Número de documento (ej: 12345678)
   - **Contraseña**: 1234
3. **Navegación**: Acceso completo a todos los módulos

## ✅ Cambios Realizados

- ❌ **Eliminado**: archivo `setup_users.php`
- ❌ **Eliminado**: archivo `generate_hashes.php`
- ✅ **Simplificado**: Una sola contraseña para todos (1234)
- ✅ **Login**: Usar número de documento como usuario
- ✅ **Contraseñas**: Almacenadas en texto plano (sin hash)

## 🔧 Notas Técnicas

- Las contraseñas están almacenadas en texto plano en la base de datos
- El sistema utiliza comparación directa de strings
- Todas las sesiones están protegidas
- Sistema simplificado de autenticación

---
**Última actualización**: Octubre 2025