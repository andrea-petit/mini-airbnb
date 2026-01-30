<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/index.php");
    exit();
}

function validate_int($v) {
    if (!isset($v) || $v === '') return false;
    return filter_var($v, FILTER_VALIDATE_INT) !== false;
}

function validate_float($v) {
    if (!isset($v) || $v === '') return false;
    $v = str_replace(',', '.', $v);
    return filter_var($v, FILTER_VALIDATE_FLOAT) !== false;
}

function validate_date($d) {
    if (!isset($d) || trim($d) === '') return false;
    try {
        $dt = new DateTime($d);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$errors = [];

$id_propiedad = $_POST['id_propiedad'] ?? '';
$f_inicio = $_POST['fecha_inicio'] ?? '';
$f_fin = $_POST['fecha_fin'] ?? '';
$cant_huespedes = $_POST['cant_huespedes'] ?? '';

if (!validate_int($id_propiedad)) {
    $errors[] = 'ID de propiedad inválido.';
}

if (!validate_date($f_inicio) || !validate_date($f_fin)) {
    $errors[] = 'Fechas inválidas.';
} else {
    $d1 = new DateTime($f_inicio);
    $d2 = new DateTime($f_fin);
    if ($d1 >= $d2) {
        $errors[] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
    }
}

if (!validate_int($cant_huespedes) || (int)$cant_huespedes <= 0) {
    $errors[] = 'Cantidad de huéspedes inválida (debe ser entero mayor que 0).';
}

if (!empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Errores en el formulario</title>
        <link rel="stylesheet" href="../public/css/style.css">
    </head>
    <body>
    <div class="container" style="max-width:600px;margin:40px auto;padding:20px;border:1px solid #ddd;border-radius:8px;">
        <h2>Errores en los datos enviados</h2>
        <ul style="color:#a33;">
            <?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?>
        </ul>
        <p><a href="../public/index.php">Volver al inicio</a></p>
    </div>
    </body>
    </html>
    <?php
    exit();
}

$noches = $d1->diff($d2)->days;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Confirmar y Pagar</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h1>Confirma y paga</h1>
    
    <div style="display: flex; gap: 30px;">
        <div style="flex: 1.5;">
            <h3>Método de pago</h3>
            <form action="../actions/reserva_actions.php?action=crear" method="POST">
                <input type="hidden" name="id_propiedad" value="<?php echo $id_propiedad; ?>">
                <input type="hidden" name="fecha_inicio" value="<?php echo $f_inicio; ?>">
                <input type="hidden" name="fecha_fin" value="<?php echo $f_fin; ?>">
                <input type="hidden" name="cant_huespedes" value="<?php echo $cant_huespedes; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="card-input" style="border: 1px solid #ccc; padding: 15px; border-radius: 8px;">
                    <label>Número de tarjeta</label>
                    <input type="text" placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16 dígitos" required style="width: 90%; margin-bottom: 10px;">
                    
                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Caducidad</label>
                            <input type="text" placeholder="MM/YY" required style="width: 100%;">
                        </div>
                        <div style="flex: 1;">
                            <label>CVV</label>
                            <input type="password" placeholder="123" pattern="\d{3}" required style="width: 100%;">
                        </div>
                    </div>
                </div>

                <p style="font-size: 0.8em; color: #666; margin-top: 15px;">
                    Tu reserva no se confirmará hasta que el anfitrión la acepte. No se te cobrará nada aún.
                </p>

                <button type="submit" class="btn-reservar" style="width: 100%; background: #ff385c; color: white; padding: 15px; border: none; border-radius: 8px; font-size: 1.1em; cursor: pointer;">
                    Confirmar y Pagar
                </button>
            </form>
        </div>

        <div style="flex: 1; border: 1px solid #ddd; padding: 20px; border-radius: 12px; background: #fafafa; position: sticky; top: 20px;">
            <?php
            // Obtenemos los datos de la propiedad para el resumen
            $stmt = $pdo->prepare("SELECT titulo, precio_noche, imagen_url FROM propiedades WHERE id_propiedad = ?");
            $stmt->execute([$id_propiedad]);
            $prop = $stmt->fetch();
            
            $total_estadia = ($noches * $prop['precio_noche']) * $cant_huespedes;
            $comision_servicio = $total_estadia * 0.03; // Simulación de comisión del 3%
            $total_final = $total_estadia + $comision_servicio;
            ?>

            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <img src="../public/uploads/<?php echo $prop['imagen_url']; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                <div>
                    <h4 style="margin: 0;"><?php echo htmlspecialchars($prop['titulo']); ?></h4>
                    <p style="font-size: 0.9em; color: #666;">Puntuación ★ 5.0</p>
                </div>
            </div>

            <hr>
            <h3>Detalles del precio</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>$<?php echo number_format($prop['precio_noche'], 2); ?> x <?php echo $noches; ?> noches</span>
                <span>$<?php echo number_format($noches * $prop['precio_noche'], 2); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Huéspedes (x<?php echo $cant_huespedes; ?>)</span>
                <span>Subtotal</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #666;">
                <span>Comisión por servicio</span>
                <span>$<?php echo number_format($comision_servicio, 2); ?></span>
            </div>
            <hr>
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2em;">
                <span>Total (USD)</span>
                <span>$<?php echo number_format($total_final, 2); ?></span>
            </div>
        </div>
    </div>
</div>
</body>
</html>