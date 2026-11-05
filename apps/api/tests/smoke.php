<?php
declare(strict_types=1);
require dirname(__DIR__). '/src/bootstrap.php';
if(!class_exists(TalentHub\Application::class)){
    fwrite(STDERR, 'Error: No se pudo cargar la clase TalentHub\Application' . PHP_EOL);
    exit(1);
}
echo 'SMOKE PHP OK\n';
?>