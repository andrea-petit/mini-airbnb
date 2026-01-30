<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/property_controller.php';

$id_propiedad = $_GET['id'] ?? null;
if (!$id_propiedad) {
    header("Location: ../public/index.php");
    exit();
}

$propCtrl = new PropertyController($pdo);
$p = $propCtrl->obtener_propiedad_por_id($id_propiedad);

if (!$p) {
    die("La propiedad no existe.");
}

$comodidades = $propCtrl->obtener_comodidades_por_propiedad($id_propiedad) ?: [];

$capacidadMaxima = $p['capacidad'] ?? 5; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($p['titulo']); ?> - Detalles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #ff385c;
            --text-main: #222222;
            --text-light: #717171;
            --border-color: #dddddd;
            --bg-light: #f7f7f7;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Cabecera y Navegación */
        .back-link {
            display: inline-block;
            margin: 20px 0;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
            transition: transform 0.2s;
        }
        .back-link:hover { transform: translateX(-5px); }

        /* Galería de Imagen */
        .main-image-container {
            width: 100%;
            height: 480px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .main-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Layout Principal */
        .detalle-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 80px;
            margin-top: 32px;
        }

        h1 { font-size: 26px; margin-bottom: 8px; }
        .meta-info { color: var(--text-light); margin-bottom: 32px; font-size: 16px; }

        hr { border: 0; border-top: 1px solid var(--border-color); margin: 32px 0; }

        /* Comodidades */
        .amenities-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .amenity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            color: var(--text-main);
        }
        .amenity-icon { color: var(--primary-color); font-size: 18px; }

        /* Tarjeta de Reserva */
        .booking-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            box-shadow: rgba(0, 0, 0, 0.12) 0px 6px 16px;
            position: sticky;
            top: 32px;
            background: white;
        }

        .price-header { font-size: 22px; font-weight: 700; margin-bottom: 24px; }
        .price-header small { font-weight: 400; color: var(--text-light); font-size: 16px; }

        .input-group {
            border: 1px solid #b0b0b0;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .input-box {
            padding: 10px 12px;
            border-bottom: 1px solid #b0b0b0;
        }
        .input-box:last-child { border-bottom: none; }
        .input-box label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .input-box input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 14px;
            padding: 4px 0;
            background: transparent;
        }

        /* Resumen de Pago */
        .resumen-pago {
            margin-top: 24px;
            font-size: 16px;
        }
        .resumen-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .total-row {
            border-top: 1px solid var(--border-color);
            padding-top: 16px;
            font-weight: 700;
            font-size: 18px;
        }

        .btn-reservar {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-reservar:hover:not(:disabled) { opacity: 0.9; }
        .btn-reservar:disabled { background: #dddddd; cursor: not-allowed; }

        /* Flatpickr Customization */
        .flatpickr-day.flatpickr-disabled { color: #ccc !important; text-decoration: line-through; }

        @media (max-width: 950px) {
            .detalle-layout { grid-template-columns: 1fr; gap: 40px; }
            .booking-card { position: static; }
        }
    </style>
</head>
<body>

<main class="container">
    <a href="../public/index.php" class="back-link">← Volver al buscador</a>

    <section class="main-image-container">
        <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" alt="Imagen de la propiedad">
    </section>

    <div class="detalle-layout">
        <section>
            <h1><?php echo htmlspecialchars($p['titulo']); ?></h1>
            <div class="meta-info">
                <strong><?php echo htmlspecialchars($p['ubicacion']); ?></strong> · 
                Anfitrión: <?php echo htmlspecialchars($p['anfitrion_nombre']); ?> · 
                <?php echo $capacidadMaxima; ?> huéspedes máximo
            </div>

            <hr>

            <h2 style="font-size: 22px;">Sobre este espacio</h2>
            <p style="white-space: pre-line; color: #484848;"><?php echo htmlspecialchars($p['descripcion']); ?></p>

            <?php if (!empty($comodidades)): ?>
                <hr>
                <h2 style="font-size: 22px;">Lo que este lugar ofrece</h2>
                <div class="amenities-container">
                    <?php foreach ($comodidades as $com): ?>
                        <div class="amenity-item">
                            <span class="amenity-icon">✓</span>
                            <span><?php echo htmlspecialchars($com['nombre']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <aside>
            <div class="booking-card">
                <div class="price-header">
                    $<?php echo number_format($p['precio_noche'], 2); ?> <small>/ noche</small>
                </div>

                <form action="confirmar_reserva.php" method="POST" id="reservaForm">
                    <input type="hidden" name="id_propiedad" value="<?php echo $p['id_propiedad']; ?>">
                    
                    <div class="input-group">
                        <div class="input-box">
                            <label>Llegada</label>
                            <input type="text" id="f_inicio" name="fecha_inicio" required placeholder="Añadir fecha">
                        </div>
                        <div class="input-box">
                            <label>Salida</label>
                            <input type="text" id="f_fin" name="fecha_fin" required placeholder="Añadir fecha" disabled>
                        </div>
                        <div class="input-box">
                            <label>Huéspedes</label>
                            <input type="number" id="cant_huespedes" name="cant_huespedes" value="1" min="1" max="<?php echo $capacidadMaxima; ?>">
                        </div>
                    </div>

                    <div id="resumen" class="resumen-pago" style="display: none;">
                        <div class="resumen-row">
                            <span>$<?php echo number_format($p['precio_noche'], 2); ?> x <span id="noches_txt">0</span> noches</span>
                            <span>$<span id="subtotal_txt">0.00</span></span>
                        </div>
                        <div class="resumen-row total-row">
                            <span>Total</span>
                            <span>$<span id="total_txt">0.00</span></span>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn-reservar" disabled>
                        Reservar
                    </button>
                    <p style="text-align: center; font-size: 12px; color: var(--text-light); margin-top: 12px;">No se cobrará nada todavía</p>
                </form>
            </div>
        </aside>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const precioNoche = <?php echo (float)$p['precio_noche']; ?>;
    const capacidadMax = <?php echo (int)$capacidadMaxima; ?>;
    const inputInicio = document.getElementById('f_inicio');
    const inputFin = document.getElementById('f_fin');
    const inputHuespedes = document.getElementById('cant_huespedes');
    const resumen = document.getElementById('resumen');
    const btnSubmit = document.getElementById('btnSubmit');

    let ocupadas = [];
    let blockedRanges = [];
    let fpStart = null, fpEnd = null;

    function calcular() {
        const d1 = fpStart.selectedDates[0];
        const d2 = fpEnd.selectedDates[0];
        const personas = parseInt(inputHuespedes.value) || 1;

        if (d1 && d2 && d2 > d1) {
            const diffTime = Math.abs(d2 - d1);
            const noches = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (noches > 30) {
                alert("La estancia máxima es de 30 noches.");
                fpEnd.clear();
                actualizarEstadoBoton(false);
                return;
            }

            const total = (noches * precioNoche) * personas;
            document.getElementById('noches_txt').innerText = noches;
            document.getElementById('total_txt').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2});
            resumen.style.display = 'block';
            actualizarEstadoBoton(true);
        } else {
            resumen.style.display = 'none';
            actualizarEstadoBoton(false);
        }
    }

    function actualizarEstadoBoton(habilitar) {
        btnSubmit.disabled = !habilitar;
    }

    function autoSaltoMes(instance) {
        let mesesIntentados = 0;
        const limiteMeses = 12;
        while (mesesIntentados < limiteMeses) {
            const y = instance.currentYear;
            const m = instance.currentMonth;
            const ultimoDiaMes = new Date(y, m + 1, 0).getDate();
            let hayDiaLibre = false;
            for (let d = 1; d <= ultimoDiaMes; d++) {
                const fechaEvaluar = new Date(y, m, d);
                if (fechaEvaluar >= new Date().setHours(0,0,0,0) && instance.isEnabled(fechaEvaluar)) {
                    hayDiaLibre = true;
                    break;
                }
            }
            if (!hayDiaLibre) {
                instance.changeMonth(1);
                mesesIntentados++;
            } else { break; }
        }
    }

    function initFlatpickr(){
        fpStart = flatpickr(inputInicio, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: blockedRanges,
            onDayCreate: (dObj, dStr, fp, dayElem) => {
                if (dayElem.classList.contains("flatpickr-disabled")) dayElem.title = "Ocupado";
            },
            onReady: (sd, ds, instance) => autoSaltoMes(instance),
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    const sel = selectedDates[0];
                    inputFin.disabled = false;
                    const minOut = new Date(sel);
                    minOut.setDate(minOut.getDate() + 1);
                    const maxOut = new Date(sel);
                    maxOut.setDate(maxOut.getDate() + 30);
                    let primerBloqueo = null;
                    for(const r of ocupadas){
                        const ri = new Date(r.inicio + 'T00:00:00');
                        if (ri > sel){ primerBloqueo = ri; break; }
                    }
                    const limiteFinal = (primerBloqueo && primerBloqueo < maxOut) ? primerBloqueo : maxOut;
                    fpEnd.set('minDate', minOut);
                    fpEnd.set('maxDate', limiteFinal);
                    fpEnd.clear(); 
                    fpEnd.jumpToDate(minOut); 
                    setTimeout(() => fpEnd.open(), 100); 
                }
                calcular();
            }
        });

        fpEnd = flatpickr(inputFin, {
            dateFormat: 'Y-m-d',
            disable: blockedRanges,
            onDayCreate: (dObj, dStr, fp, dayElem) => {
                if (dayElem.classList.contains("flatpickr-disabled")) dayElem.title = "Ocupado";
            },
            onChange: calcular
        });
    }

    inputHuespedes.addEventListener('input', () => {
        if(inputHuespedes.value > capacidadMax) inputHuespedes.value = capacidadMax;
        if(inputHuespedes.value < 1) inputHuespedes.value = 1;
        calcular();
    });

    function fetchFechasOcupadas(){
        const id = <?php echo (int)$p['id_propiedad']; ?>;
        fetch(`../actions/reserva_actions.php?action=fechas_ocupadas&id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (Array.isArray(data)) {
                    ocupadas = data.map(r => ({inicio: r.fecha_inicio, fin: r.fecha_fin}));
                    blockedRanges = ocupadas.map(r => ({from: r.inicio, to: r.fin}));
                }
                initFlatpickr();
            }).catch(() => initFlatpickr());
    }

    document.getElementById('reservaForm').addEventListener('submit', (e) => {
        if (btnSubmit.disabled) {
            e.preventDefault();
            alert("Seleccione fechas válidas.");
        }
    });

    fetchFechasOcupadas();
</script>

</body>
</html>