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
        <link rel="stylesheet" href="../public/css/styles.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="../public/css/confirmar_reserva.css?v=<?php echo time(); ?>">
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
    <link rel="stylesheet" href="../public/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../public/css/confirmar_reserva.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="confirmation-container">
    <div class="header-actions">
        <a href="detalle_propiedad.php?id=<?php echo $id_propiedad; ?>" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Volver
        </a>
    </div>

    <h1>Confirma y paga</h1>
    
    <div class="confirmation-layout">
        <div class="payment-section">
            <div class="payment-method-title">
                <h3>Pagar con</h3>
                <div class="secure-icons">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Pago seguro</span>
                </div>
            </div>

            <form action="../actions/reserva_actions.php?action=crear" method="POST">
                <input type="hidden" name="id_propiedad" value="<?php echo $id_propiedad; ?>">
                <input type="hidden" name="fecha_inicio" value="<?php echo $f_inicio; ?>">
                <input type="hidden" name="fecha_fin" value="<?php echo $f_fin; ?>">
                <input type="hidden" name="cant_huespedes" value="<?php echo $cant_huespedes; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="card-input-box">
                    <div class="card-field-full">
                        <label>Número de tarjeta</label>
                        <input name="numero_tarjeta" type="text" placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16 dígitos" required maxlength="16" >
                    </div>
                    
                    <div class="card-fields-row">
                        <div class="card-field">
                            <label>Caducidad</label>
                            <input type="text" name="caducidad" placeholder="MM/YY" required maxlength="5">
                        </div>
                        <div class="card-field">
                            <label>CVV</label>
                            <input type="password" name="cvv" placeholder="123" pattern="\d{3}" required maxlength="3">
                        </div>
                    </div>
                </div>

                <hr class="section-divider">

                <div class="cancellation-policy">
                    <h3>Política de cancelación</h3>
                    <p>Esta reserva no es reembolsable. Asegúrate de que tus planes son definitivos antes de reservar.</p>
                </div>

                <hr class="section-divider">

                <p class="disclaimer-text">
                    Al seleccionar el botón que aparece a continuación, acepto las Reglas de la casa del anfitrión, los Términos de servicio de WindBnB y reconozco la Política de privacidad.
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
            
            $total_estadia = ($noches * $prop['precio_noche']);
            $comision_servicio = $total_estadia * 0.03; // Simulación de comisión del 3%
            $total_final = $total_estadia + $comision_servicio;
            ?>

            <div class="property-preview">
                <img src="../public/uploads/<?php echo $prop['imagen_url']; ?>" class="property-image">
                <div class="preview-info">
                    <h4 class="property-title"><?php echo htmlspecialchars($prop['titulo']); ?></h4>
                    <p class="property-rating">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                        5.0
                    </p>
                </div>
            </div>

            <hr class="section-divider">
            
            <h3>Detalles del precio</h3>
            <div class="price-details">
                <div class="price-row">
                    <span>$<?php echo number_format($prop['precio_noche'], 2); ?> x <?php echo $noches; ?> noches</span>
                    <span>$<?php echo number_format($noches * $prop['precio_noche'], 2); ?></span>
                </div>
                <div class="price-row">
                    <span>Huéspedes (x<?php echo $cant_huespedes; ?>)</span>
                </div>
                <div class="price-row">
                    <span>Tarifa por servicio de WindBnB</span>
                    <span>$<?php echo number_format($comision_servicio, 2); ?></span>
                </div>
            </div>

            <hr class="section-divider">
            <div class="price-row-total">
                <span>Total (USD)</span>
                <span>$<?php echo number_format($total_final, 2); ?></span>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    (function(){
    const el = document.querySelector('input[name="numero_tarjeta"]');
    const ell = document.querySelector('input[name="cvv"]');
    const elll = document.querySelector('input[placeholder="MM/YY"]');
    if (!el) return;
    el.addEventListener('keydown', function(e){
        const allowed = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab','Home','End'];
        if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) return;
        if (/^\d$/.test(e.key)) return;
        e.preventDefault();
    });
    el.addEventListener('input', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    el.addEventListener('paste', function(e){
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = text.replace(/[^0-9]/g, '');
        if (cleaned !== text) {
            e.preventDefault();
            document.execCommand('insertText', false, cleaned);
        }
    });
    if (!ell) return;
    ell.addEventListener('keydown', function(e){
        const allowed = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab','Home','End'];
        if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) return;
        if (/^\d$/.test(e.key)) return;
        e.preventDefault();
    });
    ell.addEventListener('input', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    ell.addEventListener('paste', function(e){
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = text.replace(/[^0-9]/g, '');
        if (cleaned !== text) {
            e.preventDefault();
            document.execCommand('insertText', false, cleaned);
        }
    });
    if (!elll) return;
    elll.addEventListener('keydown', function(e){
        const allowed = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab','Home','End'];
        if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) return;
        if (/^\d$/.test(e.key)) return;
        e.preventDefault();
    });
    elll.addEventListener('input', function(){
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    elll.addEventListener('paste', function(e){
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = text.replace(/[^0-9]/g, '');
        if (cleaned !== text) {
            e.preventDefault();
            document.execCommand('insertText', false, cleaned);
        }
    });
})();
</script>
</html>