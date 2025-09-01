<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Hexlet\Code\CheckRepo;
use Valitron\Validator;
use Hexlet\Code\Connection;
use Hexlet\Code\Url;
use Hexlet\Code\UrlRepo;


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
$pdo = $container->get('PDO');
$pdo->exec($initSql);

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

//обработчик стартовой страницы
$app->get('/', function (Request $request, Response $response) {

    return $this->get('renderer')->render($response, 'index.phtml');
})->setName('/');

//обработчик страницы с таблицей со всеми url'ами
$app->get('/urls', function (Request $request, Response $response) use ($pdo) {

    $sql = "SELECT
                urls.id,
                urls.name,
                c.status_code,
                c.created_at
            FROM urls
            LEFT JOIN (
                SELECT DISTINCT ON (url_id) *
                FROM checks
                ORDER BY url_id, created_at DESC
                ) c 
                ON urls.id = c.url_id
        ORDER BY urls.id ASC 
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $messages = $this->get('flash')->getMessages();

    $params = ['rows' => $rows, 'flash' => $messages];

    return $this->get('renderer')->render($response, '/urls/urls.phtml', $params);
})->setName('urls');


$app->post('/urls', function (Request $request, Response $response) use ($pdo) {
    $body = $request->getParsedBody();
    $urlName = $body['url']['name'];

    //здесь происходит валидация
    $v = new Validator(['url[name]' => $urlName]);

    $v->rule('required', 'url[name]')->message('URL не должен быть пустым!');
    $v->rule('lengthMax', 'url[name]', 255)->message('URL не должен превышать 255 символов!');
    $v->rule('regex', 'url[name]', '/^(https?:\/\/)/')->message('Некорректный URL!');

    if (!$v->validate()) {
        $errors = $v->errors();

        $params = ['errors' => $errors, 'urlValue' => $urlName];
        $response = $response->withStatus(422);
        return $this->get('renderer')->render($response, 'index.phtml', $params);
    };

    // здесь логика, если данные валидные и такого url в таблице нет. создание новой записи в таблице

    $urlRepo = new UrlRepo($pdo);

    $existingUrl = $urlRepo->findByName($urlName);

    if ($existingUrl) {
        // URL уже существует - редирект на страницу этого url 
        $id = $existingUrl->getId();

        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        // Если такого Url еще не существует - создаем новую запись в БД и редирект на страницу нового url

        $newUrl = $urlRepo->create($urlName);

        $id = $newUrl->getId();

        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }

    return $response->withHeader('Location', "/urls/{$id}")
        ->withStatus(302);
});

$app->get('/urls/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    $urlId = $args['id'];

    $urlRepo = new UrlRepo($pdo);

    $url = $urlRepo->findById($urlId);

    if (is_null($url)) {
        $response->getBody()->write('Page not found');
        return $response->withStatus(404);
    }

    $checkRepo = new CheckRepo($pdo);
    $checks = $checkRepo->getAllForUrlId($urlId);

    $messages = $this->get('flash')->getMessages();

    $params = [
        'id' => $urlId,
        'url' => $url,
        'checks' => $checks,
        'flash' => $messages
    ];

    return $this->get('renderer')->render($response, '/urls/show.phtml', $params);
})->setName('urls.show');

$app->post('/urls/{id}/checks', function (Request $request, Response $response, array $args) use ($pdo) {
    $urlId = $args['id'];

    $checkRepo = new CheckRepo($pdo);
    $checkRepo->create($urlId);

    $this->get('flash')->addMessage('success', 'Страница успешно проверена');

    return $response->withHeader('Location', "/urls/{$urlId}")
        ->withStatus(302);
});

$app->run();
