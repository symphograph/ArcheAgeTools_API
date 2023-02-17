<?php

namespace App\Errors;

use App\Env\Env;
use Error;
use Exception;

class MyErrors extends Exception
{
    protected string $type = 'Err';
    protected bool $loggable = true;
    public function __construct(string $message, private string $pubMsg = '')
    {
        parent::__construct($message);
        //self::writeLog();
        if($this->loggable){
            self::writeJsonLog();
        }

    }

    protected function writeLog(): void
    {
        $logText = self::prepLog();
        $log = fopen(dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/errors.log', 'a+');
        fwrite($log, "$logText\r\n");
        fclose($log);
    }

    private function writeJsonLog(): void
    {
        //$logText = self::prepLog();
        $data = [
            'datetime' => date('Y-m-d H:i:s'),
            'type' => $this->type,
            'level' => 'error',
            'msg' => $this->getMessage(),
            'trace' => self::prepTrace()
        ];
        $data = json_encode($data);
        $log = fopen(dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/errors.log', 'a+');
        fwrite($log, "$data\r\n");
        fclose($log);
    }

    private function prepTrace(): string
    {
        if(!count(self::getTrace())){
            return $_SERVER['SCRIPT_NAME'] . "({$this->getLine()})";
        }
        return self::getTraceAsString();
    }

    private function prepLog(): string
    {
        $trace = self::getTraceAsString();
        if(!count(self::getTrace())){
            $trace = $_SERVER['SCRIPT_NAME'] . "({$this->getLine()})";
        }

        return '[' . date('Y-m-d H:i:s') . '] [error]' . "\t {$this->getMessage()}\r\n$trace\r\n";
    }

    public function getPubMsg(): string
    {
        return $this->pubMsg;
    }

    public function getResponseMsg(): string
    {
        return Env::isDebugMode() ? $this->getMessage() : $this->getPubMsg();
    }
}