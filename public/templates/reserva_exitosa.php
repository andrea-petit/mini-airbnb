<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Reserva Exitosa! | WindBnB</title>
    <link rel="stylesheet" href="/airbnb/public/css/templates.css">
</head>
<body>
    <div class="template-container">
        <div class="logo-container">
            <a href="/airbnb/public/index.php" class="brand-logo"><span class="brand-highlight">Wind</span>BnB</a>
        </div>
        
        <div class="status-icon success-icon">✨</div>
        
        <h1>¡Reserva confirmada!</h1>
        <p>Tu solicitud ha sido procesada con éxito. Puedes revisar los detalles de tu viaje en la sección "Mis Viajes".</p>
        
        <div style="display: flex; gap: 16px; justify-content: center;">
            <a href="/airbnb/public/index.php" class="btn-home">Explorar más</a>
            <a href="/airbnb/views/mis_reservas.php" class="btn-home" style="background-color: var(--success-color);">Ver mis viajes</a>
        </div>
    </div>
</body>
</html>