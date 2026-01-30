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

            if ($this->modelo->nro_registrado($nro_tlf)) {
                header("Location: ../views/registro.php?error=tlf_registrado");
                exit();
            }



            $exito= $this->modelo->registrar($username, $email, $password, $nro_tlf, $rol);
            if($exito){
                header("Location: ../views/login.php?registro=exito");
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
}

