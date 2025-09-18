<?php

namespace Hexlet\Code;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\RequestException;

class Checker
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 5]);
    }

    public function checkUrl(?string $url): array
    {
        if ($url === null) {
            return [
                'success' => false
            ];
        }
        try {
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();
            $html = (string) $response->getBody();
            return [
                'success' => true,
                'statusCode' => $statusCode,
                'html' => $html
            ];
        } catch (ConnectException | ServerException $e) {
            return [
                'success' => false
            ];
        } catch (ClientException | TooManyRedirectsException $e) {
            $response = $e->getResponse();

            if (!$response) {
                return ['success' => false];
            }

            $statusCode = $response->getStatusCode();
            $html = (string) $response->getBody();
            return [
                'success' => true,
                'statusCode' => $statusCode,
                'html' => $html
            ];
        }
        // catch (RequestException $e) {
        //     // Общая обработка всех ошибок запроса. которые не отловились выше
        //     $response = $e->getResponse();

        //     if (!$response) {
        //         return ['success' => false];
        //     }

        //     $statusCode = $response->getStatusCode();
        //     $html = (string) $response->getBody();
        //     return [
        //         'success' => true,
        //         'statusCode' => $statusCode,
        //         'html' => $html
        //     ];
        // }
    }
}
