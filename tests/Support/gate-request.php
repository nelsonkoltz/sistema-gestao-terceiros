<?php

// Worker used only by the isolated MySQL concurrency test.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!$app->environment('testing') || config('database.connections.mysql.database') !== 'terceiroscr_test') {
    throw new RuntimeException('Este worker exige o banco isolado terceiroscr_test.');
}
Carbon\Carbon::setTestNow('2026-09-29 10:00:00');
Illuminate\Support\Facades\Auth::loginUsingId((int) $argv[1]);
file_put_contents($argv[3], 'ready');
$deadline = microtime(true) + 15;
while (!is_file($argv[4])) {
    if (microtime(true) > $deadline) throw new RuntimeException('Tempo limite da barreira de teste.');
    usleep(10000);
}
$request = Illuminate\Http\Request::create('/guarita/funcionarios/'.(int) $argv[2].'/entrada', 'POST');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
echo json_encode(['status' => $response->getStatusCode(), 'success' => session('success'), 'error' => session('error')]);
$kernel->terminate($request, $response);
