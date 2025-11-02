<?php

namespace Hexlet\Code;

use Valitron\Validator;

class UrlValidator
{
    public static function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', 'url[name]')->message('URL не должен быть пустым!');
        $v->rule('lengthMax', 'url[name]', 255)->message('URL не должен превышать 255 символов!');
        $v->rule('url', 'url[name]')->message('Некорректный URL!');

        if (!$v->validate()) {
            $errors = $v->errors();
            $firstError = $errors['url[name]'][0] ?? '';

            return [
                'success' => false,
                'errors' => ['url[name]' => [$firstError]]
            ];
        }

        return ['success' => true, 'errors' => []];
    }

    public static function extractDomain(string $urlName): string
    {
        $parsedUrl = parse_url($urlName);
        if (!$parsedUrl) {
            return '';
        }

        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];

        return "{$scheme}://{$host}";
    }
}
