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
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav> <a href="index.php">← Volver al Inicio</a> </nav>
    
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
                                    <button type="submit" style="background:green; color:white;">Aceptar</button>
                                </form>
                            <?php endif; ?>
                            
                            <form action="../actions/reserva_actions.php?action=cancelar" method="POST" style="display:inline;">
                                <input type="hidden" name="id_reserva" value="<?php echo $r['id_reserva']; ?>">
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