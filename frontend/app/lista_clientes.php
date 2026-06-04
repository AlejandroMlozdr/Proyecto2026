<?php
session_start();
header('X-Pod-Name: ' . gethostname());
if (!isset($_SESSION['token'])) { header("Location: index.php"); exit; }
require_once 'config/services.php';

$resp    = api_request('GET', CLIENTS_SERVICE . '/clientes', [], $_SESSION['token']);
$clientes = $resp['code'] === 200 ? $resp['body'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes | Novasoft</title>
    <link rel="stylesheet" href="estiloIndex.css">
    <link rel="shortcut icon" href="img/undraw_male_avatar_323b.svg">
    <style>
        .tabla-contenedor { max-width:1000px; margin:40px auto; background:whitesmoke; border-radius:4px; padding:40px; box-shadow:3px 17px 21px -6px rgba(0,0,0,0.75); }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px 12px; border:1px solid #ccc; text-align:left; }
        th { background:#3A4B90; color:white; }
        tr:nth-child(even) { background:#f0f0f0; }
        .acciones a { margin-right:8px; color:#3A4B90; text-decoration:none; font-weight:bold; }
        .acciones a:hover { text-decoration:underline; }
        .flash-ok  { color:green; background:#e0ffe0; padding:10px; margin-bottom:15px; border-radius:4px; }
        .flash-err { color:red;   background:#ffe0e0; padding:10px; margin-bottom:15px; border-radius:4px; }
    </style>
</head>
<body>
<header>
    <div class="contentheader">
        <img class="imagenh" src="img/data_maintenance_isometric.svg">
        <nav><ul class="menu">
            <li><a href="perfil.php">Mi Perfil</a></li>
            <li><a href="crear_cliente.php">+ Nuevo</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul></nav>
    </div>
</header>

<div class="tabla-contenedor">
    <h2>Clientes registrados</h2>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="flash-ok"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="flash-err"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <a href="crear_cliente.php" class="btn">+ Nuevo Cliente</a>

    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Nombre</th>
            <th>Correo</th><th>Celular</th><th>Acciones</th>
        </tr>
        <?php foreach ($clientes as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['correo']) ?></td>
            <td><?= htmlspecialchars($row['celular']) ?></td>
            <td class="acciones">
                <a href="editar_cliente.php?id=<?= $row['id'] ?>">✏️ Editar</a>
                <a href="eliminar_cliente.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('¿Eliminar este cliente?')">🗑 Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="perfil.php" class="btn">← Volver al perfil</a>
</div>

<footer><div class="conteninfo"><h1>Novasoft</h1><p>Todos los derechos reservados</p></div></footer>
</body>
</html>
