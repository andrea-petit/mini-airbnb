<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/user_model.php';

class UserController{
    private $modelo;

    public function __construct($pdo){
        $this->modelo = new User($pdo);
    }

    public function registrar(){
        #Recibe datos de usuario, purifica, valida  y llama al modelo para registrar
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }
            $username= htmlspecialchars(trim($_POST['username']));
            $email= filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password= $_POST['password'];
            $nro_tlf= $_POST['nro_tlf'];
            $rol= $_POST['rol'];


            $nro_format = substr($nro_tlf, 1);

            if(empty($username) || empty($email) || empty ($password)){
                header("Location: ../views/registro.php?error=campos_vacios");
                exit();
            }

            if(strlen($password) < 6){
                header("Location: ../views/registro.php?error=contrasena_corta");
                exit();
            }

            if(strlen($nro_tlf) != 11 ){
                header("Location: ../views/registro.php?error=tlf_invalido");
                exit();
            }

            if ($this->modelo->ya_registrado($email)) {
                header("Location: ../views/registro.php?error=email_registrado");
                exit();
            }

            if ($this->modelo->nro_registrado($nro_format)) {
                header("Location: ../views/registro.php?error=tlf_registrado");
                exit();
            }



            $exito= $this->modelo->registrar($username, $email, $password, $nro_format, $rol);
            if($exito){
                session_start();
                $_SESSION['id_usuario']= $exito;
                header("Location: ../views/configurar_seguridad.php");
                exit();
            }else{
                header("Location: ../views/registro.php?error=registro_fallido");
            }
        }
    }

    public function login(){
        #Recibe datos de usuario, purifica y envia al modelo. Si devuelve exito, guarda datos en la sesion
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }

            $email= filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password= $_POST['password'];

            if(empty($email) || empty ($password)){
                header("Location: ../views/login.php?error=campos_vacios");
                exit();
            }

            $usuario= $this->modelo->login($email, $password);
            if($usuario){
                $_SESSION['user_id']= $usuario['id_usuario'];
                $_SESSION['user_name']= $usuario['username'];
                $_SESSION['user_email']= $usuario['email'];
                $_SESSION['user_rol']= $usuario['rol'];
                header("Location: ../public/index.php");
                exit();
            }else{
                header("Location: ../views/login.php?error=datos_invalidos");
            }
        }
    }

    public function logout(){
        #Destruye la sesion y redirige al login
        session_start();
        session_unset();
        session_destroy();
        header("Location: ../views/login.php?msg=logout_exito");
        exit();
    }

    public function solicitar_recuperacion(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            if(empty($email)){
                header("Location: ../views/olvido_password.php?error=campo_vacio");
                exit();
            }

            if ($this->modelo->ya_registrado($email)) {
                header("Location: ../views/reset_password.php?email=" . urlencode($email));
                exit();
            } else {
                header("Location: ../views/olvido_password.php?error=email_no_encontrado");
                exit();
            }
        }
    }

    public function reset_password(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                die("Error: token CSRF inválido");
            }
            $email = $_POST['email'];
            $password = $_POST['password'];

            if(empty($password)){
                header("Location: ../views/reset_password.php?email=".urlencode($email)."&error=campo_vacio");
                exit();
            }

            $exito = $this->modelo->actualizar_password($email, $password);
            if($exito){
                header("Location: ../views/login.php?msg=password_actualizada");
                exit();
            } else {
                header("Location: ../views/reset_password.php?email=".urlencode($email)."&error=error_servidor");
            }
        }
    }

    public function cargar_formulario_seguridad() {
        return $this->modelo->obtener_opciones_preguntas();
    }

    public function procesar_seguridad($id_usuario, $id_pregunta, $respuesta) {
        if (empty($id_usuario) || empty($id_pregunta) || empty($respuesta)) {
            header("Location: ../views/configurar_seguridad.php?id_usuario=$id_usuario&error=campos_vacios");
            exit();
        }

        $exito = $this->modelo->registrar_preguntas_seguridad($id_usuario, $id_pregunta, $respuesta);

        if ($exito) {
            header("Location: ../views/login.php?success=registro_completo");
            exit();
        } else {
            header("Location: ../views/configurar_seguridad.php?id_usuario=$id_usuario&error=error_db");
            exit();
        }
    }

    public function validar_identidad_recuperacion($email, $id_pregunta, $respuesta) {
        $usuario = $this->modelo->obtener_usuario_por_email($email);
        
        if (!$usuario) {
            return false;
        }

        return $this->modelo->verificar_respuesta_seguridad($usuario['id_usuario'], $id_pregunta, $respuesta);
    }

    // Indica si el usuario ya tiene una pregunta de seguridad registrada
    public function tiene_pregunta_configurada($id_usuario) {
        $preguntas = $this->modelo->obtener_preguntas_seguridad($id_usuario);
        return !empty($preguntas) && count($preguntas) > 0;
    }

    public function obtener_pregunta_por_email($email) {
        // Usar el método del modelo que devuelve id_pregunta y pregunta asociados al email, o null si no existe
        return $this->modelo->obtener_pregunta_por_email($email);
    }

    public function obtener_usuario_por_email($email) {
        return $this->modelo->obtener_usuario_por_email($email);
    }


}

