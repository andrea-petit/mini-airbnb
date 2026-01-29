<?php

class Property{
    private $db;

    public function __construct($conexion){
        $this->db = $conexion;
    }

    public function obtener_propiedades(){
        #retorna todas las propiedades existentes
        $sql = "SELECT * FROM propiedades";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            die("Error al obtener propiedades: " . $e->getMessage());
        }

    }

    public function agregar_propiedad($titulo, $descripcion, $precio, $ubicacion, $id_usuario, $imagen_url, $capacidad){
        #CREATE propiedad. Recibe los datos e inserta en la bd
        $sql = "INSERT INTO propiedades (titulo, descripcion, precio_noche, ubicacion, id_anfitrion, imagen_url, capacidad) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute([$titulo, $descripcion, $precio, $ubicacion, $id_usuario, $imagen_url, $capacidad]);
            return true;
        }catch(PDOException $e){
            die("Error al agregar propiedad: " . $e->getMessage());
        }
    }

    public function eliminar_propiedad($id_propiedad, $id_usuario){
        #DELETE propiedad. Ubica propiedad por id y elimina de la bd
        $sql = "DELETE FROM propiedades WHERE id_propiedad = ? AND id_anfitrion = ?";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute([$id_propiedad, $id_usuario]);
            return $stmt->rowCount() > 0;
        }catch(PDOException $e){
            die("Error al eliminar propiedad: " . $e->getMessage());
        }
    }

    public function actualizar_propiedad($id_propiedad, $titulo, $descripcion, $precio, $ubicacion, $imagen_url, $id_usuario){
        #UPDATE propiedad, recibe datos nuevos y cambia en los campos relacionados con el id de la propiedad
        $sql = "UPDATE propiedades SET titulo = ?, descripcion = ?, precio_noche = ?, ubicacion = ?, imagen_url = ? WHERE id_propiedad = ? AND id_anfitrion = ?";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute([$titulo, $descripcion, $precio, $ubicacion, $imagen_url, $id_propiedad, $id_usuario]);
            return $stmt->rowCount() > 0;
        }catch(PDOException $e){
            die("Error al actualizar propiedad: " . $e->getMessage());
        }
    }

    public function obtener_propiedad_por_id($id_propiedad){
        #Buscar propiedad exacta con su id 
        $sql = "SELECT p.* , u.username as anfitrion_nombre, u.email as anfitrion_email FROM propiedades p
                JOIN usuarios u ON p.id_anfitrion = u.id_usuario
                WHERE p.id_propiedad = ?";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute([$id_propiedad]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            die("Error al obtener propiedad: " . $e->getMessage());
        }
    }

    public function buscar_propiedades($criterio){
        #Obtener propiedades con criterios de busqueda... (PARA EL BUSCADOR)
        $sql = "SELECT * FROM propiedades WHERE titulo LIKE ? OR descripcion LIKE ? OR ubicacion LIKE ?";
        $stmt = $this->db->prepare($sql);
        $like_criterio = '%' . $criterio . '%';
        try{
            $stmt->execute([$like_criterio, $like_criterio, $like_criterio]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            die("Error al buscar propiedades: " . $e->getMessage());
        }
    }

    public function obtener_propiedades_por_usuario($id_usuario){
        #Ubica las propiedades asociadas con el id del usuario
        $sql = "SELECT * FROM propiedades WHERE id_anfitrion = ?";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            die("Error al obtener propiedades de usuario: " . $e->getMessage());
        }

    }

    public function obtener_propiedades_disponibles($fecha_inicio, $fecha_fin){
        #Trae las propiedades que no estan reservadas en un rango de fecha
        $sql = "SELECT * FROM propiedades WHERE id_propiedad NOT IN (
                    SELECT id_propiedad FROM reservas 
                    WHERE (fecha_inicio <= ? AND fecha_fin >= ?)
                    OR (fecha_inicio <= ? AND fecha_fin >= ?)
                    OR (fecha_inicio >= ? AND fecha_fin <= ?)
                )";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute([$fecha_fin, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            die("Error al obtener propiedades disponibles: " . $e->getMessage());
        }
    }

    public function feed_propiedades_disponibles(){
        #Trae todas las propiedades (sin filtro de fechas)
        $sql = "SELECT p.*, u.username as anfitrion_nombre, u.email as anfitrion_email FROM propiedades p
                JOIN usuarios u ON p.id_anfitrion = u.id_usuario";
        $stmt = $this->db->prepare($sql);
        try{
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            die("Error al obtener propiedades disponibles: " . $e->getMessage());
        }
    }
}