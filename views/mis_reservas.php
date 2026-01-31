<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/reserva_controller.php';

if (!isset($_SESSION['user_id'])) { header("Location: views/login.php"); exit(); }

$resCtrl = new ReservaController($pdo);
$userRol = $_SESSION['user_rol'];
$userId = $_SESSION['user_id'];

// Cargamos la data según el rol
if ($userRol === 'anfitrion') {
    $reservas = $resCtrl->obtener_reservas_anfitrion($userId);
    $titulo = "Reservas Recibidas";
} else {
    $reservas = $resCtrl->obtener_reservas_usuario($userId);
    $titulo = "Mis Viajes";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php $userName = $_SESSION['user_name'] ?? 'Usuario'; ?>
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="../public/css/reservas.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <a href="../public/index.php" class="nav-logo"> <span class="brand-highlight">Wind</span>BnB</a>
        </div>
        
        <div class="nav-right">
            <?php if ($userRol === 'anfitrion'): ?>
                <a href="formulario_propiedad.php" class="nav-host-link">Añadir Propiedad</a>
            <?php endif; ?>

            <div class="user-menu-pill">
                <div class="hamburger-icon">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="presentation" focusable="false" class="hamburger-svg"><g fill="none" fill-rule="nonzero"><path d="m2 16h28"></path><path d="m2 24h28"></path><path d="m2 8h28"></path></g></svg>
                </div>
                <div class="user-avatar">
                   <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="presentation" focusable="false" class="user-avatar-svg"><path d="m16 .7c-8.437 0-15.3 6.863-15.3 15.3s6.863 15.3 15.3 15.3 15.3-6.863 15.3-15.3-6.863-15.3-15.3-15.3zm0 28c-4.021 0-7.605-1.884-9.933-4.81a12.425 12.425 0 0 1 6.451-4.4 6.507 6.507 0 0 1 -3.018-5.49c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5a6.513 6.513 0 0 1 -3.019 5.491 12.42 12.42 0 0 1 6.452 4.4c-2.328 2.925-5.912 4.809-9.933 4.809z"></path></svg>
                </div>
                
                <div class="user-dropdown">
                    <div class="dropdown-header">Hola, <strong><?php echo htmlspecialchars($userName); ?></strong></div>
                    <hr>
                    <a href="../public/index.php" class="dropdown-item">Inicio</a>
                    <a href="mis_reservas.php" class="dropdown-item">Mis Viajes</a>
                    <hr>
                    <a href="../actions/user_actions.php?action=logout" class="dropdown-item text-danger">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="reservations-container">
        <div class="reservations-header">
            <h1><?php echo $titulo; ?></h1>
            <p class="reservations-count"><?php echo count($reservas); ?> <?php echo count($reservas) === 1 ? 'reserva' : 'reservas'; ?></p>
        </div>

        <?php if (empty($reservas)): ?>
            <div class="empty-reservations">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                </svg>
                <h3>No tienes reservas</h3>
                <p><?php echo $userRol === 'anfitrion' ? 'Aún no has recibido ninguna reserva.' : 'Comienza a explorar y reserva tu próximo viaje.'; ?></p>
            </div>
        <?php else: ?>
            <div class="reservations-grid">
                <?php foreach ($reservas as $r): ?>
                    <div class="reservation-card">
                        <div class="reservation-card-header">
                            <div class="reservation-property">
                                <h3 class="reservation-property-title"><?php echo htmlspecialchars($r['titulo_propiedad']); ?></h3>
                                <p class="reservation-property-location">
                                    <?php echo $userRol === 'anfitrion' 
                                        ? 'Huésped: ' . htmlspecialchars($r['nombre']) 
                                        : htmlspecialchars($r['ubicacion_propiedad']); ?>
                                </p>
                            </div>
                            <span class="status-badge <?php echo $r['estado']; ?>">
                                <?php echo ucfirst($r['estado']); ?>
                            </span>
                        </div>

                        <div class="reservation-details">
                            <div class="detail-item">
                                <span class="detail-label">Fechas</span>
                                <span class="detail-value"><?php echo date('d M', strtotime($r['fecha_inicio'])); ?> - <?php echo date('d M Y', strtotime($r['fecha_fin'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total</span>
                                <span class="detail-value price">$<?php echo number_format($r['precio_total'], 2); ?></span>
                            </div>
                        </div>

                        <div class="reservation-actions">
                            <?php if ($r['estado'] === 'pendiente'): ?>
                                <?php if ($userRol === 'anfitrion'): ?>
                                    <form action="../actions/reserva_actions.php?action=confirmar" method="POST" class="inline-form">
                                        <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="btn-reservation btn-accept">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            Aceptar
                                        </button>
                                    </form>
                                    <form action="../actions/reserva_actions.php?action=cancelar" method="POST" class="inline-form">
                                        <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="button" class="btn-reservation btn-reject" onclick="confirmReject(this.form)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            Rechazar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="../actions/reserva_actions.php?action=cancelar" method="POST" class="inline-form">
                                        <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="button" class="btn-reservation btn-cancel" onclick="confirmCancel(this.form)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                            Cancelar Reserva
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="status-info">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Sin acciones pendientes
                                </div>
                            <?php endif; ?>
                            
                            <button type="button" onclick="window.open('https://wa.me/+58<?php echo htmlspecialchars($r['telefono']); ?>?text=Hola%20<?php echo htmlspecialchars($r['nombre']); ?>,%20te%20contacto%20por%20la%20reserva%20de%20la%20propiedad%20<?php echo htmlspecialchars($r['titulo_propiedad']); ?>.', '_blank')" class="btn-reservation btn-message">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-14h.1a4.6 4.6 0 0 1 3.3 1.3 4.6 4.6 0 0 1 1.3 3.3v.4c.03.11.08.2.16.27.08.06.18.09.28.09h.5a1 1 0 0 0 1-1h.5a1 1 0 0 0 1 1H21v1.1c.1.1.2.14.33.14s.24-.04.34-.14V11.5zM12 11h.01"></path></svg>
                                Enviar Mensaje
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <script>
    function confirmCancel(form) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción cancelará tu reserva de forma permanente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar reserva',
            cancelButtonText: 'No, mantener'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    function confirmReject(form) {
        Swal.fire({
            title: '¿Rechazar reserva?',
            text: "El huésped recibirá una notificación de la cancelación.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Volver'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
    </script>
</body>
</html>