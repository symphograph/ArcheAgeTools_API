<?php
use App\Env\Config;

Config::redirectFromWWW();
Config::initDisplayErrors();
Config::checkPermission();
Config::initApiSettings();