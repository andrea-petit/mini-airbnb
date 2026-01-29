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

// Capacidad máxima desde la BD (por defecto 1 si no existe el campo)
$capacidadMaxima = $p['capacidad'] ?? 5; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($p['titulo']); ?> - Detalles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../public/css/styles.css">
    <style>
        /* Pintar de rojo los días deshabilitados (ocupados) */
        .flatpickr-day.flatpickr-disabled {
            background-color: #ffe5e5 !important;
            color: #d90429 !important;
            text-decoration: line-through;
            opacity: 1 !important;
        }
        .btn-reservar:disabled {
            background-color: #ccc !important;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>

<main class="container">
    <div class="detalle-layout" style="display: flex; gap: 40px; margin-top: 30px;">
        
        <section style="flex: 2;">
            <a href="../public/index.php" style="text-decoration: none; color: #ff385c;">← Volver al buscador</a>
            <div style="margin-top: 20px;">
                <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 15px;">
            </div>
            <h1><?php echo htmlspecialchars($p['titulo']); ?></h1>
            <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($p['ubicacion']); ?></p>
            <p><strong>Anfitrión:</strong> <?php echo htmlspecialchars($p['anfitrion_nombre']); ?></p>
            <p><strong>Capacidad Máxima:</strong> <?php echo $capacidadMaxima; ?> personas</p>
            <hr>
            <p><?php echo nl2br(htmlspecialchars($p['descripcion'])); ?></p>
        </section>

        <aside style="flex: 1; border: 1px solid #ddd; padding: 25px; border-radius: 12px; height: fit-content; box-shadow: 0 6px 16px rgba(0,0,0,0.12);">
            <h2 style="margin-top: 0;">$<?php echo number_format($p['precio_noche'], 2); ?> <small style="font-size: 0.5em; color: #666;">/ noche</small></h2>

            <form action="confirmar_reserva.php" method="POST" id="reservaForm">
                <input type="hidden" name="id_propiedad" value="<?php echo $p['id_propiedad']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Llegada:</label>
                    <input type="text" id="f_inicio" name="fecha_inicio" required placeholder="Seleccione fecha" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Salida:</label>
                    <input type="text" id="f_fin" name="fecha_fin" required placeholder="Primero elija llegada" disabled style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Huéspedes (Máx: <?php echo $capacidadMaxima; ?>):</label>
                    <input type="number" id="cant_huespedes" name="cant_huespedes" value="1" min="1" max="<?php echo $capacidadMaxima; ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>

                <div id="resumen" style="background: #f7f7f7; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none; border: 1px solid #eee;">
                    <p style="margin: 5px 0;">Noches: <span id="noches_txt">0</span></p>
                    <p style="margin: 5px 0;">Precio x noche: $<?php echo number_format($p['precio_noche'], 2); ?></p>
                    <hr>
                    <p style="margin: 5px 0; font-size: 1.1em;">Total: <strong>$<span id="total_txt">0.00</span></strong></p>
                </div>

                <button type="submit" id="btnSubmit" class="btn-reservar" style="width: 100%; background: #ff385c; color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1em;" disabled>
                    Reservar ahora
                </button>
            </form>
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
        // CALENDARIO DE LLEGADA
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
                    
                    // Definir límites de salida
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
                    
                    // ACTUALIZACIÓN CORRELATIVA
                    fpEnd.set('minDate', minOut);
                    fpEnd.set('maxDate', limiteFinal);
                    fpEnd.clear(); 
                    
                    // OBLIGAR al calendario de salida a saltar al mes de entrada
                    fpEnd.jumpToDate(minOut); 
                    
                    setTimeout(() => fpEnd.open(), 100); 
                }
                calcular();
            }
        });

        // CALENDARIO DE SALIDA
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