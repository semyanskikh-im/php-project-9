<?php

// $sqlFilePath = implode('/', [dirname(__DIR__), 'database.sql']);
// var_dump($sqlFilePath);
// $initSql = file_get_contents($sqlFilePath);
// var_dump($initSql);

//print_r(dirname(__DIR__));


require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Arr;

$arr = ['name' => 'mail.ru', 'created_at' => '25.05.25'];
$result = Arr::only($arr, ['name', 'created_at']);
var_dump($result);