<?php
require __DIR__ . '/../vendor/autoload.php';
use Illuminate\Http\Request;
$c = new App\Http\Controllers\PaymentController();
$r = Request::create('/payments/create', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$v = $c->create($r);
if (is_object($v)) {
    echo get_class($v) . "\n";
    try {
        $html = $v->render();
        echo "Length: " . strlen($html) . "\n";
    } catch (Exception $e) {
        echo 'render error: ' . $e->getMessage() . "\n";
    }
} else {
    echo 'Not object';
}
