<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/property_controller.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRol = $_SESSION['user_rol']; 

$propCtrl = new PropertyController($pdo);

// Lógica de obtención de datos según el Rol
if ($userRol === 'anfitrion') {
    // El anfitrión ve sus propias propiedades
    $misPropiedades = $propCtrl->obtener_propiedades_por_usuario($userId) ?: [];
} else {
    // El huésped ve el feed o los resultados de búsqueda
    if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
        $busqueda = trim($_GET['buscar']);
        $feedPropiedades = $propCtrl->buscar_propiedades($busqueda) ?: [];
    } else {
        $feedPropiedades = $propCtrl->feed_propiedades_disponibles() ?: [];
    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WindBnB - Dashboard</title>
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/index.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <nav class="navbar">
        <div class="logo-container">
            <a href="index.php" class="nav-logo"> <span class="brand-highlight">Wind</span>BnB</a>
        </div>
        
        <?php if ($userRol === 'huesped'): ?>
        <div class="nav-search">
            <form action="index.php" method="GET">
                <div class="search-input-group">
                    <svg class="search-icon" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="presentation" focusable="false">
                        <g fill="none">
                            <path d="m13 24c6.0751322 0 11-4.9248678 11-11 0-6.07513225-4.9248678-11-11-11-6.07513225 0-11 4.92486775-11 11 0 6.0751322 4.92486775 11 11 11zm8-3 9 9"></path>
                        </g>
                    </svg>
                    <input type="text" name="buscar" placeholder="Busca destinos..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                    
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="nav-right">
            <?php if ($userRol === 'anfitrion'): ?>
                <a href="../views/formulario_propiedad.php" class="nav-host-link">Añadir Propiedad</a>
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
                    <a href="index.php" class="dropdown-item">Inicio</a>
                    <a href="../views/mis_reservas.php" class="dropdown-item">Mis Viajes</a>
                    <hr>
                    <a href="../actions/user_actions.php?action=logout" class="dropdown-item text-danger">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container main-container">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                ¡Operación realizada con éxito!
            </div>
        <?php endif; ?>

        <?php if ($userRol === 'huesped' && !empty($_GET['buscar'])): ?>
            <div class="empty-state-container">
                <a href="index.php" class="btn-clear">Borrar filtros</a>
            </div>
        <?php endif; ?>

        <?php if ($userRol === 'huesped'): ?>
            <section>
                <h2><?php echo !empty($_GET['buscar']) ? 'Resultados de la búsqueda' : 'Alojamientos Disponibles'; ?></h2>
                
                <?php if (empty($feedPropiedades)): ?>
                    <div class="empty-state">
                        <p>No se encontraron propiedades.</p>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($feedPropiedades as $prop): ?>
                            <a href="../views/detalle_propiedad.php?id=<?php echo $prop['id_propiedad']; ?>" class="card card-link">
                                <div class="card-img-container">
                                    <img src="../public/uploads/<?php echo htmlspecialchars($prop['imagen_url']); ?>" alt="<?php echo htmlspecialchars($prop['titulo']); ?>" class="card-img">
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($prop['titulo']); ?></h3>
                                    <p class="card-location">Ubicacion: <?php echo htmlspecialchars($prop['ubicacion']); ?></p>
                                    <p class="card-price"><strong>$<?php echo number_format($prop['precio_noche'], 2); ?></strong> / noche</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <section>
                <div class="section-header">
                    <h2>Mis Propiedades Anunciadas</h2>
                    <span class="text-count"><?php echo count($misPropiedades); ?> propiedades activas</span>
                </div>

                <?php if (empty($misPropiedades)): ?>
                    <div class="empty-state-host">
                        <p>Aún no has publicado ninguna propiedad.</p>
                        <a href="../views/formulario_propiedad.php" class="link-highlight-bold">Empieza a ganar dinero ahora</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($misPropiedades as $p): ?>
                            <div class="card host-card">
                                <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" class="host-card-img">
                                <h3 class="host-card-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                                <div class="host-actions">
                                    <a href="../views/formulario_propiedad.php?id=<?php echo $p['uuid']; ?>" class="btn-action-edit">Editar</a>
                                    <button onclick="confirmDelete('<?php echo $p['uuid']; ?>')" class="btn-action-delete">Eliminar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="main-footer">
        &copy; 2026 WindBnB. Todos los derechos reservados.
    </footer>

    <script>
    function confirmDelete(propertyId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = '../actions/property_actions.php?action=eliminar&id=' + propertyId;
            }
        });
    }
    </script>

</body>
<script>
    function validarSoloLetrasYEspacios(event) {
    const char = String.fromCharCode(event.which);
        if (!/[a-zA-ZáéíóúÁÉÍÓÚñÑ'\s]/.test(char)) {
            event.preventDefault();
        }
}
</script>
</html>