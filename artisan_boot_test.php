<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "BOOTSTRAP OK\n";
} catch (Throwable $e) {
    echo get_class($e).": ".$e->getMessage()."\n";
    echo $e->getTraceAsString();
}
