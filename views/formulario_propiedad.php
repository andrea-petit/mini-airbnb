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
    $uuid = $_GET['id'];
    
    // Verificamos propiedad por UUID (IDOR check)
    if (!$propCtrl->verificar_propiedad_por_uuid($uuid)) {
        header("Location: ../public/templates/403.php?error=no_autorizado");
        exit();
    }

    $propiedad = $propCtrl->obtener_propiedad_por_uuid($uuid);
    
    if ($propiedad) {
        $id_propiedad = $propiedad['id_propiedad']; 
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
    <link rel="stylesheet" href="../public/css/formulario_propiedad.css?v=<?php echo time(); ?>">
</head>
<body>


<div class="container">
    <header class="page-header">
        <a href="../public/index.php" class="back-link-primary">← Volver al Panel</a>
        <h1><?php echo $tituloPagina; ?></h1>
    </header>

    <div class="tabs-nav">
        <button id="tab-btn-datos" class="tab-btn <?php echo ($step == 1) ? 'active' : ''; ?> clickable" 
                onclick="switchTab('datos')">
            1. Datos Básicos
        </button>
        <button id="tab-btn-comodidades" class="tab-btn <?php echo ($step == 2) ? 'active' : ''; ?> <?php echo ($esEdicion || $step == 2 || (isset($_GET['id']) && isset($_GET['new']))) ? 'clickable' : 'disabled'; ?>" 
                <?php echo ($esEdicion || $step == 2 || (isset($_GET['id']) && isset($_GET['new']))) ? "onclick=\"switchTab('comodidades')\"" : ""; ?>>
            2. Comodidades
        </button>
    </div>

    <section id="section-datos" class="card-form <?php echo ($step == 2) ? 'hidden' : ''; ?>">
        <form action="../actions/property_actions.php?action=<?php echo $esEdicion ? 'actualizar' : 'registrar'; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($propiedad): ?>
                <input type="hidden" name="uuid" value="<?php echo $propiedad['uuid']; ?>">
            <?php endif; ?>

            <label>Título del anuncio</label>
            <input type="text" name="titulo" required value="<?php echo $propiedad ? htmlspecialchars($propiedad['titulo']) : ''; ?>">

            <label>Descripción</label>
            <textarea name="descripcion" required><?php echo $propiedad ? htmlspecialchars($propiedad['descripcion']) : ''; ?></textarea>

            <div class="form-row">
                <div class="form-col"><label>Capacidad</label><input type="number" name="capacidad" min="1" required value="<?php echo $propiedad ? $propiedad['capacidad'] : ''; ?>"></div>
                <div class="form-col"><label>Precio/Noche (USD)</label><input type="number" name="precio" step="0.01" required value="<?php echo $propiedad ? $propiedad['precio_noche'] : ''; ?>"></div>
            </div>

            <label>Ubicación</label>
            <input type="text" name="ubicacion" required value="<?php echo $propiedad ? htmlspecialchars($propiedad['ubicacion']) : ''; ?>">

            <label>Imagen</label>
            <?php if($propiedad && !empty($propiedad['imagen_url'])): ?>
                <div class="current-image-preview">
                    <img src="../public/uploads/<?php echo $propiedad['imagen_url']; ?>" class="preview-image">
                    <span class="preview-text">Imagen actual. Sube otra para cambiarla.</span>
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
            <input type="hidden" name="uuid" value="<?php echo $propiedad['uuid'] ?? ''; ?>">
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

            <button type="submit" class="btn-main btn-main-dark">
                <?php echo ($esEdicion) ? "Actualizar Comodidades" : "Publicar Propiedad Ahora"; ?>
            </button>
        </form>
    </section>
</div>

<script>
    function switchTab(tab) {
        const secDatos = document.getElementById('section-datos');
        const secComodidades = document.getElementById('section-comodidades');
        const btnDatos = document.getElementById('tab-btn-datos');
        const btnComodidades = document.getElementById('tab-btn-comodidades');

        if (tab === 'datos') {
            secDatos.classList.remove('hidden');
            secComodidades.classList.add('hidden');
            btnDatos.classList.add('active');
            btnComodidades.classList.remove('active');
        } else {
            // Solo permitir ir a comodidades si es edición o si ya estamos en el paso 2 (o el botón es clickable)
            if (btnComodidades.classList.contains('disabled')) return;
            
            secDatos.classList.add('hidden');
            secComodidades.classList.remove('hidden');
            btnDatos.classList.remove('active');
            btnComodidades.classList.add('active');
        }
    }
</script>
</body>
</html>