<?php

namespace App;

class Debug
{
    public static function header(string $title = 'test'): string
    {
        return <<<HTML
            <!doctype html>
            <html lang="ru">
            <head>
                <meta charset="utf-8">
                <title>$title</title>
            </head>
            <body style="color: white; background-color: #262525">
        HTML;

    }

    public static function footer(float|int $start): string
    {
        return
            '<br>Время выполнения скрипта: '
            . round(microtime(true) - $start, 4)
            . ' сек.</body>';

    }
}