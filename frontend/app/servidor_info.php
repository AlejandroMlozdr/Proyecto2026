<?php
$hostname  = gethostname();
$server_ip = $_SERVER['SERVER_ADDR'] ?? 'N/A';
$replica   = substr($hostname, 0, 12);
$colors    = ['#e74c3c','#2ecc71','#3498db','#9b59b6','#f39c12','#1abc9c'];
$color     = $colors[abs(crc32($hostname)) % count($colors)];
?>
<div style="position:fixed;bottom:0;left:0;right:0;background:<?= $color ?>;color:white;
    text-align:center;padding:8px 16px;font-family:monospace;font-size:14px;
    font-weight:bold;z-index:9999;box-shadow:0 -2px 8px rgba(0,0,0,0.3);">
    🖥️ Servidor: <strong><?= htmlspecialchars($replica) ?></strong>
    &nbsp;|&nbsp; IP: <strong><?= htmlspecialchars($server_ip) ?></strong>
    &nbsp;|&nbsp; ⚡ Réplica activa
</div>
<div style="height:45px;"></div>
