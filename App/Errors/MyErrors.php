<?php

namespace App\Errors;

use Error;
use Exception;

class MyErrors extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
        self::writeLog();
    }

    protected function writeLog(): void
    {
        $logText = self::prepLog();
        $log = fopen(dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/errors.txt', 'a+');
        fwrite($log, "$logText\r\n");
        fclose($log);
    }

    private function prepLog(): string
    {
        $trace = self::getTraceAsString();
        return date('Y-m-d H:i:s') . "\t {$this->getMessage()}\r\n$trace\r\n";
    }
}