<?php
// Script temporal para generar contraseñas hasheadas correctas
$passwords = [
    'admin123' => '',
    'empleado123' => '', 
    'maria123' => ''
];

echo "<h2>🔑 Generando contraseñas hasheadas correctas</h2>";
echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Contraseña</th><th>Hash generado</th></tr>";

foreach ($passwords as $password => $hash) {
    $generatedHash = password_hash($password, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td><strong>$password</strong></td>";
    echo "<td><code>$generatedHash</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>📋 SQL actualizado:</h3>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "-- Usuarios con contraseñas hasheadas correctas\n";
echo "DELETE FROM USUARIOS WHERE DOCUMENTO IN ('12345678', '87654321', '11111111');\n\n";
echo "INSERT INTO `USUARIOS` (`DOCUMENTO`, `NOMBRECOMPLETO`, `CORREO`, `CLAVE`, `ESTADO`, `FECHAREGISTRO`, `IDROL`) VALUES\n";
echo "('12345678', 'Administrador Principal', 'admin@alisbook.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'Activo', NOW(), 1),\n";
echo "('87654321', 'Juan Pérez', 'juan@alisbook.com', '" . password_hash('empleado123', PASSWORD_DEFAULT) . "', 'Activo', NOW(), 2),\n";
echo "('11111111', 'María González', 'maria@alisbook.com', '" . password_hash('maria123', PASSWORD_DEFAULT) . "', 'Activo', NOW(), 2);\n";
echo "</pre>";
?>