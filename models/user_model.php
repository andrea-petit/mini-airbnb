<?php

class User {
    private $db;

    public function __construct($conexion){
        $this->db = $conexion;
    }

    public function ya_registrado($email) {
        $sql = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);

        return $stmt->fetch() ? true : false;
    }

    public function registrar($username, $email, $password, $rol){
        #Recibe datos de usuarios, hashea la contraseña y guarda en la bd,
        $hashed_password= password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (username, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt= $this->db->prepare($sql);

        try{
            $stmt->execute([$username, $email, $hashed_password, $rol]);
            return true;

        }catch(PDOexception $e){
            die("Error al registrar usuario: " . $e->getMessage());
        }
    }

    public function login($email, $password){
        #Login de usuario, verifica datos y devuelve el usuario si es correcto
        $sql= "SELECT * FROM usuarios WHERE email= ?";
        $stmt= $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user= $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password'])){
            return $user;
        }else{
            return false;
        }
    }
}