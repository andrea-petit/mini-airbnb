<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/property_model.php';

class PropertyController{
    private $modelo;

    public function __construct($pdo){
        $this->modelo= new Property($pdo);
    }

    public function obtener_propiedades(){
        return $this->modelo->get_all_properties();
    }

    public function chequear_id(){
        $id_usuario = $_SESSION['user_id'] ?? null;
        if(!$id_usuario){
            header("Location: ../login.php?error=no_autenticado");
            exit();
        }else{
            return $id_usuario;
        }   
    }

    public function agregar_propiedad($titulo, $descripcion, $precio, $ubicacion, $archivo_foto, $capacidad){
        $id_usuario = $this->chequear_id();
        
        $nombre_foto = "default.jpg"; 
        
        if (isset($archivo_foto) && $archivo_foto['error'] === 0) {
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = time() . "_" . uniqid() . "." . $extension;
            $ruta_destino = __DIR__ . "/../public/uploads/" . $nombre_foto;
            
            move_uploaded_file($archivo_foto['tmp_name'], $ruta_destino);
        }

        $precio = floatval($precio); 

        $nueva_propiedad= $this->modelo->agregar_propiedad($titulo, $descripcion, $precio, $ubicacion, $id_usuario, $nombre_foto, $capacidad);
    
        if($nueva_propiedad){
            header("Location: ../public/index.php?success=propiedad_agregada");
            exit();
        }else{
            header("Location: ../public/index.php?error=error_agregando");
            exit();
        }
    }

    public function eliminar_propiedad($id_propiedad){
        $id_usuario = $this->chequear_id();
        $eliminado= $this->modelo->eliminar_propiedad($id_propiedad, $id_usuario);

        if($eliminado){
            header("Location: ../public/index.php?success=propiedad_eliminada");
            exit();
        }else{
            header("Location: ../public/index.php?error=error_eliminando");
            exit();
        }
    }

    public function actualizar_propiedad($id_propiedad, $titulo, $descripcion, $precio, $ubicacion, $archivo_foto = null){
        $id_usuario = $this->chequear_id();
        $precio = floatval($precio);

        $imagen_url = null;
        if ($archivo_foto && isset($archivo_foto['error']) && $archivo_foto['error'] === 0) {
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = time() . "_" . uniqid() . "." . $extension;
            $ruta_destino = __DIR__ . "/../public/uploads/" . $nombre_foto;
            if (move_uploaded_file($archivo_foto['tmp_name'], $ruta_destino)) {
                $imagen_url = $nombre_foto;
            }
        }

        $actualizado= $this->modelo->actualizar_propiedad($id_propiedad, $titulo, $descripcion, $precio, $ubicacion, $imagen_url, $id_usuario);

        if($actualizado){
            header("Location: ../public/index.php?success=propiedad_actualizada");
            exit();
        }else{
            header("Location: ../public/index.php?error=error_actualizando");
            exit();
        }
    }

    public function obtener_propiedad_por_id($id_propiedad){
        try{
            return $this->modelo->obtener_propiedad_por_id($id_propiedad);
        }catch(Exception $e){
            die("Error al obtener propiedad: " . $e->getMessage());
        }
 
    }

    public function buscar_propiedades($criterio){
        try{
            return $this->modelo->buscar_propiedades($criterio);
        }catch(Exception $e){
            die("Error al buscar propiedades: " . $e->getMessage());
        }
        
    }

    public function obtener_propiedades_por_usuario($id_usuario){
        try{
            return $this->modelo->obtener_propiedades_por_usuario($id_usuario);
        }catch(Exception $e){
            die("Error al obtener propiedades: " . $e->getMessage());
        }

    }

    public function obtener_propiedades_disponibles(){
        try{
            return $this->modelo->obtener_propiedades_disponibles();
        }catch(Exception $e){
            die("Error al obtener propiedades: " . $e->getMessage());
        }
    }

    public function feed_propiedades_disponibles(){
        try{
            return $this->modelo->feed_propiedades_disponibles();
        }catch(Exception $e){
            die("Error al obtener propiedades: " . $e->getMessage());
        }
    }
}
