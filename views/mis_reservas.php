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
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <a href="../public/index.php" class="nav-logo">WindBnB</a>
        </div>
        
        <div class="nav-right">
            <?php if ($userRol === 'anfitrion'): ?>
                <a href="formulario_propiedad.php" class="nav-host-link">Modo Anfitrión</a>
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
    
    <main class="container" style="padding: 20px;">
        <h1><?php echo $titulo; ?></h1>

        <table border="1" style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead style="background: #f8f8f8;">
                <tr>
                    <th>Propiedad</th>
                    <th><?php echo ($userRol === 'anfitrion') ? "Huésped" : "Ubicación"; ?></th>
                    <th>Fechas</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['titulo_propiedad']); ?></td>
                    <td><?php echo ($userRol === 'anfitrion') ? htmlspecialchars($r['nombre_huesped']) : htmlspecialchars($r['ubicacion_propiedad']); ?></td>
                    <td><?php echo $r['fecha_inicio'] . " al " . $r['fecha_fin']; ?></td>
                    <td><strong>$<?php echo number_format($r['precio_total'], 2); ?></strong></td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo ($r['estado']=='pendiente' ? '#fff3cd' : ($r['estado']=='confirmada' ? '#d4edda' : '#f8d7da')); ?>">
                            <?php echo ucfirst($r['estado']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($r['estado'] === 'pendiente'): ?>
                            <?php if ($userRol === 'anfitrion'): ?>
                                <form action="../actions/reserva_actions.php?action=confirmar" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" style="background:green; color:white;">Aceptar</button>
                                </form>
                            <?php endif; ?>
                            
                            <form action="../actions/reserva_actions.php?action=cancelar" method="POST" style="display:inline;">
                                <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" style="background:red; color:white;">
                                    <?php echo ($userRol === 'anfitrion') ? "Rechazar" : "Cancelar"; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled>Sin acciones</button>
                        <?php endif; ?>
                        
                        <button onclick="location.href='mensajes.php?con=<?php echo $r['id_reserva']; ?>'" style="background: #007bff; color:white;">Enviar Mensaje</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>