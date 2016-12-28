<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

require __DIR__ . '/../config/config.inc.php';

// Configure logger infrastructure
if (isset($APP_CONF['logger'], $APP_CONF['logger']['logfile'], $APP_CONF['logger']['level'])) {
    // configure the Simple Logging Facade for PSR-3 loggers with a Monolog backend
    \bitExpert\Slf4PsrLog\LoggerFactory::registerFactoryCallback(function ($channel) use ($APP_CONF) {
        if (!\Monolog\Registry::hasLogger($channel)) {
            $handler = new \Monolog\Handler\StreamHandler($APP_CONF['logger']['logfile'], $APP_CONF['logger']['level']);
            $log = new \Monolog\Logger('name');
            $log->pushHandler($handler);
            \Monolog\Registry::addLogger($log, $channel);
            return $log;
        }

        return \Monolog\Registry::getInstance($channel);
    });
}

// Create Disco cache dir
if (isset($APP_CONF['di'], $APP_CONF['di']['cache']) and !is_dir($APP_CONF['di']['cache'])) {
    @mkdir($APP_CONF['di']['cache'], 0777, true);
}

// Configure and set up the BeanFactory instance
$config = new \bitExpert\Disco\BeanFactoryConfiguration($APP_CONF['di']['cache']);
$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory($APP_CONF['di']['config'], $APP_CONF, $config);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new \Zend\Diactoros\Response();

$beanFactory = \bitExpert\Disco\BeanFactoryRegistry::getInstance();

/** @var \bitExpert\Adrenaline\Adrenaline $app **/
$app = $beanFactory->get('webapp');
$app($request, $response);
