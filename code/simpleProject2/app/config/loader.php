<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */

$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir
    ]
)->register();

/*
$loader->registerDirs(
    [
        $config->application->modelsDir,
    ]
);
*/
//Register some ////namespaces


$loader->registerNamespaces(
    array(
       "Application\Models"    => __DIR__ . '/../models/',
       "Application\Controllers" => __DIR__ . '/../controllers/',
       "Application"         => __DIR__ . '/../../app/'
    )
);

/*
$loader->register////namespaces(
    array(
       "Application\Models"    => $config->application->controllersDir,
       "Application\Controllers" => $config->application->modelsDir
    )
);
*/
// register autoloader
$loader->register();