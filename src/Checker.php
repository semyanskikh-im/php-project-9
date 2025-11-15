<?php

namespace Hexlet\Code;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class Checker
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 5]);
    }

    public function checkUrl(string $url): array
    {
        try {
            $response = $this->client->request('GET', $url);
        } catch (ConnectException $e) {
            return [
                'success' => false
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();

            if (!$response) {
                return ['success' => false];
            }
        }

        $statusCode = $response->getStatusCode();
        $html = (string) $response->getBody();
        return [
            'success' => true,
            'statusCode' => $statusCode,
            'html' => $html
        ];
    }
}
