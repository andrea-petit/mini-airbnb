<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/user_controller.php';

$controller = new UserController($pdo);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'registrar':
            $controller->registrar();
            break;

        case 'login':
            $controller->login();
            break;

        case 'logout':
            $controller->logout();
            break;

        case 'guardar_seguridad':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: ../public/index.php");
                exit();
            }

            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Error: token CSRF inválido");
            }

            $id_usuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : (int)($_POST['id_usuario'] ?? 0);
            $id_pregunta = (int)($_POST['id_pregunta'] ?? 0);
            $respuesta = trim($_POST['respuesta'] ?? '');

            if ($id_usuario <= 0 || $id_pregunta <= 0 || $respuesta === '') {
                header("Location: ../views/configurar_seguridad.php?id_usuario={$id_usuario}&error=campos_vacios");
                exit();
            }

            if ($controller->tiene_pregunta_configurada($id_usuario)) {
                header("Location: ../views/login.php?msg=seguridad_configurada");
                exit();
            }

            $controller->procesar_seguridad($id_usuario, $id_pregunta, $respuesta);
            break;

        case 'solicitar_recuperacion':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: ../views/olvido_password.php");
                exit();
            }

            $email = trim($_POST['email'] ?? '');
            if ($email === '') {
                header("Location: ../views/olvido_password.php?error=campo_vacio");
                exit();
            }

            header("Location: ../views/verificar_seguridad.php?email=" . urlencode($email));
            exit();
            break;

        case 'verificar_pregunta':
            $email = $_POST['email'] ?? '';
            $id_pregunta = $_POST['id_pregunta'] ?? null;
            $respuesta = $_POST['respuesta'] ?? '';
            
            $es_valido = $controller->validar_identidad_recuperacion($email, $id_pregunta, $respuesta);

            if ($es_valido) {
                header("Location: ../views/reset_password.php?email=" . urlencode($email));
            } else {
                header("Location: ../views/verificar_seguridad.php?email=" . urlencode($email) . "&error=respuesta_incorrecta");
            }
            break;

        case 'reset_password':
            $controller->reset_password();
            break;

        default:
            header("Location: ../public/index.php");
            break;
    }
} catch (Exception $e) {
    header("Location: ../views/login.php?error=" . urlencode($e->getMessage()));
    exit();
}