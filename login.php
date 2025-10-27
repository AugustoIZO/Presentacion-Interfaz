<?php
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';

// Si ya está logueado, redirigir al main
if ($auth->isLoggedIn()) {
    header('Location: main.php');
    exit();
}

// Procesar el formulario de login
if ($_POST) {
    $documento = trim($_POST['documento'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($documento) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        try {
            if ($auth->login($documento, $password)) {
                header('Location: main.php');
                exit();
            } else {
                $error = 'Documento o contraseña incorrectos. Verifique las credenciales de prueba.';
            }
        } catch (Exception $e) {
            $error = 'Error de conexión a la base de datos. Verifique que XAMPP esté corriendo y la BD esté importada.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Login - Alisbook</title>
</head>
<body class="login-body">
    <div class="background">
        <div class="rectangulo-caja">
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="rectangulo-usuario">
                    <input type="text" name="documento" placeholder="Número de Documento" required 
                           value="<?php echo htmlspecialchars($_POST['documento'] ?? ''); ?>">
                </div>
                <div class="rectangulo-password">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <div class="boton-ingresar">
                    <button type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>