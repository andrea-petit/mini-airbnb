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

    public function verificar_propiedad_por_uuid($uuid) {
        $id_usuario = $this->chequear_id();
        return $this->modelo->verificar_propiedad_por_uuid($uuid, $id_usuario);
    }

    public function verificar_propiedad($id_propiedad) {
        $id_usuario = $this->chequear_id();
        return $this->modelo->verificar_propiedad($id_propiedad, $id_usuario);
    }

    public function agregar_propiedad($titulo, $descripcion, $precio, $ubicacion, $archivo_foto = null, $capacidad) {
        $id_usuario = $this->chequear_id();
            
        $imagen_url = null;
        if ($archivo_foto && isset($archivo_foto['error']) && $archivo_foto['error'] === 0) {
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = time() . "_" . uniqid() . "." . $extension;
            $ruta_destino = __DIR__ . "/../public/uploads/" . $nombre_foto;
            if (move_uploaded_file($archivo_foto['tmp_name'], $ruta_destino)) {
                $imagen_url = $nombre_foto;
            }
        }

        $uuid_nueva_propiedad = $this->modelo->agregar_propiedad($titulo, $descripcion, $precio, $ubicacion, $id_usuario, $imagen_url, $capacidad);

        if ($uuid_nueva_propiedad) {
            header("Location: ../views/formulario_propiedad.php?id=" . $uuid_nueva_propiedad . "&step=2&success=datos_guardados");
            exit();
        } else {
            header("Location: ../views/formulario_propiedad.php?error=error_agregando");
            exit();
        }
    }

    public function agregar_comodidades($uuid, $comodidades) {
        $prop = $this->modelo->obtener_propiedad_por_uuid($uuid);
        if (!$prop) {
            header("Location: ../public/index.php?error=propiedad_no_encontrada");
            exit();
        }
        $id_propiedad = $prop['id_propiedad'];

        $comodidades = $comodidades ?? [];
        $agregado = $this->modelo->agregar_comodidades($id_propiedad, $comodidades);

        if ($agregado) {
            header("Location: ../public/index.php?success=propiedad_publicada_completa");
            exit();
        } else {
            header("Location: ../views/formulario_propiedad.php?id=$uuid&error=error_comodidades");
            exit();
        }
    }

    public function eliminar_propiedad($uuid){
        $id_usuario = $this->chequear_id();
        $eliminado = $this->modelo->eliminar_propiedad_por_uuid($uuid, $id_usuario);

        if($eliminado){
            header("Location: ../public/index.php?success=propiedad_eliminada");
            exit();
        }else{
            header("Location: ../public/index.php?error=error_eliminando");
            exit();
        }
    }

    public function actualizar_propiedad($uuid, $titulo, $descripcion, $precio, $ubicacion, $archivo_foto = null, $id_usuario) {
        
        $imagen_url = null; 
        
        if ($archivo_foto && isset($archivo_foto['error']) && $archivo_foto['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = time() . "_" . uniqid() . "." . $extension;
            $ruta_destino = __DIR__ . "/../public/uploads/" . $nombre_foto;
            
            if (move_uploaded_file($archivo_foto['tmp_name'], $ruta_destino)) {
                $imagen_url = $nombre_foto;
            }
        }

        $actualizado = $this->modelo->actualizar_propiedad_por_uuid($uuid, $titulo, $descripcion, $precio, $ubicacion, $imagen_url, $id_usuario);

        if ($actualizado) {
            header("Location: ../public/index.php?success=datos_actualizados");
            exit();
        } else {
            header("Location: ../views/formulario_propiedad.php?id=$uuid&error=error_actualizando");
            exit();
        }
    }

    public function actualizar_comodidades($uuid, $comodidades) {
        if (!$this->verificar_propiedad_por_uuid($uuid)) {
            header("Location: ../public/index.php?error=no_autorizado");
            exit();
        }
        
        $prop = $this->modelo->obtener_propiedad_por_uuid($uuid);
        $id_propiedad = $prop['id_propiedad'];

        $comodidades = $comodidades ?? [];
        $actualizado = $this->modelo->actualizar_comodidades($id_propiedad, $comodidades);

        if ($actualizado) {
            header("Location: ../public/index.php?success=comodidades_actualizadas");
            exit();
        } else {
            header("Location: ../views/formulario_propiedad.php?id=$uuid&error=error_actualizando_comodidades");
            exit();
        }
    }

    public function obtener_propiedad_por_uuid($uuid){
        try{
            return $this->modelo->obtener_propiedad_por_uuid($uuid);
        }catch(Exception $e){
            die("Error al obtener propiedad: " . $e->getMessage());
        }
    }

    public function obtener_propiedad_por_id($id_propiedad){
        try{
            return $this->modelo->obtener_propiedad_por_id($id_propiedad);
        }catch(Exception $e){
            die("Error al obtener propiedad: " . $e->getMessage());
        }
 
    }

    public function obtener_comodidades_por_propiedad($id_propiedad){
        try{
            return $this->modelo->obtener_comodidades_por_propiedad($id_propiedad);
        }catch(Exception $e){
            die("Error al obtener comodidades: " . $e->getMessage());
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

    public function obtener_comodidades_seleccionadas($id_propiedad){
        try{
            return $this->modelo->obtener_comodidades_seleccionadas($id_propiedad);
        }catch(Exception $e){
            die("Error al obtener comodidades: " . $e->getMessage());
        }
        
    }

    public function obtener_todas_las_comodidades(){
        try{
            return $this->modelo->obtener_todas_las_comodidades();
        }catch(Exception $e){
            die("Error al obtener comodidades: " . $e->getMessage());
        }
        
    }
}
