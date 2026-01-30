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

    private function validate_text($v) {
        if (!isset($v) || trim($v) === '') return false;
        $s = trim(strip_tags($v));
        return preg_match('/^[\p{L}\s\'\-]+$/u', $s);
    }

    private function validate_int($v) {
        if (!isset($v) || $v === '') return false;
        return filter_var($v, FILTER_VALIDATE_INT) !== false;
    }

    private function validate_float($v) {
        if (!isset($v) || $v === '') return false;
        $v = str_replace(',', '.', $v);
        return filter_var($v, FILTER_VALIDATE_FLOAT) !== false;
    }

    private function validate_date($d) {
        if (!isset($d) || trim($d) === '') return false;
        try {
            new DateTime($d);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function validate_card_number($n) {
        $n = preg_replace('/\s+/', '', $n);
        return preg_match('/^\d{16}$/', $n);
    }

    private function validate_cvv($c) {
        return preg_match('/^\d{3,4}$/', $c);
    }

    private function validate_expiry($e) {
        // Espera MM/YY o MM/YYYY
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2}|\d{4})$/', $e, $m)) return false;
        $parts = explode('/', $e);
        $month = intval($parts[0]);
        $year = intval($parts[1]);
        if ($year < 100) $year += 2000;
        $exp = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, 1));
        if (!$exp) return false;
        $lastDay = (clone $exp)->modify('last day of this month')->setTime(23,59,59);
        return $lastDay >= new DateTime();
    }

    public function crear_reserva(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $errors = [];

            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }

            $id_propiedad = $_POST['id_propiedad'] ?? '';
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin    = $_POST['fecha_fin'] ?? '';
            $cant_huespedes = $_POST['cant_huespedes'] ?? '';

            // Campos de pago (no se almacenan aquí, solo validación básica de formato)
            $card_number = $_POST['card_number'] ?? '';
            $card_expiry = $_POST['card_expiry'] ?? '';
            $card_cvv = $_POST['card_cvv'] ?? '';

            // Validaciones obligatorias y formatos
            if (!$this->validate_int($id_propiedad)) $errors[] = 'ID de propiedad inválido.';
            if (!$this->validate_date($fecha_inicio) || !$this->validate_date($fecha_fin)) $errors[] = 'Fechas inválidas.';
            if (!$this->validate_int($cant_huespedes) || intval($cant_huespedes) <= 0) $errors[] = 'Cantidad de huéspedes inválida.';

            // Validar tarjeta (si existe el campo en el formulario)
            // if ($card_number === '' || $card_expiry === '' || $card_cvv === '') {
            //     $errors[] = 'Datos de pago incompletos.';
            // } else {
            //     if (!$this->validate_card_number($card_number)) $errors[] = 'Número de tarjeta inválido.';
            //     if (!$this->validate_expiry($card_expiry)) $errors[] = 'Fecha de caducidad inválida.';
            //     if (!$this->validate_cvv($card_cvv)) $errors[] = 'CVV inválido.';
            // }

            if (!empty($errors)){
                $id = intval($id_propiedad) ?: 0;
                $msg = urlencode(implode('; ', $errors));
                header("Location: ../views/detalle_propiedad.php?id={$id}&error={$msg}");
                exit();
            }

            $id_propiedad = intval($id_propiedad);
            $id_huesped   = intval($_SESSION['user_id']);
            $cant_huespedes = intval($cant_huespedes);

            $propModel = new Property($this->pdo); 
            $propiedad = $propModel->obtener_propiedad_por_id($id_propiedad);
            $precio_noche = $propiedad['precio_noche'];

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

            if($cant_huespedes > $propiedad['capacidad']){
                header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=excede_capacidad");
                exit();
            }

            if($noches > 30 or $noches < 1){
                header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=noches_invalidas");
                exit();
            }

            if($fecha_inicio < date('Y-m-d')){
                header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=fecha_inicio_pasada");
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

            $exito= $this->modelo->cancelar_reserva($id_reserva);
            if($exito){
                header("Location: ../public/index.php?cancelacion=exito");
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

            $exito= $this->modelo->confirmar_reserva($id_reserva);
            if($exito){
                header("Location: ../public/index.php?confirmacion=exito");
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