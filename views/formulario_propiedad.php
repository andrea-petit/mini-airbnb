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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>


<div class="container">
    <header class="page-header">
        <a href="../public/index.php" class="back-link-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Volver al Panel
        </a>
        <h1><?php echo $tituloPagina; ?></h1>
    </header>

    <div class="tabs-nav">
        <button id="tab-btn-datos" class="tab-btn <?php echo ($step == 1) ? 'active' : ''; ?> clickable" 
                onclick="switchTab('datos')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            1. Datos Básicos
        </button>
        <button id="tab-btn-comodidades" class="tab-btn <?php echo ($step == 2) ? 'active' : ''; ?> <?php echo ($esEdicion || $step == 2 || (isset($_GET['id']) && isset($_GET['new']))) ? 'clickable' : 'disabled'; ?>" 
                <?php echo ($esEdicion || $step == 2 || (isset($_GET['id']) && isset($_GET['new']))) ? "onclick=\"switchTab('comodidades')\"" : ""; ?>>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
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

            <label>Imagen de portada</label>
            <div class="upload-area" id="upload-container">
                <?php if($propiedad && !empty($propiedad['imagen_url'])): ?>
                    <div class="current-image-preview">
                        <img src="../public/uploads/<?php echo $propiedad['imagen_url']; ?>" class="preview-image" id="image-preview">
                        <div class="preview-info">
                            <span class="preview-text">Imagen actual</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="upload-placeholder" id="placeholder-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        <p>Haz clic para subir una imagen</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" id="foto-input" accept="image/*" <?php echo ($propiedad) ? '' : 'required'; ?> class="file-hidden">
            </div>

            <button type="submit" class="btn-main">
                <?php echo $esEdicion ? "Actualizar Información" : "Guardar y Continuar"; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>
    </section>

    <section id="section-comodidades" class="card-form <?php echo ($step == 1) ? 'hidden' : ''; ?>">
        <div class="section-header-v2">
            <h2>¿Qué ofrece tu alojamiento?</h2>
            <p>Selecciona todas las opciones que apliquen</p>
        </div>
        <form action="../actions/property_actions.php?action=actualizar_comodidades" method="POST">
            <input type="hidden" name="uuid" value="<?php echo $propiedad['uuid'] ?? ''; ?>">
            <?php if(isset($_GET['new'])): ?> <input type="hidden" name="is_new" value="1"> <?php endif; ?>

            <div class="amenities-grid">
                <?php foreach ($todasComodidades as $com): ?>
                    <label class="amenity-card">
                        <input type="checkbox" name="comodidades[]" value="<?php echo $com['id_comodidad']; ?>"
                            <?php echo in_array($com['id_comodidad'], $comodidadesSeleccionadas) ? 'checked' : ''; ?> class="checkbox-hidden">
                        <div class="amenity-content">
                            <span class="amenity-check-icon-v2"></span>
                            <span class="amenity-name"><?php echo htmlspecialchars($com['nombre']); ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-main btn-publish">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
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
            if (btnComodidades.classList.contains('disabled')) return;
            secDatos.classList.add('hidden');
            secComodidades.classList.remove('hidden');
            btnDatos.classList.remove('active');
            btnComodidades.classList.add('active');
        }
    }

    // Image Upload Interaction
    const uploadContainer = document.getElementById('upload-container');
    const fileInput = document.getElementById('foto-input');
    const imagePreview = document.getElementById('image-preview');
    const placeholder = document.getElementById('placeholder-box');

    if (uploadContainer && fileInput) {
        uploadContainer.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imagePreview) {
                        imagePreview.src = e.target.result;
                    } else if (placeholder) {
                        placeholder.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">`;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
</script>
</body>
</html>