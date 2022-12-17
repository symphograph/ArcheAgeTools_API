<?php
if(preg_match('/www./',$_SERVER['SERVER_NAME']))
{
	$server_name = str_replace('www.','',$_SERVER['SERVER_NAME']);
	$ref=$_SERVER["QUERY_STRING"];
	if ($ref!="") $ref="?".$ref;
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://".$server_name."/".$ref);
	exit();
}
use Symphograph\Bicycle\DB;

$env = require dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/env.php';
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/functions.php';


if($env->myip)
{
	ini_set('display_errors',1);
	error_reporting(E_ALL);
    $env->debug = true;

}
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/vendor/autoload.php';
spl_autoload_register(function ($className) {
    $fileName = str_replace('\\', '/', $className) . '.php';
    $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/classes/' . $fileName;
    if(file_exists($file)){
        require_once $file;
    }
});


if(str_starts_with($_SERVER['SCRIPT_NAME'], '/api/')) {

    if(!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'OPTIONS']))
        die();

    \User\Sess::checkOrigin();

    if(empty($_POST)) {
        $_POST = json_decode(file_get_contents('php://input'), true)['params'] ?? [];
    }
    if(empty($_POST['token'])){
        die('emptyToken');
    }

    if($env->debug) {

    }

    //cors();
}

if(str_starts_with($_SERVER['SCRIPT_NAME'],'/test/') || str_starts_with($_SERVER['SCRIPT_NAME'],'/api/')){
    if(!$env->myip){
        die('permis');
    }
}

//------------------------------------------------------------------

function qwe(string $sql, array $args = null): bool|PDOStatement
{
    global $DB, $env;

    if(!isset($DB)){
        $DB = new DB();
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

