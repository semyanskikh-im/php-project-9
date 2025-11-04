<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hexlet\Code\Connection;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
$dotenv->required(['DATABASE_URL']);

$dataBaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

try {
    //Подключение к базе данных
    $connection = new Connection();
    $pdo = $connection->createPdo($dataBaseUrl);

    //Чтение файла миграций
    $sqlFilePath = __DIR__ . '/../database.sql';

    if (!file_exists($sqlFilePath)) {
        throw new Exception("Файл миграций не найден: " . $sqlFilePath);
    }

    $initSql = file_get_contents($sqlFilePath);
    if ($initSql === false) {
        throw new Exception("Не удалось прочитать файл миграций: " . $sqlFilePath);
    }

    //Выполнение миграций
    $pdo->exec($initSql);
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
