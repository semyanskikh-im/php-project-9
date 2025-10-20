<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Hexlet\Code\Repositories\CheckRepo;
use Hexlet\Code\UrlValidator;
use Hexlet\Code\Connection;
use Hexlet\Code\Repositories\UrlRepo;
use Hexlet\Code\Checker;
use DiDom\Document;

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
$app = AppFactory::createFromContainer($container);

$container->set(\PDO::class, function () use ($dataBaseUrl) {
    $pdo = new Connection();
    return $pdo->createPdo($dataBaseUrl);
});

$container->set(UrlRepo::class, function (Container $container) {
    $pdo = $container->get(\PDO::class);
    return new UrlRepo($pdo);
});

$container->set(CheckRepo::class, function (Container $container) {
    $pdo = $container->get(\PDO::class);
    return new CheckRepo($pdo);
});

$container->set('renderer', function () {

    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$container->set('router', function () use ($app) {
    return $app->getRouteCollector()->getRouteParser();
});

$app->addErrorMiddleware(true, true, true);

//обработчик стартовой страницы
$app->get('/', function (Request $request, Response $response) {

    return $this->get('renderer')->render($response, 'index.phtml');
})->setName('index');

//обработчик страницы с таблицей со всеми url'ами
$app->get('/urls', function (Request $request, Response $response) {

    $urlRepo = $this->get(UrlRepo::class);
    $urls = $urlRepo->findAllWithLastCheck();

    $messages = $this->get('flash')->getMessages();

    $params = ['urls' => $urls, 'flash' => $messages];

    return $this->get('renderer')->render($response, '/urls/index.phtml', $params);
})->setName('urls.index');

//добавляем или нет новую запись в таблицу с url'ами
$app->post('/urls', function (Request $request, Response $response) {
    $urlRepo = $this->get(UrlRepo::class);
    $body = $request->getParsedBody();
    $urlName = '';

    if (is_array($body)) {
        $urlName = strtolower($body['url']['name']) ?? '';
    }
    //здесь происходит валидация
    $validationResult = UrlValidator::validate(['url[name]' => $urlName]);

    if (!$validationResult['success']) {
        $errors = $validationResult['errors'];
        $params = ['errors' => $errors, 'urlValue' => $urlName];
        $response = $response->withStatus(422);
        return $this->get('renderer')->render($response, 'index.phtml', $params);
    };

    $domain = UrlValidator::extractDomain($urlName);

    $existingUrl = $urlRepo->findByName($domain);

    if ($existingUrl) {
        // URL уже существует - редирект на страницу этого url
        $id = $existingUrl->getId();

        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        // Если такого Url еще не существует - создаем новую запись в БД и редирект на страницу нового url

        $newUrl = $urlRepo->create($domain);

        $id = $newUrl->getId();

        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }

    return $response
        ->withHeader('Location', $this->get('router')->urlFor('urls.show', ['id' => $id]))
        ->withStatus(302);
});

//вывод страницы конкретного url
$app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

    $urlId = $args['id'];

    $urlRepo = $this->get(UrlRepo::class);
    $url = $urlRepo->findById($urlId);

    //если в базе нет такого url, выводим 404
    if (is_null($url)) {
        return $this->get('renderer')->render($response->withStatus(404), '404.phtml');
    }

    $checkRepo = $this->get(CheckRepo::class);
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

//обработчик с seo-проверкой url
$app->post('/urls/{id}/checks', function (Request $request, Response $response, array $args) {

    $urlId = $args['id'];

    $urlRepo = $this->get(UrlRepo::class);
    $url = $urlRepo->findById($urlId);

    if (is_null($url)) {
        return $this->get('renderer')->render($response->withStatus(404), '404.phtml');
    }

    $urlName = (string) $url->getUrlName();

    $checker = new Checker();
    $result = $checker->checkUrl($urlName);

    if (!$result['success']) {
        $this->get('flash')->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
        return $response
            ->withHeader('Location', $this->get('router')->urlFor('urls.show', ['id' => $urlId]))
            ->withStatus(302);
    }

    $statusCode = $result['statusCode'];

    //парсинг DiDOM

    $html = $result['html'];

    $document = new Document($html);

    $h1Element = $document->first('h1');
    $h1 = $h1Element ? trim(optional($h1Element)->text()) : '';

    $titleElement = $document->first('title');
    $title = $titleElement ? trim(optional($titleElement)->text()) : '';


    $metaElement = $document->first('meta[name="description"]');


    $description = $metaElement?->getAttribute('content') ?? '';
    $description = trim($description);

    $data = [
        'h1' => $h1,
        'title' => $title,
        'description' => $description
    ];

    $checkRepo = $this->get(CheckRepo::class);
    $checkRepo->create($urlId, $statusCode, $data);

    $this->get('flash')->addMessage('success', 'Страница успешно проверена');

    return $response
        ->withHeader('Location', $this->get('router')->urlFor('urls.show', ['id' => $urlId]))
        ->withStatus(302);
});

// Обработчик для всех остальных маршрутов (404 ошибка)
$app->any('/{routes:.+}', function (Request $request, Response $response) {
    return $this->get('renderer')->render($response->withStatus(404), '404.phtml');
});

$app->run();
