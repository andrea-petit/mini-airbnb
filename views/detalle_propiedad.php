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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($p['titulo']); ?> - Detalles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>

<main class="container">
    <div class="detalle-layout" style="display: flex; gap: 40px;">
        
        <section style="flex: 2;">
            <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" style="width: 40%; border-radius: 15px;">
            <h1><?php echo htmlspecialchars($p['titulo']); ?></h1>
            <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($p['ubicacion']); ?></p>
            <p><strong>Anfitrión:</strong> <?php echo htmlspecialchars($p['anfitrion_nombre']); ?></p>
            <hr>
            <p><?php echo nl2br(htmlspecialchars($p['descripcion'])); ?></p>
        </section>

        <aside style="flex: 1; border: 1px solid #ddd; padding: 20px; border-radius: 12px; height: fit-content;">
            <h2>$<?php echo number_format($p['precio_noche'], 2); ?> <small>/ noche</small></h2>

            <form action="confirmar_reserva.php" method="POST">
                <input type="hidden" name="id_propiedad" value="<?php echo $p['id_propiedad']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <label>Llegada:</label>
                <input type="date" id="f_inicio" name="fecha_inicio" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; margin-bottom: 10px;">
                
                <label>Salida:</label>
                <input type="date" id="f_fin" name="fecha_fin" required style="width: 100%; margin-bottom: 10px;">

                <label>Huéspedes:</label>
                <input type="number" id="cant_huespedes" name="cant_huespedes" value="1" min="1" style="width: 100%; margin-bottom: 20px;">

                <div id="resumen" style="background: #f0f0f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none;">
                    <p>Noches: <span id="noches_txt">0</span></p>
                    <p>Total: <strong>$<span id="total_txt">0.00</span></strong></p>
                </div>

                <!-- Fechas ocupadas gestionadas por el datepicker -->

                <button type="submit" class="btn-reservar" style="width: 100%; background: #ff385c; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                    Reservar
                </button>
            </form>
        </aside>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const precioNoche = <?php echo $p['precio_noche']; ?>;
    const inputInicio = document.getElementById('f_inicio');
    const inputFin = document.getElementById('f_fin');
    const inputHuespedes = document.getElementById('cant_huespedes');
    const resumen = document.getElementById('resumen');

    function calcular() {
        const d1 = inputInicio._flatpickr && inputInicio._flatpickr.selectedDates[0];
        const d2 = inputFin._flatpickr && inputFin._flatpickr.selectedDates[0];
        const personas = parseInt(inputHuespedes.value) || 1;
        if (d1 && d2 && d2 > d1) {
            const diffTime = Math.abs(d2 - d1);
            const noches = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const total = (noches * precioNoche) * personas;
            document.getElementById('noches_txt').innerText = noches;
            document.getElementById('total_txt').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2});
            resumen.style.display = 'block';
        } else {
            resumen.style.display = 'none';
        }
    }

    inputHuespedes.addEventListener('input', calcular);

    function showMessage(msg){
        let el = document.getElementById('msg_solapamiento');
        if (!el){ el = document.createElement('div'); el.id='msg_solapamiento'; el.style.color='#a60000'; el.style.marginTop='8px'; resumen.parentNode.insertBefore(el, resumen.nextSibling); }
        el.textContent = msg;
    }
    function hideMessage(){ const el = document.getElementById('msg_solapamiento'); if(el) el.remove(); }

    let ocupadas = [];
    let blockedRanges = [];
    let fpStart = null, fpEnd = null;

    function fetchFechasOcupadas(){
        const id = <?php echo intval($p['id_propiedad']); ?>;
        fetch(`../actions/reserva_actions.php?action=fechas_ocupadas&id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                ocupadas = data.map(range => ({inicio: range.fecha_inicio, fin: range.fecha_fin}));
                blockedRanges = ocupadas.map(r => ({from: r.inicio, to: r.fin}));
                initFlatpickr();
            }).catch(err => console.error('Error cargando fechas ocupadas', err));
    }

    function rangesOverlap(start, end, range){
        const a = start.getTime();
        const b = end.getTime();
        const ri = new Date(range.inicio + 'T00:00:00').getTime();
        const rf = new Date(range.fin + 'T00:00:00').getTime();
        return a < rf && b > ri;
    }

    function initFlatpickr(){
        if (fpStart) fpStart.destroy();
        if (fpEnd) fpEnd.destroy();

        fpStart = flatpickr(inputInicio, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: blockedRanges,
            onChange: function(selectedDates){
                hideMessage();
                if (!selectedDates || selectedDates.length === 0) return;
                const sel = selectedDates[0];
                const minEnd = new Date(sel);
                minEnd.setDate(minEnd.getDate() + 1);
                fpEnd.set('minDate', minEnd);
                let maxEnd = null;
                for(const r of ocupadas){
                    const ri = new Date(r.inicio + 'T00:00:00');
                    if (ri > sel){ maxEnd = new Date(ri); maxEnd.setDate(maxEnd.getDate() - 1); break; }
                }
                if (maxEnd){ fpEnd.set('maxDate', maxEnd); const curEnd = fpEnd.selectedDates[0]; if (curEnd && curEnd > maxEnd) fpEnd.clear(); }
                else { fpEnd.set('maxDate', null); }
                calcular();
            }
        });

        fpEnd = flatpickr(inputFin, {
            dateFormat: 'Y-m-d',
            disable: blockedRanges,
            onChange: function(){ hideMessage(); calcular(); }
        });
        // Si el mes mostrado inicialmente está completamente bloqueado, avanzar hasta encontrar un mes con al menos un día habilitado
        function monthFullyBlocked(fp){
            try{
                const year = fp.currentYear;
                const month = fp.currentMonth;
                const daysInMonth = new Date(year, month+1, 0).getDate();
                for(let d=1; d<=daysInMonth; d++){
                    const date = new Date(year, month, d, 12,0,0);
                    if (fp.isEnabled(date)) return false;
                }
                return true;
            }catch(e){ return false; }
        }

        // Advance up to 12 months to find a not-fully-blocked month
        for(let i=0;i<12;i++){
            if (monthFullyBlocked(fpStart)) fpStart.changeMonth(1);
            else break;
        }
        for(let i=0;i<12;i++){
            if (monthFullyBlocked(fpEnd)) fpEnd.changeMonth(1);
            else break;
        }
    }

    const form = document.querySelector('form[action="confirmar_reserva.php"]');
    form.addEventListener('submit', function(e){
        hideMessage();
        const inicio = inputInicio._flatpickr && inputInicio._flatpickr.selectedDates[0];
        const fin = inputFin._flatpickr && inputFin._flatpickr.selectedDates[0];
        if (!inicio || !fin) return;
        for(const r of ocupadas){ if (rangesOverlap(inicio, fin, r)){ e.preventDefault(); showMessage('El rango seleccionado se solapa con reservas existentes. Elija otras fechas.'); return false; } }
    });

    fetchFechasOcupadas();
</script>

</body>
</html>