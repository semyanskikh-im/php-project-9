<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use DI\Container;
use Hexlet\Code\Repositories\CheckRepository;
use Valitron\Validator;
use Hexlet\Code\Connection;
use Hexlet\Code\Repositories\UrlRepository;
use Hexlet\Code\Checker;
use DiDom\Document;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
$dotenv->required(['DATABASE_URL']);

session_start();

$container = new Container();
$app = AppFactory::createFromContainer($container);

$dataBaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

$container->set(\PDO::class, function () use ($dataBaseUrl) {
    $connection = new Connection();
    return $connection->createPdo($dataBaseUrl);
});

$container->set('renderer', function () {

    $renderer = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
    $renderer->setLayout('layout.phtml');
    return $renderer;
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$container->set('router', function () use ($app) {
    return $app->getRouteCollector()->getRouteParser();
});

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (Request $request, Throwable $exception) use ($app) {
        $response = $app->getResponseFactory()->createResponse();
        return $this->get('renderer')->render($response->withStatus(404), '404.phtml');
    }
);

//обработчик стартовой страницы
$app->get('/', function (Request $request, Response $response) {

    $params = ['currentPage' => 'index'];

    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('index');

//обработчик страницы с таблицей со всеми url'ами
$app->get('/urls', function (Request $request, Response $response) {

    $urlRepo = $this->get(UrlRepository::class);
    $checkRepo = $this->get(CheckRepository::class);
    $urls = $urlRepo->findAllWithLastCheck($checkRepo);

    $messages = $this->get('flash')->getMessages();

    $params = ['urls' => $urls, 'flash' => $messages, 'currentPage' => 'urls'];

    return $this->get('renderer')->render($response, '/urls/index.phtml', $params);
})->setName('urls.index');

//добавляем или нет новую запись в таблицу с url'ами
$app->post('/urls', function (Request $request, Response $response) {

    $body = $request->getParsedBody();

    $body = match (true) {
        is_array($body) => $body,
        is_object($body) => (array)$body,
        is_string($body) => json_decode($body, true) ?? [],
        default => []
    };

    $v = new Validator($body);

    $v->rule('required', 'url.name')->message('URL не должен быть пустым!');
    $v->rule('lengthMax', 'url.name', 255)->message('URL не должен превышать 255 символов!');
    $v->rule('url', 'url.name')->message('Некорректный URL!');

    if (!$v->validate()) {
        $errors = $v->errors();
        $urlName = $body['url']['name'];
        $params = ['errors' => $errors, 'urlValue' => $urlName, 'currentPage' => 'index'];
        $response = $response->withStatus(422);
        return $this->get('renderer')->render($response, 'index.phtml', $params);
    }

    $urlName = strtolower($body['url']['name']);
    $parsedUrl = parse_url($urlName);
    $scheme = $parsedUrl['scheme'];
    $host = $parsedUrl['host'];
    $domain = "{$scheme}://{$host}";

    $urlRepo = $this->get(UrlRepository::class);

    $existingUrl = $urlRepo->findByName($domain);

    if ($existingUrl) {
        // URL уже существует - редирект на страницу этого url
        $id = $existingUrl->getId();

        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        // Если такого Url еще не существует - создаем новую запись в БД и редирект на страницу нового url

        $newUrl = $urlRepo->create(['name' => $domain]);

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

    $urlRepo = $this->get(UrlRepository::class);
    $url = $urlRepo->findById($urlId);

    //если в базе нет такого url, выводим 404
    if (is_null($url)) {
        throw new HttpNotFoundException($request);
    }

    $checkRepo = $this->get(CheckRepository::class);
    $checks = $checkRepo->getAllForUrlId($urlId);

    $messages = $this->get('flash')->getMessages();

    $params = [
        'id' => $urlId,
        'url' => $url,
        'checks' => $checks,
        'flash' => $messages,
        'currentPage' => 'urls'
    ];

    return $this->get('renderer')->render($response, '/urls/show.phtml', $params);
})->setName('urls.show');

//обработчик с seo-проверкой url
$app->post('/urls/{id:[0-9]+}/checks', function (Request $request, Response $response, array $args) {

    $urlId = $args['id'];

    $urlRepo = $this->get(UrlRepository::class);
    $url = $urlRepo->findById($urlId);

    if (is_null($url)) {
        throw new HttpNotFoundException($request);
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

    $h1 = (string) optional($document->first('h1'))->text();


    $titleElement = $document->first('title');
    $title = $titleElement ? trim(optional($titleElement)->text()) : '';


    $metaElement = $document->first('meta[name="description"]');


    $description = $metaElement?->getAttribute('content') ?? '';
    $description = trim($description);

    $data = [
        'url_id' => $urlId,
        'status_code' => $statusCode,
        'h1' => $h1,
        'title' => $title,
        'description' => $description
    ];

    $checkRepo = $this->get(CheckRepository::class);
    $checkRepo->create($data);

    $this->get('flash')->addMessage('success', 'Страница успешно проверена');

    return $response
        ->withHeader('Location', $this->get('router')->urlFor('urls.show', ['id' => $urlId]))
        ->withStatus(302);
});

// Обработчик для всех остальных маршрутов (404 ошибка)
$app->any('/{routes:.+}', function (Request $request) {
    throw new HttpNotFoundException($request);
});

$app->run();
