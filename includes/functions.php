<?php

use App\Env\Env;
use Symphograph\Bicycle\DB;

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

function printr($var): void
{
    if(!Env::isDebugMode())
        return;
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function qwe(string $sql, array $args = null): bool|PDOStatement
{
    global $DB;

    if(!isset($DB)){
        $DB = new DB('DL8t');
    }

    return $DB->qwe($sql,$args);
}

function qwe2(string $sql, array $args = null) : bool|PDOStatement
{
    global $DB2;

    if(!isset($DB2)){
        $DB2 = new DB(1);
    }

    return $DB2->qwe($sql,$args);
}