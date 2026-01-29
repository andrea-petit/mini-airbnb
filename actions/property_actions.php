<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/property_controller.php';

$controller= new PropertyController($pdo);
$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['user_id'];

switch($action){
    case 'registrar':
        $controller->agregar_propiedad(
            $_POST['titulo'], 
            $_POST['descripcion'], 
            $_POST['precio'], 
            $_POST['ubicacion'], 
            $_FILES['foto'],
            $_POST['capacidad'],
        );
        break;
    case 'eliminar':
        $controller->eliminar_propiedad($_GET['id']);
        break;
    case 'actualizar':
        $controller->actualizar_propiedad($_POST['id_propiedad'], $_POST['titulo'], $_POST['descripcion'], $_POST['precio'],  $_POST['ubicacion'], $_FILES['foto'],);
        break;
    case 'actualizar_comodidades':
        $controller->actualizar_comodidades($_POST['id_propiedad'], $_POST['comodidades']);
        break;
    case 'agregar_comodidades':
        $controller->agregar_comodidades($_POST['id_propiedad'], $_POST['comodidades']);
        break;
    default:
        header("Location: ../public/index.php");
        exit();
}

