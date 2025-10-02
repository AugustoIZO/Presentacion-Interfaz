<?php
require_once 'config/database.php';

try {
    $db = new Database();
    
    // Contraseñas en texto plano que se hashearán
    $usuarios = [
        [
            'documento' => '12345678',
            'nombre' => 'Administrador Principal',
            'correo' => 'admin@alisbook.com',
            'password' => 'admin123',
            'rol' => 1 // Administrador
        ],
        [
            'documento' => '87654321',
            'nombre' => 'Juan Pérez',
            'correo' => 'juan@alisbook.com',
            'password' => 'empleado123',
            'rol' => 2 // Empleado
        ],
        [
            'documento' => '11111111',
            'nombre' => 'María González',
            'correo' => 'maria@alisbook.com',
            'password' => 'maria123',
            'rol' => 2 // Empleado
        ]
    ];
    
    echo "<h2>🔧 Configurando usuarios para Alisbook</h2>";
    
    // Primero, insertar roles si no existen
    echo "<p>📝 Verificando roles...</p>";
    $roles = [
        ['id' => 1, 'descripcion' => 'Administrador'],
        ['id' => 2, 'descripcion' => 'Empleado']
    ];
    
    foreach ($roles as $rol) {
        $sql = "INSERT IGNORE INTO ROLES (IDROL, DESCRIPCION, FECHAREGISTRO) VALUES (?, ?, NOW())";
        $db->query($sql, [$rol['id'], $rol['descripcion']]);
        echo "✅ Rol '{$rol['descripcion']}' verificado<br>";
    }
    
    echo "<p>👤 Configurando usuarios...</p>";
    
    // Eliminar usuarios existentes para evitar duplicados
    $sql = "DELETE FROM USUARIOS WHERE DOCUMENTO IN ('12345678', '87654321', '11111111')";
    $db->query($sql);
    echo "🗑️ Usuarios anteriores eliminados<br>";
    
    // Insertar usuarios con contraseñas recién hasheadas
    foreach ($usuarios as $usuario) {
        $passwordHash = password_hash($usuario['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO USUARIOS (DOCUMENTO, NOMBRECOMPLETO, CORREO, CLAVE, ESTADO, FECHAREGISTRO, IDROL) 
                VALUES (?, ?, ?, ?, 'Activo', NOW(), ?)";
        
        $db->query($sql, [
            $usuario['documento'],
            $usuario['nombre'],
            $usuario['correo'],
            $passwordHash,
            $usuario['rol']
        ]);
        
        // Verificar que la contraseña funcione
        $testLogin = password_verify($usuario['password'], $passwordHash);
        $status = $testLogin ? "✅" : "❌";
        
        echo "$status Usuario creado: {$usuario['nombre']} (Doc: {$usuario['documento']}, Pass: {$usuario['password']})<br>";
    }
    
    echo "<br><div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 ¡Usuarios creados exitosamente!</h3>";
    echo "<h4>📋 Credenciales de acceso:</h4>";
    echo "<ul>";
    foreach ($usuarios as $usuario) {
        $rolNombre = $usuario['rol'] == 1 ? 'Administrador' : 'Empleado';
        echo "<li><strong>{$usuario['nombre']}</strong> ({$rolNombre})<br>";
        echo "   📄 Documento: <code>{$usuario['documento']}</code><br>";
        echo "   🔑 Contraseña: <code>{$usuario['password']}</code></li><br>";
    }
    echo "</ul>";
    echo "</div>";
    
    echo "<p><a href='login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Ir al Login</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; color: #d00;'>";
    echo "<h3>❌ Error al configurar usuarios:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar que XAMPP esté ejecutándose (Apache + MySQL)</li>";
    echo "<li>Verificar que la base de datos 'alisbook' exista</li>";
    echo "<li>Importar el archivo database/alisbook.sql en phpMyAdmin</li>";
    echo "</ul>";
    echo "</div>";
}
?>