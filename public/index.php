<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Valitron\Validator;
use Hexlet\Code\Connection;
use Hexlet\Code\Url;

require __DIR__ . '/../vendor/autoload.php';

//определяем среду окружения 
$isLocalEnvironment = file_exists(__DIR__ . '/../.env');

//если есть файл .env загружаем переменные из него, если нет автоматом загрузится с рендера
if ($isLocalEnvironment) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    $dotenv->required(['DATABASE_URL']);
}

$dataBaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

session_start();

$container = new Container();
$container->set('renderer', function () {

    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$container->set('PDO', function () use ($dataBaseUrl) {
    $pdo = new Connection();
    return $pdo->createPdo($dataBaseUrl);
});

// инициализируем создание таблиц
$sqlFilePath = implode('/', [dirname(__DIR__), 'database.sql']);
$initSql = file_get_contents($sqlFilePath);
$container->get('PDO')->exec($initSql);

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

//обработчик стартовой страницы
$app->get('/', function (Request $request, Response $response) {

    return $this->get('renderer')->render($response, 'index.phtml');
})->setName('/');

//обработчик страницы с таблицей со всеми url'ами
$app->get('/urls', function (Request $request, Response $response) {


    return $this->get('renderer')->render($response, '/urls/urls.phtml');
})->setName('/urls');


$app->post('/urls', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $urlName = $body['url']['name'];
    //print_r($urlName);

    //здесь происходит валидация
    $v = new Validator(['url[name]' => $urlName]);

    $v->rule('required', 'url[name]')->message('URL не должен быть пустым!');
    $v->rule('lengthMax', 'url[name]', 255)->message('URL не должен превышать 255 символов!');
    $v->rule('regex', 'url[name]', '/^(https?:\/\/)/')->message('Некорректный URL!');

    if (!$v->validate()) {
        $errors = $v->errors();

        var_dump($errors);

        $params = ['errors' => $errors, 'urlValue' => $urlName];
        $response = $response->withStatus(422);
        return $this->get('renderer')->render($response, 'index.phtml', $params);
    };

    // здесь будет логика, если данные валидные и такого url в таблице нет. создание новой записи в таблице
    $pdo = $this->get('PDO');

    $stmt = $pdo->prepare("SELECT id FROM urls WHERE name = ?");
    $stmt->execute([$urlName]);
    $existingUrl = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUrl) {
        // URL уже существует - добавляем flash сообщение и редиректим
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        return $response->withHeader('Location', "/urls/{$existingUrl['id']}")->withStatus(302);
    }

    // Создаем новую запись
    $url = new Url();
    $createdAt = $url->getCreatedAt();
    $stmt = $pdo->prepare("INSERT INTO urls (name, created_at) VALUES (?, ?)");
    $stmt->execute([$urlName, $createdAt]);

    $newUrlId = $pdo->lastInsertId();

    // Добавляем flash сообщение и редиректим на страницу нового URL
    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    return $response->withHeader('Location', "/urls/{$newUrlId}")->withStatus(302);
});

$app->run();
