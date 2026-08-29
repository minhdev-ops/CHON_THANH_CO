<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$banners = App\Models\Banner::all();
echo $banners->toJson(JSON_PRETTY_PRINT);
