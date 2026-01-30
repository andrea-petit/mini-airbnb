<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/property_controller.php';

$propCtrl = new PropertyController($pdo);

// Protegemos la ruta: si no hay sesión, al login
$id_usuario_sesion = $propCtrl->chequear_id(); 

$propiedad = null;
$esEdicion = false;
$comodidadesSeleccionadas = [];
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// 1. Carga de datos a través del CONTROLADOR únicamente
if (isset($_GET['id'])) {
    $id_propiedad = (int)$_GET['id'];
    $propiedad = $propCtrl->obtener_propiedad_por_id($id_propiedad);
    
    if ($propiedad) {
        // Es edición si existe la propiedad y NO trae la bandera de "nueva"
        $esEdicion = !isset($_GET['new']); 
        $comodidadesSeleccionadas = $propCtrl->obtener_comodidades_seleccionadas($id_propiedad);
    } else {
        // Si mandan un ID que no existe
        header("Location: ../public/index.php");
        exit();
    }
}

// 2. Catálogo de comodidades (Pedido al controlador)
$todasComodidades = $propCtrl->obtener_todas_las_comodidades();

// 3. Títulos dinámicos
if ($step == 2) {
    $tituloPagina = "Agregar Comodidades";
} else {
    $tituloPagina = $esEdicion ? "Editar Propiedad" : "Agregar Nueva Propiedad";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tituloPagina; ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        :root { --primary: #ff385c; --dark: #222; --border: #ddd; }
        body { background: #fbfbfb; font-family: 'Segoe UI', sans-serif; color: #333; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .tabs-nav { display: flex; gap: 5px; margin-bottom: -1px; }
        .tab-btn { 
            padding: 12px 25px; border-radius: 12px 12px 0 0; border: 1px solid var(--border); 
            border-bottom: none; background: #eee; font-weight: 600; color: #777;
        }
        .tab-btn.active { background: white; color: var(--primary); border-top: 3px solid var(--primary); }
        .tab-btn.disabled { opacity: 0.5; cursor: not-allowed; }
        .tab-btn.clickable { cursor: pointer; }
        .card-form { background: #fff; padding: 30px; border-radius: 0 12px 12px 12px; border: 1px solid var(--border); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .hidden { display: none !important; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 15px; box-sizing: border-box; }
        .btn-main { background: var(--primary); color: white; padding: 14px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; }
        .amenities-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin: 25px 0; }
        .amenity-item { display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #eee; border-radius: 10px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <header style="margin-bottom: 30px;">
        <a href="../public/index.php" style="color: var(--primary); text-decoration: none; font-weight: bold;">← Volver al Panel</a>
        <h1><?php echo $tituloPagina; ?></h1>
    </header>

    <div class="tabs-nav">
        <button class="tab-btn <?php echo $step == 1 ? 'active' : ''; ?> <?php echo $esEdicion ? 'clickable' : 'disabled'; ?>" 
                <?php echo $esEdicion ? "onclick=\"switchTab('datos')\"" : ""; ?>>
            1. Datos Básicos
        </button>
        <button class="tab-btn <?php echo $step == 2 ? 'active' : ''; ?> <?php echo ($esEdicion || $step == 2) ? 'clickable' : 'disabled'; ?>" 
                <?php echo ($esEdicion || $step == 2) ? "onclick=\"switchTab('comodidades')\"" : ""; ?>>
            2. Comodidades
        </button>
    </div>

    <section id="section-datos" class="card-form <?php echo ($step == 2) ? 'hidden' : ''; ?>">
        <form action="../actions/property_actions.php?action=<?php echo $esEdicion ? 'actualizar' : 'registrar'; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($propiedad): ?>
                <input type="hidden" name="id_propiedad" value="<?php echo $propiedad['id_propiedad']; ?>">
            <?php endif; ?>

            <label>Título del anuncio</label>
            <input type="text" name="titulo" required value="<?php echo $propiedad ? htmlspecialchars($propiedad['titulo']) : ''; ?>">

            <label>Descripción</label>
            <textarea name="descripcion" required><?php echo $propiedad ? htmlspecialchars($propiedad['descripcion']) : ''; ?></textarea>

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;"><label>Capacidad</label><input type="number" name="capacidad" min="1" required value="<?php echo $propiedad ? $propiedad['capacidad'] : ''; ?>"></div>
                <div style="flex: 1;"><label>Precio/Noche (USD)</label><input type="number" name="precio" step="0.01" required value="<?php echo $propiedad ? $propiedad['precio_noche'] : ''; ?>"></div>
            </div>

            <label>Ubicación</label>
            <input type="text" name="ubicacion" required value="<?php echo $propiedad ? htmlspecialchars($propiedad['ubicacion']) : ''; ?>">

            <label>Imagen</label>
            <?php if($propiedad && !empty($propiedad['imagen_url'])): ?>
                <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px;">
                    <img src="../public/uploads/<?php echo $propiedad['imagen_url']; ?>" style="width: 80px; height: 60px; object-fit: cover;">
                    <span style="font-size: 0.8em; color: #666;">Imagen actual. Sube otra para cambiarla.</span>
                </div>
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*" <?php echo ($propiedad) ? '' : 'required'; ?>>

            <button type="submit" class="btn-main">
                <?php echo $esEdicion ? "Actualizar Información" : "Guardar y Continuar →"; ?>
            </button>
        </form>
    </section>

    <section id="section-comodidades" class="card-form <?php echo ($step == 1) ? 'hidden' : ''; ?>">
        <h2>¿Qué ofrece tu alojamiento?</h2>
        <form action="../actions/property_actions.php?action=actualizar_comodidades" method="POST">
            <input type="hidden" name="id_propiedad" value="<?php echo $propiedad['id_propiedad'] ?? ''; ?>">
            <?php if(isset($_GET['new'])): ?> <input type="hidden" name="is_new" value="1"> <?php endif; ?>

            <div class="amenities-grid">
                <?php foreach ($todasComodidades as $com): ?>
                    <label class="amenity-item">
                        <input type="checkbox" name="comodidades[]" value="<?php echo $com['id_comodidad']; ?>"
                            <?php echo in_array($com['id_comodidad'], $comodidadesSeleccionadas) ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($com['nombre']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-main" style="background: var(--dark);">
                <?php echo ($esEdicion) ? "Actualizar Comodidades" : "Publicar Propiedad Ahora"; ?>
            </button>
        </form>
    </section>
</div>

<script>
    function switchTab(tab) {
        const isEdicion = <?php echo json_encode($esEdicion); ?>;
        const currentStep = <?php echo $step; ?>;
        if (!isEdicion && currentStep === 2 && tab === 'datos') return;

        const secDatos = document.getElementById('section-datos');
        const secComodidades = document.getElementById('section-comodidades');
        if (tab === 'datos') {
            secDatos.classList.remove('hidden');
            secComodidades.classList.add('hidden');
        } else {
            secDatos.classList.add('hidden');
            secComodidades.classList.remove('hidden');
        }
    }
</script>
</body>
</html>