<?php
// Ruta base del sitio: vacía si el proyecto está en la raíz del servidor.
// Si se aloja en una subcarpeta (por ejemplo /appventas), se escribe aquí.
$base   = $base ?? '';
$titulo = $titulo ?? 'AppVentas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?> | AppVentas</title>
  <link rel="stylesheet" href="<?= $base ?>/css/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="<?= $base ?>/css/fontawesome/css/all.min.css">
</head>
<body>
<?php require __DIR__ . '/menu.php'; ?>
<main class="container py-4">
