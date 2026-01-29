<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/property_controller.php';

$propCtrl = new PropertyController($pdo);
$propiedad = null;
$esEdicion = false;
$comodidadesSeleccionadas = [];

// 1. Lógica de carga de datos
if (isset($_GET['id'])) {
    $id_propiedad = (int)$_GET['id'];
    $propiedad = $propCtrl->obtener_propiedad_por_id($id_propiedad);
    
    if ($propiedad) {
        $esEdicion = true;
        // Obtenemos solo los IDs de las comodidades ya marcadas
        $stmtIds = $pdo->prepare("SELECT id_comodidad FROM propiedad_comodidades WHERE id_propiedad = ?");
        $stmtIds->execute([$id_propiedad]);
        $comodidadesSeleccionadas = $stmtIds->fetchAll(PDO::FETCH_COLUMN);
    }
}

// 2. Catálogo de comodidades
$stmtAll = $pdo->query("SELECT * FROM comodidades ORDER BY nombre ASC");
$todasComodidades = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = $esEdicion ? "Gestionar Propiedad" : "Publicar Nueva Propiedad";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tituloPagina; ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .card-form {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #ddd;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f7f7f7;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .btn-save {
            background: #ff385c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-secondary {
            background: #222;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body style="background: #fbfbfb; font-family: sans-serif;">

<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    
    <header style="margin-bottom: 30px;">
        <a href="../public/index.php" style="color: #ff385c; text-decoration: none;">← Volver al inicio</a>
        <h1 style="margin-top: 10px;"><?php echo $tituloPagina; ?></h1>
    </header>

    <section class="card-form">
        <div class="section-header">
            <h2 style="margin:0; font-size: 1.3em;">1. Datos Generales</h2>
            <?php if ($esEdicion): ?>
                <span style="background: #e7f3ff; color: #1877f2; padding: 4px 10px; border-radius: 20px; font-size: 0.8em;">Editando ID: #<?php echo $propiedad['id_propiedad']; ?></span>
            <?php endif; ?>
        </div>

        <form action="../actions/property_actions.php?action=<?php echo $esEdicion ? 'actualizar' : 'registrar'; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($esEdicion): ?>
                <input type="hidden" name="id_propiedad" value="<?php echo $propiedad['id_propiedad']; ?>">
            <?php endif; ?>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom: 5px;">Título del anuncio:</label>
                <input type="text" name="titulo" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                       value="<?php echo $esEdicion ? htmlspecialchars($propiedad['titulo']) : ''; ?>">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom: 5px;">Descripción:</label>
                <textarea name="descripcion" required style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"><?php echo $esEdicion ? htmlspecialchars($propiedad['descripcion']) : ''; ?></textarea>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom: 5px;">Capacidad:</label>
                    <input type="number" name="capacidad" min="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                           value="<?php echo $esEdicion ? $propiedad['capacidad'] : ''; ?>">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom: 5px;">Precio/Noche (USD):</label>
                    <input type="number" name="precio" step="0.01" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                           value="<?php echo $esEdicion ? $propiedad['precio_noche'] : ''; ?>">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom: 5px;">Ubicación:</label>
                <input type="text" name="ubicacion" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                       value="<?php echo $esEdicion ? htmlspecialchars($propiedad['ubicacion']) : ''; ?>">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom: 5px;">Imagen de la propiedad:</label>
                <input type="file" name="foto" accept="image/*" <?php echo $esEdicion ? '' : 'required'; ?>>
            </div>

            <button type="submit" class="btn-save">
                <?php echo $esEdicion ? "Actualizar Datos Básicos" : "Siguiente: Guardar Propiedad"; ?>
            </button>
        </form>
    </section>

    <section class="card-form" <?php echo !$esEdicion ? 'style="opacity: 0.5; pointer-events: none;"' : ''; ?>>
        <div class="section-header">
            <h2 style="margin:0; font-size: 1.3em;">2. Comodidades y Servicios</h2>
            <?php if (!$esEdicion): ?>
                <small style="color: #ff385c;">* Primero guarda los datos básicos</small>
            <?php endif; ?>
        </div>

        <form action="../actions/property_actions.php?action=<?php echo $esEdicion ? 'actualizar_comodidades' : 'agregar_comodidades'; ?>" method="POST">
            <input type="hidden" name="id_propiedad" value="<?php echo $propiedad['id_propiedad'] ?? ''; ?>">

            <div class="amenities-grid">
                <?php foreach ($todasComodidades as $com): ?>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="comodidades[]" value="<?php echo $com['id_comodidad']; ?>"
                            <?php echo in_array($com['id_comodidad'], $comodidadesSeleccionadas) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($com['nombre']); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-secondary">
                <?php echo $esEdicion ? "Actualizar Comodidades" : "Guardar Comodidades"; ?>
            </button>
        </form>
    </section>

</div>

</body>
</html>