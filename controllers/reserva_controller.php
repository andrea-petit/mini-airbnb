<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/reserva_model.php';
require_once __DIR__ . '/../models/property_model.php';

class ReservaController{
    private $modelo;

    public function __construct($pdo){
        $this->pdo = $pdo;
        $this->modelo = new Reserva($pdo);
    }

    public function crear_reserva(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){

            $f_inicio = $_POST['fecha_inicio'];
            $f_fin = $_POST['fecha_fin'];

            $time_inicio = strtotime($f_inicio);
            $time_fin = strtotime($f_fin);

            if ($time_fin <= $time_inicio) {
                $id = $_POST['id_propiedad'];
                header("Location: ../views/detalle_propiedad.php?id=$id&error=rango_invalido");
                exit();
            }
            
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }

            $id_propiedad = intval($_POST['id_propiedad']);
            $id_huesped   = intval($_SESSION['user_id']);
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin    = $_POST['fecha_fin'];
            $cant_huespedes = intval($_POST['cant_huespedes']);

            $propModel = new Property($this->pdo); 
            $propiedad = $propModel->obtener_propiedad_por_id($id_propiedad);
            $precio_noche = $propiedad['precio'];

            $f1 = new DateTime($fecha_inicio);
            $f2 = new DateTime($fecha_fin);
            
            if ($f2 <= $f1) {
                header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=fechas_invalidas");
                exit();
            }

            $intervalo = $f1->diff($f2);
            $noches = $intervalo->days;

            $precio_total = ($noches * $precio_noche) * $cant_huespedes;

            if(empty($fecha_inicio) || empty($fecha_fin) || $cant_huespedes <= 0){
                header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=campos_vacios");
                exit();
            }
            
            $exito = $this->modelo->crear_reserva(
                $id_propiedad,
                $id_huesped,
                $fecha_inicio,
                $fecha_fin,
                $cant_huespedes,
                $precio_total
            );

            if($exito){
                header("Location: ../public/templates/reserva_exitosa.php");
                exit();
            } else {
                header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=reserva_fallida");
            }
        }
    }

    public function cancelar_reserva(){
        #Recibe id de reserva y llama al modelo para cancelarla
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }
            $id_reserva= intval($_POST['id_reserva']);
            $id_usuario= intval($_SESSION['user_id']);

            $exito= $this->modelo->cancelar_reserva($id_reserva, $id_usuario);
            if($exito){
                header("Location: ../public/templates/reserva_cancelada.php");
                exit();
            }else{
                header("Location: ../public/index.php?error=cancelacion_fallida");
            }
        }
    }

    public function confirmar_reserva(){
        #Recibe id de reserva y llama al modelo para confirmarla
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }
            $id_reserva= intval($_POST['id_reserva']);
            $id_usuario= intval($_SESSION['user_id']);

            $exito= $this->modelo->confirmar_reserva($id_reserva, $id_usuario);
            if($exito){
                header("Location: ../views/index.php?confirmacion=exito");
                exit();
            }else{
                header("Location: ../views/index.php?error=confirmacion_fallida");
            }
        }
    }

    public function obtener_reservas_propiedad($id_propiedad){
        $reservas = $this->modelo->obtener_reservas_propiedad($id_propiedad);
        return $reservas ? $reservas : [];
    }

    public function obtener_reservas_usuario($id_usuario){
        $reservas = $this->modelo->obtener_reservas_usuario($id_usuario);
        return $reservas ? $reservas : [];
    }

    public function obtener_fechas_ocupadas($id_propiedad) {
        return $this->modelo->obtener_fechas_ocupadas($id_propiedad);
    }

    public function obtener_reservas_anfitrion($id_anfitrion){
        $todasReservas = $this->modelo->obtener_reservas_anfitrion($id_anfitrion);

        return $todasReservas ? $todasReservas : [];
    }
    
}