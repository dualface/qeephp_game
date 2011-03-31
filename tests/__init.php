<?php

namespace tests;

use qeeplay\Autoload;
use qeeplay\Config;
use qeeplay\tools\ILogger;

if (defined('IN_QPLAY_TESTS')) return;

define('IN_QPLAY_TESTS', true);
define('ROOT_PATH', dirname(__DIR__));
define('PACKAGES_PATH', ROOT_PATH . '/packages');
define('QPLAY_SRC_PATH', PACKAGES_PATH . '/qeeplay');
define('TEST_SRC_PATH', __DIR__);

error_reporting(E_ALL | E_STRICT);

require_once QPLAY_SRC_PATH . '/__init.php';

Autoload::import(PACKAGES_PATH);
Autoload::import(TEST_SRC_PATH, '\\tests');

$logger_config = array(
    'filename' => __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'tests.log',
    'level' => ILogger::TRACE,
);
Config::set('logger.test', $logger_config);
date_default_timezone_set(Config::get(array('app.timezone', 'defaults.timezone')));

