<?php
use App\Env\Config;

Config::redirectFromWWW();
Config::checkPermission();
Config::initApiSettings();