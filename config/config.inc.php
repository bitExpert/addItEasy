<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$APP_CONF = [];

// Application configuration
$APP_CONF['app']['cachedir'] = __DIR__ . '/../cache/';
$APP_CONF['app']['logfile'] = $APP_CONF['app']['cachedir'] . '/addITeasy.log';
$APP_CONF['app']['datadir'] = '';
$APP_CONF['app']['assetdir'] = '';
$APP_CONF['app']['templatedir'] = '';
$APP_CONF['app']['exportdir'] = '';
$APP_CONF['app']['defaultpage'] = '';

// merge in the project specific settings if defined
if (isset($EASY_CONF, $EASY_CONF['app'])) {
    $APP_CONF['app'] = $EASY_CONF['app'];
}

// Disco configuration
$APP_CONF['di'] = [];
$APP_CONF['di']['config'] = \bitExpert\AddItEasy\Config::class;
$APP_CONF['di']['cache'] = $APP_CONF['app']['cachedir'].'/di/';
$APP_CONF['di']['devMode'] = false;

// Logger configuration
$APP_CONF['logger'] = [];
$APP_CONF['logger']['logfile'] = $APP_CONF['app']['logfile'];
$APP_CONF['logger']['level'] = \Monolog\Logger::DEBUG;

// Site configuration (these variables will get passed to the twig template)
$APP_CONF['site'][] = isset($EASY_CONF['site']) ? $EASY_CONF['site'] : [];
