<?php

namespace Hexlet\Code;

use PDO;
use PDOException;

class Connection
{
    public function createPdo(string $url): PDO
    {
        $params = parse_url($url);

        $dns = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $params['host'],
            $params['port'] ?? 5432,
            trim($params['path'], '/')
        );
        $user = $params['user'] ?? '';
        $pass = $params['pass'] ?? '';

        $pdo = new PDO($dns, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
