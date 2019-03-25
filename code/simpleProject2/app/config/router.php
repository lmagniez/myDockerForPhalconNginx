<?php

//$router = $di->getRouter();


use \Phalcon\Mvc\Router;

$router = new Phalcon\Mvc\Router(FALSE);


$router->setDefaults(array(
    'controller' => 'users',
    'action' => 'search'  
 ));

$router->notFound(
    [
        'controller' => 'error',
        'action'     => 'show404',
    ]
);


// Define your routes here

$router->handle();
