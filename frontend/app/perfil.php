<?php
session_start();
header('X-Pod-Name: ' . gethostname());
if (!isset($_SESSION['token'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Novasoft</title>
    <link rel="stylesheet" href="estilosesion.css">
    <link rel="shortcut icon" href="img/undraw_male_avatar_323b.svg">
</head>
<body>
<header>
    <div class="contentheader">
        <img class="imagenh" src="img/data_maintenance_isometric.svg">
        <nav><ul class="menu">
            <li><a href="perfil.php">Inicio</a></li>
            <li><a href="lista_clientes.php">Clientes</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul></nav>
    </div>
</header>

<div class="formulario">
    <div class="user">
        <img src="img/undraw_male_avatar_323b.svg" alt="Avatar">
        <h1 style="color:white; margin-top:15px;">
            Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>
        </h1>
    </div>
    <div class="infouser" style="padding:40px;">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div style="color:green; background:#e0ffe0; padding:10px; margin-bottom:15px; border-radius:4px;">
                <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        <h2>Username: <?= htmlspecialchars($_SESSION['username']) ?></h2>
        <h2>Correo: <?= htmlspecialchars($_SESSION['correo']) ?></h2>
        <br>
        <a href="lista_clientes.php" class="btn" style="display:block; text-align:center; margin-bottom:10px; padding:8px;">
            Gestionar Clientes (CRUD)
        </a>
        <a href="logout.php" class="btn" style="display:block; text-align:center; padding:8px;">
            Cerrar sesión
        </a>
    </div>
</div>

<footer>
    <div class="conteninfo"><h1>Novasoft</h1><p>Todos los derechos reservados</p></div>
</footer>
</body>
</html>
