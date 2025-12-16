<?php
/**
 * Script temporal para actualizar contraseñas sin hashear a formato hasheado
 * IMPORTANTE: Ejecutar solo una vez y luego eliminar este archivo
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Actualizar Contraseñas</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            max-width: 800px; 
            margin: 0 auto;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .success { 
            color: green; 
            padding: 10px; 
            background: #d4edda; 
            border-radius: 4px; 
            margin: 10px 0;
        }
        .error { 
            color: red; 
            padding: 10px; 
            background: #f8d7da; 
            border-radius: 4px; 
            margin: 10px 0;
        }
        .info { 
            color: #856404; 
            padding: 10px; 
            background: #fff3cd; 
            border-radius: 4px; 
            margin: 10px 0;
        }
        .warning {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 20px 0;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 0;
        }
        button:hover {
            background: #45a049;
        }
        .btn-delete {
            background: #f44336;
        }
        .btn-delete:hover {
            background: #da190b;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔐 Actualización de Contraseñas</h1>";

try {
    $db = new Database();
    
    if (isset($_POST['actualizar'])) {
        // Obtener todos los usuarios
        $sql = "SELECT IDUSUARIO, DOCUMENTO, NOMBRECOMPLETO, CLAVE FROM USUARIOS";
        $usuarios = $db->query($sql)->fetchAll();
        
        $actualizados = 0;
        $yaHasheados = 0;
        $errores = 0;
        
        echo "<h2>Procesando usuarios...</h2>";
        
        foreach ($usuarios as $usuario) {
            $clave = $usuario['CLAVE'];
            
            // Verificar si la contraseña ya está hasheada
            // Las contraseñas hasheadas con password_hash() comienzan con $2y$
            if (substr($clave, 0, 4) === '$2y$') {
                echo "<div class='info'>✓ Usuario {$usuario['NOMBRECOMPLETO']} (ID: {$usuario['IDUSUARIO']}) - Contraseña ya hasheada</div>";
                $yaHasheados++;
                continue;
            }
            
            // Hashear la contraseña
            $claveHasheada = password_hash($clave, PASSWORD_DEFAULT);
            
            // Actualizar en la base de datos
            $updateSql = "UPDATE USUARIOS SET CLAVE = ? WHERE IDUSUARIO = ?";
            if ($db->query($updateSql, [$claveHasheada, $usuario['IDUSUARIO']])) {
                echo "<div class='success'>✓ Usuario {$usuario['NOMBRECOMPLETO']} (ID: {$usuario['IDUSUARIO']}) - Contraseña actualizada correctamente</div>";
                $actualizados++;
            } else {
                echo "<div class='error'>✗ Error al actualizar usuario {$usuario['NOMBRECOMPLETO']} (ID: {$usuario['IDUSUARIO']})</div>";
                $errores++;
            }
        }
        
        echo "<h2>Resumen:</h2>";
        echo "<div class='success'>Contraseñas actualizadas: $actualizados</div>";
        echo "<div class='info'>Ya hasheadas (sin cambios): $yaHasheados</div>";
        if ($errores > 0) {
            echo "<div class='error'>Errores: $errores</div>";
        }
        
        echo "<div class='warning'>
                <strong>⚠️ IMPORTANTE:</strong><br>
                - Las contraseñas han sido actualizadas correctamente.<br>
                - Todas las contraseñas antiguas ahora están hasheadas de forma segura.<br>
                - Por seguridad, <strong>DEBES ELIMINAR este archivo (actualizar_passwords.php)</strong> del servidor.<br>
              </div>";
        
        echo "<form method='POST' onsubmit='return confirm(\"¿Estás seguro de que quieres eliminar este archivo?\");'>
                <button type='submit' name='eliminar' class='btn-delete'>🗑️ Eliminar este archivo ahora</button>
              </form>";
        
    } elseif (isset($_POST['eliminar'])) {
        // Intentar eliminar el archivo
        if (unlink(__FILE__)) {
            echo "<div class='success'>✓ Archivo eliminado correctamente. Redirigiendo...</div>";
            echo "<script>setTimeout(function(){ window.location.href='main.php'; }, 2000);</script>";
        } else {
            echo "<div class='error'>✗ No se pudo eliminar el archivo automáticamente. Por favor, elimínalo manualmente.</div>";
        }
    } else {
        // Contar usuarios
        $sql = "SELECT COUNT(*) as total FROM USUARIOS";
        $total = $db->query($sql)->fetch()['total'];
        
        echo "<p>Este script actualizará las contraseñas de todos los usuarios del sistema a un formato hasheado seguro.</p>";
        echo "<div class='info'>Total de usuarios en el sistema: <strong>$total</strong></div>";
        
        echo "<div class='warning'>
                <strong>⚠️ ADVERTENCIA:</strong><br>
                - Este proceso actualizará todas las contraseñas que no estén hasheadas.<br>
                - Las contraseñas ya hasheadas no serán modificadas.<br>
                - Después de ejecutar este script, <strong>DEBES eliminarlo</strong> por seguridad.<br>
                - Los usuarios seguirán usando las mismas contraseñas para iniciar sesión.
              </div>";
        
        echo "<form method='POST'>
                <button type='submit' name='actualizar'>🔄 Actualizar Contraseñas Ahora</button>
              </form>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "      <br><a href='main.php'>← Volver al inicio</a>
        </div>
    </body>
</html>";
?>
