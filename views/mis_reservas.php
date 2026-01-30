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
    <link rel="stylesheet" href="../public/css/styles.css?v=1.0">
    <link rel="stylesheet" href="../public/css/index.css?v=1.0">
    <link rel="stylesheet" href="../public/css/reservas.css?v=1.0">
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <a href="../public/index.php" class="nav-logo"> <span style="color: #1C726E;">Wind</span>BnB</a>
        </div>
        
        <div class="nav-right">
            <?php if ($userRol === 'anfitrion'): ?>
                <a href="formulario_propiedad.php" class="nav-host-link">Añadir Propiedad</a>
            <?php endif; ?>

            <div class="user-menu-pill">
                <div class="hamburger-icon">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="presentation" focusable="false" style="display: block; fill: none; height: 16px; width: 16px; stroke: currentcolor; stroke-width: 3; overflow: visible;"><g fill="none" fill-rule="nonzero"><path d="m2 16h28"></path><path d="m2 24h28"></path><path d="m2 8h28"></path></g></svg>
                </div>
                <div class="user-avatar">
                   <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="presentation" focusable="false" style="display: block; height: 30px; width: 30px; fill: currentcolor;"><path d="m16 .7c-8.437 0-15.3 6.863-15.3 15.3s6.863 15.3 15.3 15.3 15.3-6.863 15.3-15.3-6.863-15.3-15.3-15.3zm0 28c-4.021 0-7.605-1.884-9.933-4.81a12.425 12.425 0 0 1 6.451-4.4 6.507 6.507 0 0 1 -3.018-5.49c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5a6.513 6.513 0 0 1 -3.019 5.491 12.42 12.42 0 0 1 6.452 4.4c-2.328 2.925-5.912 4.809-9.933 4.809z"></path></svg>
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
                                        ? 'Huésped: ' . htmlspecialchars($r['nombre_huesped']) 
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
                                    <form action="../actions/reserva_actions.php?action=confirmar" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="btn-reservation btn-accept">Aceptar</button>
                                    </form>
                                    <form action="../actions/reserva_actions.php?action=cancelar" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="btn-reservation btn-reject">Rechazar</button>
                                    </form>
                                <?php else: ?>
                                    <form action="../actions/reserva_actions.php?action=cancelar" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="btn-reservation btn-cancel">Cancelar Reserva</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn-reservation btn-disabled" disabled>Sin acciones disponibles</button>
                            <?php endif; ?>
                            
                            <button onclick="window.open('https://wa.me/<?php echo htmlspecialchars($r['telefono_huesped']); ?>?text=Hola%20<?php echo htmlspecialchars($r['nombre_huesped']); ?>,%20te%20contacto%20por%20la%20reserva%20de%20la%20propiedad%20<?php echo htmlspecialchars($r['titulo_propiedad']); ?>.', '_blank')" class="btn-reservation btn-message">
                                Enviar Mensaje
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>