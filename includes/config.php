<?php

use App\Api;
use App\Env\Config;
use App\Errors\MyErrors;

try {
    Config::redirectFromWWW();
    Config::initDisplayErrors();
    Config::checkPermission();
    Config::initApiSettings();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}
