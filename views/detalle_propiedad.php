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
    <link rel="stylesheet" href="../public/css/detalle_propiedad.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<main class="container">
    <div class="header-actions">
        <a href="../public/index.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Volver al buscador
        </a>
    </div>

    <section class="main-image-container">
        <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" alt="Imagen de la propiedad">
    </section>

    <div class="detalle-layout">
        <section class="content-section">
            <div class="title-block">
                <h1><?php echo htmlspecialchars($p['titulo']); ?></h1>
                <div class="meta-info">
                    <span class="location">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <?php echo htmlspecialchars($p['ubicacion']); ?>
                    </span>
                    <span class="divider">·</span>
                    <span class="capacity">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Hasta <?php echo $capacidadMaxima; ?> huéspedes
                    </span>
                </div>
            </div>

            <div class="host-badge">
                <div class="host-info">
                    <h3>Anfitrión: <?php echo htmlspecialchars($p['anfitrion_nombre']); ?></h3>
                </div>
                <div class="host-avatar">
                   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40" style="color: #717171;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
            </div>

            <hr class="section-divider">

            <?php if (!empty($comodidades)): ?>
                <h2 class="section-title">Lo que este lugar ofrece</h2>
                <div class="amenities-grid-v2">
                    <?php foreach ($comodidades as $com): ?>
                        <div class="amenity-card-item">
                            <span class="amenity-icon-v2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <span class="amenity-name"><?php echo htmlspecialchars($com['nombre']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr class="section-divider">
            <?php endif; ?>

            <div class="trust-highlights">
                <div class="highlight-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <div>
                        <h4>Garantía WindBnB</h4>
                        <p>Tu estancia está protegida en caso de cancelaciones o problemas de entrada.</p>
                    </div>
                </div>
                <div class="highlight-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <div>
                        <h4>Cancelación gratuita</h4>
                        <p>Cancela antes de las 48 horas previas al viaje para un reembolso completo.</p>
                    </div>
                </div>
            </div>

            <hr class="section-divider">

            <h2 class="section-title">Sobre este espacio</h2>
            <p class="description-text"><?php echo htmlspecialchars($p['descripcion']); ?></p>
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

                    <div id="resumen" class="resumen-pago hidden">
                        <div class="resumen-row">
                            <span>$<?php echo number_format($p['precio_noche'], 2); ?> x <span id="noches_txt">0</span> noches</span>
                        </div>
                        <div class="resumen-row total-row">
                            <span>Total</span>
                            <span>$<span id="total_txt">0.00</span></span>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn-reservar" disabled>
                        Reservar
                    </button>
                    <p class="disclaimer-text">No se cobrará nada todavía</p>
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Estancia máxima excedida',
                    text: 'La estancia máxima es de 30 noches.',
                    confirmButtonColor: '#ff385c'
                });
                fpEnd.clear();
                actualizarEstadoBoton(false);
                return;
            }

            const total = (noches * precioNoche);
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
            Swal.fire({
                icon: 'error',
                title: 'Fechas inválidas',
                text: 'Por favor, seleccione fechas válidas para continuar.',
                confirmButtonColor: '#ff385c'
            });
        }
    });

    fetchFechasOcupadas();
</script>

</body>
</html>