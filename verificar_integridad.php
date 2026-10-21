<?php
require __DIR__ . '/paginas/modelo/conexion.php';

$sql = file_get_contents(__DIR__ . '/bd/verification.sql');
$sql = preg_replace('/^--.*$/m', '', $sql);
$bd = Conexion::conectar();

foreach (array_filter(array_map('trim', explode(';', $sql))) as $consulta) {
  echo str_repeat('=', 60) . "\n";
  foreach ($bd->query($consulta)->fetchAll() as $fila) {
    echo implode(' | ', $fila) . "\n";
  }
}
