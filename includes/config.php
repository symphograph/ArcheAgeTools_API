<?php

use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\Handler;
use Symphograph\Bicycle\Logs\AccessLog;

Config::redirectFromWWW();
Handler::regHandlers();
Config::initDisplayErrors();
Config::checkPermission();
Config::postHandler();
\App\Env\Config::initEndPoints();
AccessLog::writeToLog();