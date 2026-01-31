<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/property_controller.php';

$controller= new PropertyController($pdo);
$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['user_id'];

function v_text($v) {
    if (!isset($v) || trim($v) === '') return false;
    $s = trim(strip_tags($v));
    return preg_match('/^[\p{L}\d\s\'\-.,:;()¿?¡!]+$/u', $s);
}

function v_title($v){
    if (!isset($v) || trim($v) === '') return false;
    $s = trim(strip_tags($v));
    return preg_match('/^[\p{L}\s\'\-]+$/u', $s);
}

function v_alpha($v){
    if (!isset($v) || trim($v) === '') return false;
    $s = trim(strip_tags($v));
    return preg_match('/^[\p{L}\s\'\-.,]+$/u', $s);
}

function v_int($v){
    if (!isset($v) || $v === '') return false;
    return filter_var($v, FILTER_VALIDATE_INT) !== false;
}

function v_float($v){
    if (!isset($v) || $v === '') return false;
    $v = str_replace(',', '.', $v);
    return filter_var($v, FILTER_VALIDATE_FLOAT) !== false;
}

function validate_image_file($file){
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return false;
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    return in_array($mime, ['image/jpeg','image/png','image/webp']);
}

switch($action){
    case 'registrar':
        $errors = [];
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $capacidad = $_POST['capacidad'] ?? '';
        $foto = $_FILES['foto'] ?? null;

        if (!v_alpha($titulo)) $errors[] = 'Título inválido (solo letras, espacios y signos permitidos).';
        if (!v_text($descripcion)) $errors[] = 'Descripción inválida.';
        if (!v_float($precio) || (float)$precio <= 0) $errors[] = 'Precio inválido.';
        if (!v_alpha($ubicacion)) $errors[] = 'Ubicación inválida (solo letras y espacios).';
        if (!v_int($capacidad) || (int)$capacidad <= 0) $errors[] = 'Capacidad inválida.';
        if (!validate_image_file($foto)) $errors[] = 'Imagen inválida (jpg, png, webp).';

        if (!empty($errors)){
            $msg = urlencode(implode('; ', $errors));
            header("Location: ../views/formulario_propiedad.php?error={$msg}");
            exit();
        }

        $controller->agregar_propiedad(
            trim($titulo), 
            trim($descripcion), 
            (float)str_replace(',', '.', $precio), 
            trim($ubicacion), 
            $foto,
            (int)$capacidad,
        );
        break;
    case 'eliminar':
        $controller->eliminar_propiedad($_GET['id'] ?? '');
        break;
    case 'actualizar':
        $errors = [];
        $uuid = $_POST['uuid'] ?? '';
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $foto = $_FILES['foto'] ?? null;
        $id_usuario = $_SESSION['user_id'];

        if (empty($uuid)) $errors[] = 'UUID de propiedad inválido.';
        if (!v_alpha($titulo)) $errors[] = 'Título inválido.';
        if (!v_text($descripcion)) $errors[] = 'Descripción inválida.';
        if (!v_float($precio) || (float)$precio <= 0) $errors[] = 'Precio inválido.';
        if (!v_alpha($ubicacion)) $errors[] = 'Ubicación inválida.';
        if (isset($foto['name']) && $foto['name'] !== '' && !validate_image_file($foto)) $errors[] = 'Imagen inválida (jpg, png, webp).';

        if (!empty($errors)){
            $msg = urlencode(implode('; ', $errors));
            header("Location: ../views/formulario_propiedad.php?id={$uuid}&error={$msg}");
            exit();
        }

        $controller->actualizar_propiedad($uuid, trim($titulo), trim($descripcion), (float)str_replace(',', '.', $precio), trim($ubicacion), $_FILES['foto'], $id_usuario);
        break;
    case 'actualizar_comodidades':
        $controller->actualizar_comodidades($_POST['uuid'], $_POST['comodidades']);
        break;
    case 'agregar_comodidades':
        $controller->agregar_comodidades($_POST['uuid'], $_POST['comodidades']);
        break;
    default:
        header("Location: ../public/index.php");
        exit();
}

