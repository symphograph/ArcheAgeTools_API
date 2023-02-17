<?php

namespace App\Env;

use App\Api;

class Config
{
    public const debugOnlyFolders = [
        'test',
        'api',
        'services',
        'transfer'
    ];
    public static function checkPermission(): void
    {
        if(Env::isDebugMode()){
            return;
        }
        foreach (self::debugOnlyFolders as $folder){
            if(str_starts_with($_SERVER['SCRIPT_NAME'], '/' . $folder . '/')){
                http_response_code(403);
                die();
            }
        }
    }

    public static function redirectFromWWW(): void
    {
        if (!preg_match('/www./', $_SERVER['SERVER_NAME'])){
            return;
        }
        $server_name = str_replace('www.', '', $_SERVER['SERVER_NAME']);
        $ref = $_SERVER["QUERY_STRING"];
        if ($ref != "") $ref = "?" . $ref;

        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://" . $server_name . "/" . $ref);
        exit();
    }

    public static function initApiSettings(): void
    {
        if (!str_starts_with($_SERVER['SCRIPT_NAME'], '/api/')) {
            return;
        }

        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'OPTIONS'])){
            Api::errorResponse('invalid method', 405);
        }

        self::checkOrigin();

        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true)['params'] ?? [];
        }
        if (empty($_POST['token'])  && empty($_SERVER['HTTP_AUTHORIZATION'])) {
            Api::errorResponse('emptyToken', 401);
        }

    }

    public static function checkOrigin(): void
    {
        if (empty($_SERVER['HTTP_ORIGIN'])){
            Api::errorResponse('emptyOrigin', 401);
        }

        $adr = 'https://' . Env::getFrontendDomain();
        if($_SERVER['HTTP_ORIGIN'] !== $adr){
            Api::errorResponse('Unknown domain', 401);
        }
    }

    public static function initDisplayErrors(): void
    {
        if (Env::isDebugMode()) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
    }
}