<?php

use Symfony\Component\VarDumper\VarDumper;
use Symphograph\Bicycle\Env\Server\ServerEnvCli;
use Symphograph\Bicycle\Env\Server\ServerEnvHttp;
use Symphograph\Bicycle\Env\Server\ServerEnvITF;
use Symphograph\Bicycle\PDO\DB;
use JetBrains\PhpStorm\Language;
use Symphograph\Bicycle\Env\Env;

/*
function cors() {

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}
*/

function printr($var): void
{
    if(!Env::isDebugMode())
        return;
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function qwe(#[Language("SQL")] string $sql, array $args = [], string $connectName = 'default'): false|PDOStatement
{
    return DB::qwe($sql, $args, $connectName);
}

function getRoot(): string
{
    return dirname(__DIR__);
}

function getServerEnvClass(): ServerEnvITF
{
    global $ServerEnv;
    if (isset($ServerEnv)) {
        return $ServerEnv;
    }
    if (PHP_SAPI === 'cli') {
        $ServerEnv = new ServerEnvCli();
    } else {
        $ServerEnv = new ServerEnvHttp();
    }
    return $ServerEnv;
}

function vd($data, ?string $label = null): void
{
    if (!Env::isDebugMode()) return;
    VarDumper::dump($data, $label);
}