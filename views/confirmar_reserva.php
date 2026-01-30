<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/index.php");
    exit();
}

$id_propiedad = $_POST['id_propiedad'];
$f_inicio = $_POST['fecha_inicio'];
$f_fin = $_POST['fecha_fin'];
$cant_huespedes = $_POST['cant_huespedes'];

$d1 = new DateTime($f_inicio);
$d2 = new DateTime($f_fin);
$noches = $d1->diff($d2)->days;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Confirmar y Pagar</title>
    <link rel="stylesheet" href="../public/css/confirmar_reserva.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="confirmation-container">
    <h1>Confirma y paga</h1>
    
    <div class="confirmation-layout">
        <div class="payment-section">
            <h3>Método de pago</h3>
            <form action="../actions/reserva_actions.php?action=crear" method="POST">
                <input type="hidden" name="id_propiedad" value="<?php echo $id_propiedad; ?>">
                <input type="hidden" name="fecha_inicio" value="<?php echo $f_inicio; ?>">
                <input type="hidden" name="fecha_fin" value="<?php echo $f_fin; ?>">
                <input type="hidden" name="cant_huespedes" value="<?php echo $cant_huespedes; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="card-input-box">
                    <label>Número de tarjeta</label>
                    <input type="text" placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16 dígitos" required>
                    
                    <div class="card-fields-row">
                        <div class="card-field">
                            <label>Caducidad</label>
                            <input type="text" placeholder="MM/YY" required>
                        </div>
                        <div class="card-field">
                            <label>CVV</label>
                            <input type="password" placeholder="123" pattern="\d{3}" required>
                        </div>
                    </div>
                </div>

                <p class="disclaimer-text">
                    Tu reserva no se confirmará hasta que el anfitrión la acepte. No se te cobrará nada aún.
                </p>

                <button type="submit" class="btn-confirm">
                    Confirmar y Pagar
                </button>
            </form>
        </div>

        <div class="summary-sidebar">
            <?php
            // Obtenemos los datos de la propiedad para el resumen
            $stmt = $pdo->prepare("SELECT titulo, precio_noche, imagen_url FROM propiedades WHERE id_propiedad = ?");
            $stmt->execute([$id_propiedad]);
            $prop = $stmt->fetch();
            
            $total_estadia = ($noches * $prop['precio_noche']) * $cant_huespedes;
            $comision_servicio = $total_estadia * 0.03; // Simulación de comisión del 3%
            $total_final = $total_estadia + $comision_servicio;
            ?>

            <div class="property-preview">
                <img src="../public/uploads/<?php echo $prop['imagen_url']; ?>" class="property-image">
                <div>
                    <h4 class="property-title"><?php echo htmlspecialchars($prop['titulo']); ?></h4>
                    <p class="property-rating">Puntuación ★ 5.0</p>
                </div>
            </div>

            <hr>
            <h3>Detalles del precio</h3>
            <div class="price-row">
                <span>$<?php echo number_format($prop['precio_noche'], 2); ?> x <?php echo $noches; ?> noches</span>
                <span>$<?php echo number_format($noches * $prop['precio_noche'], 2); ?></span>
            </div>
            <div class="price-row">
                <span>Huéspedes (x<?php echo $cant_huespedes; ?>)</span>
                <span>Subtotal</span>
            </div>
            <div class="price-row subtle">
                <span>Comisión por servicio</span>
                <span>$<?php echo number_format($comision_servicio, 2); ?></span>
            </div>
            <hr>
            <div class="price-row-total">
                <span>Total (USD)</span>
                <span>$<?php echo number_format($total_final, 2); ?></span>
            </div>
        </div>
    </div>
</div>
</body>
</html>