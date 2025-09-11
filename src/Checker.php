<?php

namespace Hexlet\Code;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;

class Checker
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 5]);
    }

    public function checkUrl($url): array
    {
        try {
            $response = $this->client->request('GET', $url);
            return [
                'success' => true,
                'statusCode' => $response->getStatusCode()
            ];
        } catch (ConnectException | ServerException $e) {
            return [
                'success' => false
            ];
        } catch (ClientException | TooManyRedirectsException $e) {
            return [
                'success' => true,
                'statusCode' => $e->getResponse()->getStatusCode()
            ];
        }
    }

    public function getHTML($url): string
    {
        $response = $this->client->get($url);
        $html = (string) $response->getBody();
        return $html;
    }
}
