<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/user_controller.php';

$controller = new UserController($pdo);

$action = $_GET['action'] ?? '';

try{
    if ($action === 'registrar') {
        $controller->registrar();
    } elseif ($action === 'login') {
        $controller->login();
    } elseif ($action === 'logout') {
        $controller->logout();
    } elseif ($action === 'solicitar_recuperacion') {
        $controller->solicitar_recuperacion();
    } elseif ($action === 'reset_password') {
        $controller->reset_password();
    }
} catch (Exception $e) {
    header ("Location: ../views/login.php?msg=" . urlencode($e->getMessage()));
    exit();
}
