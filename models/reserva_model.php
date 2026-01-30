<?php

class Reserva{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo; 
    }

    public function crear_reserva($id_propiedad, $id_huesped, $fecha_inicio, $fecha_fin, $cant_huespedes, $precio_total){
        $query_verificacion = "SELECT COUNT(*) FROM reservas 
            WHERE id_propiedad = :id 
            AND (estado = 'confirmada' OR estado = 'pendiente')
            AND (:inicio < fecha_fin AND :fin > fecha_inicio)";

        $stmt_verificacion = $this->pdo->prepare($query_verificacion);
        $stmt_verificacion->execute([
            'id' => $id_propiedad,
            'inicio' => $fecha_inicio,
            'fin' => $fecha_fin
        ]);

        if ($stmt_verificacion->fetchColumn() > 0) {
            header("Location: ../views/detalle_propiedad.php?id=$id_propiedad&error=dias_ocupados");
            exit();
        }
        
        try {
            $sql = "INSERT INTO reservas (id_propiedad, id_huesped, fecha_inicio, fecha_fin, cant_huespedes, precio_total) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id_propiedad, $id_huesped, $fecha_inicio, $fecha_fin, $cant_huespedes, $precio_total]);
        } catch (PDOException $e) {
            error_log("Error al crear reserva: " . $e->getMessage());
            return false;
        }
    }

    public function cancelar_reserva($id_reserva){
        try {
            $sql = "UPDATE reservas SET estado = 'cancelada' WHERE id_reserva = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id_reserva]);
        } catch (PDOException $e) {
            error_log("Error al cancelar reserva: " . $e->getMessage());
            return false;
        }
    }

    public function confirmar_reserva($id_reserva){
        try {
            $sql = "UPDATE reservas SET estado = 'confirmada' WHERE id_reserva = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id_reserva]);
        } catch (PDOException $e) {
            error_log("Error al confirmar reserva: " . $e->getMessage());
            return false;
        }
    }

    public function obtener_reservas_propiedad($id_propiedad){
        try {
            $sql = "SELECT * FROM reservas WHERE id_propiedad = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_propiedad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reservas de la propiedad: " . $e->getMessage());
            return [];
        }
    }

    public function obtener_reservas_usuario($id_usuario){
        try {
            $sql = "SELECT r.*, p.titulo as titulo_propiedad, p.ubicacion as ubicacion_propiedad
            FROM reservas r 
            JOIN propiedades p ON r.id_propiedad = p.id_propiedad 
            WHERE r.id_huesped = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reservas: " . $e->getMessage());
            return [];
        }
    }

    public function obtener_fechas_ocupadas($id_propiedad) {
        $sql = "SELECT fecha_inicio, fecha_fin FROM reservas 
            WHERE id_propiedad = :id AND (estado IS NULL OR estado <> 'cancelada')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id_propiedad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_reservas_anfitrion($id_anfitrion) {
        $sql = "SELECT r.*, p.titulo AS titulo_propiedad, u.username AS nombre_huesped, u.nro_tlf AS telefono_huesped
                FROM reservas r
                JOIN propiedades p ON r.id_propiedad = p.id_propiedad
                JOIN usuarios u ON r.id_huesped = u.id_usuario
                WHERE p.id_anfitrion = :id_anfitrion
                ORDER BY r.creado_en DESC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_anfitrion' => $id_anfitrion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}