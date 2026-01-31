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

    public function nro_registrado($nro_tlf) {
        $sql = "SELECT id_usuario FROM usuarios WHERE nro_tlf = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nro_tlf]);

        return $stmt->fetch() ? true : false;
    }

    public function registrar($username, $email, $password, $nro_tlf, $rol){
        $hashed_password= password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (username, email, password, nro_tlf, rol) VALUES (?, ?, ?, ?, ?)";
        $stmt= $this->db->prepare($sql);
        try{
            $stmt->execute([$username, $email, $hashed_password, $nro_tlf, $rol]);
            return $this->db->lastInsertId();
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

    public function actualizar_password($email, $nueva_password) {
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET password = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$hashed_password, $email]);
    }


    public function obtener_preguntas_seguridad($id_usuario) {
        $sql = "SELECT p.pregunta FROM usuarios_preguntas up JOIN preguntas_seguridad p ON up.id_pregunta = p.id_pregunta WHERE up.id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            die("Error al obtener preguntas de seguridad: " . $e->getMessage());
        }
    }

    public function obtener_opciones_preguntas() {
        $sql = "SELECT id_pregunta, pregunta FROM preguntas_seguridad";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener catálogo de preguntas: " . $e->getMessage());
        }
    }

    public function registrar_preguntas_seguridad($id_usuario, $id_pregunta, $respuesta) {
        $sql = "INSERT INTO usuarios_preguntas (id_usuario, id_pregunta, respuesta_hash) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            $hash = password_hash(strtolower(trim($respuesta)), PASSWORD_BCRYPT);
            return $stmt->execute([$id_usuario, $id_pregunta, $hash]);
        } catch (PDOException $e) {
            die("Error al registrar pregunta de seguridad: " . $e->getMessage());
        }
    }

    public function verificar_respuesta_seguridad($id_usuario, $id_pregunta, $respuesta) {
        $sql = "SELECT respuesta_hash FROM usuarios_preguntas WHERE id_usuario = ? AND id_pregunta = ?";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([$id_usuario, $id_pregunta]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($registro) {
                $respuesta_limpia = strtolower(trim($respuesta));
                return password_verify($respuesta_limpia, $registro['respuesta_hash']);
            }
            return false;
        } catch (PDOException $e) {
            die("Error al verificar respuesta: " . $e->getMessage());
        }
    }

    public function obtener_pregunta_por_email($email) {
        $sql = "SELECT p.id_pregunta, p.pregunta 
                FROM usuarios_preguntas up 
                JOIN preguntas_seguridad p ON up.id_pregunta = p.id_pregunta 
                JOIN usuarios u ON up.id_usuario = u.id_usuario 
                WHERE u.email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtener_usuario_por_email($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}