<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/property_controller.php';

$propCtrl = new PropertyController($pdo);
$propiedad = null;
$esEdicion = false;

if (isset($_GET['id'])) {
    $propiedad = $propCtrl->obtener_propiedad_por_id($_GET['id']);
    if ($propiedad) {
        $esEdicion = true;
    }
}

$tituloPagina = $esEdicion ? "Editar Propiedad" : "Publicar Nueva Propiedad";
$accionUrl = $esEdicion ? "actualizar" : "registrar";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tituloPagina; ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <h1><?php echo $tituloPagina; ?></h1>

    <form action="../actions/property_actions.php?action=<?php echo $accionUrl; ?>" method="POST" enctype="multipart/form-data">
        
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id_propiedad" value="<?php echo $propiedad['id_propiedad']; ?>">
        <?php endif; ?>

        <div>
            <label>Título del anuncio:</label>
            <input type="text" name="titulo" required 
                   value="<?php echo $esEdicion ? htmlspecialchars($propiedad['titulo']) : ''; ?>">
        </div>

        <div>
            <label>Descripción:</label>
            <textarea name="descripcion" required><?php echo $esEdicion ? htmlspecialchars($propiedad['descripcion']) : ''; ?></textarea>
        </div>

        <div>
            <label for="capacidad">Capacidad:</label>
            <input type="number" name="capacidad" min="1" required 
                   value="<?php echo $esEdicion ? $propiedad['capacidad'] : ''; ?>">
        </div>

        <div>
            <label>Precio por noche (USD):</label>
            <input type="number" name="precio" step="0.01" required 
                   value="<?php echo $esEdicion ? $propiedad['precio_noche'] : ''; ?>">
        </div>

        <div>
            <label>Ubicación:</label>
            <input type="text" name="ubicacion" required 
                   value="<?php echo $esEdicion ? htmlspecialchars($propiedad['ubicacion']) : ''; ?>">
        </div>

        <div>
            <label>Foto de la propiedad:</label>
            <?php if ($esEdicion && !empty($propiedad['imagen'])): ?>
                <div style="margin-bottom: 10px;">
                    <p>Imagen actual:</p>
                    <img src="../public/uploads/<?php echo $propiedad['imagen']; ?>" width="150">
                </div>
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*" <?php echo $esEdicion ? '' : 'required'; ?>>
            <small>Formatos permitidos: JPG, PNG, WEBP.</small>
        </div>

        <button type="submit">
            <?php echo $esEdicion ? "Guardar Cambios" : "Publicar Ahora"; ?>
        </button>
        
        <a href="../public/index.php">Cancelar</a>
    </form>
</body>
</html>