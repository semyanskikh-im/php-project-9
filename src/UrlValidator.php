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
        $v->rule('regex', 'url[name]', '/^https?:\/\/[^A-Z]+/')->message('Некорректный URL!');

        if (!$v->validate()) {
            $errors = $v->errors();
            return ['success' => false, 'errors' => $errors];
        }

        return ['success' => true, 'errors' => []];
    }
}
