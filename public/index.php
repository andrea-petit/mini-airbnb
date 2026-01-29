<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/property_controller.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: views/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRol = $_SESSION['user_rol']; 

$propCtrl = new PropertyController($pdo);

if ($userRol === 'anfitrion') {
    $misPropiedades = $propCtrl->obtener_propiedades_por_usuario($userId) ?: [];
} else {
    $feedPropiedades = $propCtrl->feed_propiedades_disponibles() ?: [];
}

if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $feedPropiedades = $propCtrl->buscar_propiedades($_GET['buscar']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Airbnb Clone - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav style="display: flex; justify-content: space-between; padding: 20px; background: #fff; border-bottom: 1px solid #ddd;">
        <span>Bienvenido, <strong><?php echo htmlspecialchars($userName); ?></strong></span>
        <div class="menu">
            <a href="index.php">Inicio</a>
            <a href="../views/mis_reservas.php">Reservas</a>
            
            <?php if ($userRol === 'anfitrion'): ?>
                <a href="../views/formulario_propiedad.php" class="btn-primary" style="background:#ff385c; color:white; padding:8px 15px; border-radius:8px; text-decoration:none;">Anunciar propiedad</a>
            <?php endif; ?>
            <a href="../actions/user_actions.php?action=logout" style="color:red; margin-left:15px;">Cerrar Sesión</a>
        </div>
    </nav>

    <main class="container" style="padding: 20px;">
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ¡Acción realizada con éxito!
            </div>
        <?php endif; ?>

        <?php if ($userRol === 'huesped'): ?>
            <section class="search-bar" style="margin-bottom: 30px;">
                <form action="index.php" method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="buscar" placeholder="¿A dónde quieres ir?" value="<?php echo $_GET['buscar'] ?? ''; ?>" style="flex: 1; padding: 12px; border-radius: 25px; border: 1px solid #ccc;">
                    <button type="submit" style="padding: 10px 25px; border-radius: 25px; background: #ff385c; color: white; border: none; cursor: pointer;">Buscar</button>
                </form>
            </section>

            <section>
                <h2>Alojamientos Disponibles</h2>
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php if (empty($feedPropiedades)): echo "<p>No se encontraron resultados.</p>"; endif; ?>
                    <?php foreach ($feedPropiedades as $p): ?>
                        <div class="card" style="border: 1px solid #eee; border-radius: 12px; overflow: hidden;">
                            <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <div style="padding: 15px;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                                <p style="color: #666;"><?php echo htmlspecialchars($p['ubicacion']); ?></p>
                                <strong>$<?php echo number_format($p['precio_noche'], 2); ?> USD / noche</strong>
                                <button onclick="location.href='../views/detalle_propiedad.php?id=<?php echo $p['id_propiedad']; ?>'" style="width: 100%; margin-top: 10px; padding: 8px; border-radius: 8px; border: 1px solid #ff385c; color: #ff385c; background: white; cursor: pointer;">Ver detalles</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php else: ?>
            <section>
                <h2>Mis Propiedades</h2>
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <?php foreach ($misPropiedades as $p): ?>
                        <div class="card" style="border: 1px solid #ddd; padding: 10px; border-radius: 12px;">
                            <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                            <h3><?php echo htmlspecialchars($p['titulo']); ?></h3>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="location.href='../views/formulario_propiedad.php?id=<?php echo $p['id_propiedad']; ?>'" style="flex: 1;">Editar</button>
                                <button onclick="if(confirm('¿Borrar?')) location.href='../actions/property_actions.php?action=eliminar&id=<?php echo $p['id_propiedad']; ?>'" style="flex: 1; background: #eee;">Eliminar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>