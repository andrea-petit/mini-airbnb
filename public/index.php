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
    <title>Airbnb Clone - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-container {
            max-width: 800px;
            margin: 20px auto 40px;
            text-align: center;
        }
        .search-input-group {
            display: flex;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        .search-input-group input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            outline: none;
            font-size: 16px;
        }
        .btn-search {
            background: #ff385c;
            color: white;
            border: none;
            padding: 0 30px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-search:hover { background: #e31c5f; }
        
        .btn-clear {
            display: inline-block;
            margin-top: 10px;
            color: #717171;
            text-decoration: underline;
            font-size: 0.9em;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

    <nav style="display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: #fff; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000;">
        <div class="logo">
            <a href="index.php" style="color: #ff385c; font-size: 1.5rem; font-weight: bold; text-decoration: none;">AirbnbClone</a>
        </div>
        
        <div class="user-info">
            <span>Hola, <strong><?php echo htmlspecialchars($userName); ?></strong></span>
            <span style="margin: 0 10px; color: #ddd;">|</span>
            <a href="index.php" style="text-decoration: none; color: #444;">Inicio</a>
            <a href="../views/mis_reservas.php" style="text-decoration: none; color: #444; margin-left: 15px;">Mis Reservas</a>
            
            <?php if ($userRol === 'anfitrion'): ?>
                <a href="../views/formulario_propiedad.php" style="background:#ff385c; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; margin-left: 15px;">Anunciar propiedad</a>
            <?php endif; ?>
            
            <a href="../actions/user_actions.php?action=logout" style="color:#ff385c; margin-left:15px; text-decoration: none; font-weight: bold;">Salir</a>
        </div>
    </nav>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                ¡Operación realizada con éxito!
            </div>
        <?php endif; ?>

        <?php if ($userRol === 'huesped'): ?>
            <section class="search-container">
                <form action="index.php" method="GET">
                    <div class="search-input-group">
                        <input type="text" name="buscar" placeholder="Busca por título o ubicación..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                        <button type="submit" class="btn-search">Buscar</button>
                    </div>
                    <?php if (!empty($_GET['buscar'])): ?>
                        <a href="index.php" class="btn-clear">Limpiar filtros</a>
                    <?php endif; ?>
                </form>
            </section>

            <section>
                <h2><?php echo !empty($_GET['buscar']) ? 'Resultados de la búsqueda' : 'Alojamientos Disponibles'; ?></h2>
                
                <?php if (empty($feedPropiedades)): ?>
                    <div style="text-align: center; padding: 50px; color: #717171;">
                        <p style="font-size: 1.2em;">No encontramos alojamientos que coincidan con tu búsqueda.</p>
                        <a href="index.php" style="color: #ff385c;">Ver todas las propiedades</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($feedPropiedades as $p): ?>
                            <div class="card" onclick="location.href='../views/detalle_propiedad.php?id=<?php echo $p['id_propiedad']; ?>'" style="border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div style="position: relative;">
                                    <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" style="width: 100%; height: 220px; object-fit: cover;">
                                </div>
                                <div style="padding: 15px;">
                                    <h3 style="margin: 0 0 5px 0; font-size: 1.1em;"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                                    <p style="color: #717171; margin: 0 0 10px 0; font-size: 0.9em;"><?php echo htmlspecialchars($p['ubicacion']); ?></p>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <strong>$<?php echo number_format($p['precio_noche'], 2); ?> <span style="font-weight: normal; color: #717171;">/ noche</span></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        <?php else: ?>
            <section>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Mis Propiedades Anunciadas</h2>
                    <span style="color: #717171;"><?php echo count($misPropiedades); ?> propiedades activas</span>
                </div>

                <?php if (empty($misPropiedades)): ?>
                    <div style="border: 2px dashed #ddd; padding: 40px; text-align: center; border-radius: 15px;">
                        <p>Aún no has publicado ninguna propiedad.</p>
                        <a href="../views/formulario_propiedad.php" style="color: #ff385c; font-weight: bold;">Empieza a ganar dinero ahora</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($misPropiedades as $p): ?>
                            <div class="card" style="border: 1px solid #ddd; padding: 10px; border-radius: 12px; background: #fff;">
                                <img src="../public/uploads/<?php echo $p['imagen_url']; ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                                <h3 style="font-size: 1em; margin: 0 0 10px 0;"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                                <div style="display: flex; gap: 8px;">
                                    <a href="../views/formulario_propiedad.php?id=<?php echo $p['id_propiedad']; ?>" style="flex: 1; text-align: center; padding: 8px; background: #f7f7f7; border-radius: 6px; text-decoration: none; color: #333; font-size: 0.9em; border: 1px solid #ddd;">Editar</a>
                                    <button onclick="if(confirm('¿Estás seguro de que quieres eliminar esta propiedad?')) location.href='../actions/property_actions.php?action=eliminar&id=<?php echo $p['id_propiedad']; ?>'" style="flex: 1; padding: 8px; background: #fff; border: 1px solid #ff385c; color: #ff385c; border-radius: 6px; cursor: pointer; font-size: 0.9em;">Eliminar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer style="margin-top: 50px; padding: 30px; border-top: 1px solid #eee; text-align: center; color: #717171; font-size: 0.9em;">
        &copy; 2026 Airbnb Clone. Todos los derechos reservados.
    </footer>

</body>
</html>