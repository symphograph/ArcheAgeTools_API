<?php

use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Logs\AccessLog;

Config::redirectFromWWW();
Config::regHandlers();
Config::initDisplayErrors();
Config::checkPermission();
Config::initApiSettings();
AccessLog::writeToLog();
