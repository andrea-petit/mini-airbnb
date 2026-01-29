<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/reserva_controller.php';

$controller = new ReservaController($pdo);

$action = $_GET['action'] ?? '';

try{
    if ($action === 'crear') {
        $controller->crear_reserva();
    } elseif ($action === 'cancelar') {
        $controller->cancelar_reserva();
    } elseif ($action === 'confirmar') {
        $controller->confirmar_reserva();
    } elseif ($action === 'fechas_ocupadas') {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'id inválido']);
            exit();
        }
        $fechas = $controller->obtener_fechas_ocupadas($id);
        header('Content-Type: application/json');
        echo json_encode($fechas);
        exit();
    }
} catch (Exception $e) {
    header ("Location: ../views/index.php?msg=" . urlencode($e->getMessage()));
    exit();
}