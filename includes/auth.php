<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function login($documento, $password) {
        $sql = "SELECT u.IDUSUARIO, u.DOCUMENTO, u.CLAVE, u.NOMBRECOMPLETO, r.DESCRIPCION as ROL 
                FROM USUARIOS u 
                INNER JOIN ROLES r ON u.IDROL = r.IDROL 
                WHERE u.DOCUMENTO = ? AND u.ESTADO = 'Activo'";
        $stmt = $this->db->query($sql, [$documento]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Verificar contraseña hasheada
        // Soporta también contraseñas sin hashear para compatibilidad temporal
        if (password_verify($password, $user['CLAVE'])) {
            // Contraseña hasheada correcta
            $_SESSION['user_id'] = $user['IDUSUARIO'];
            $_SESSION['documento'] = $user['DOCUMENTO'];
            $_SESSION['nombre'] = $user['NOMBRECOMPLETO'];
            $_SESSION['rol'] = $user['ROL'];
            $_SESSION['logged_in'] = true;
            
            return true;
        } elseif ($password === $user['CLAVE']) {
            // Compatibilidad con contraseñas sin hashear (actualizar automáticamente)
            $_SESSION['user_id'] = $user['IDUSUARIO'];
            $_SESSION['documento'] = $user['DOCUMENTO'];
            $_SESSION['nombre'] = $user['NOMBRECOMPLETO'];
            $_SESSION['rol'] = $user['ROL'];
            $_SESSION['logged_in'] = true;
            
            // Actualizar la contraseña a formato hasheado
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE USUARIOS SET CLAVE = ? WHERE IDUSUARIO = ?";
            $this->db->query($updateSql, [$hashedPassword, $user['IDUSUARIO']]);
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'documento' => $_SESSION['documento'],
                'nombre' => $_SESSION['nombre'],
                'rol' => $_SESSION['rol']
            ];
        }
        return null;
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>