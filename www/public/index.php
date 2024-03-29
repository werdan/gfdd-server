<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('MODULE_PATH')
    || define('MODULE_PATH', realpath(dirname(__FILE__) . '/../application/modules'));
    
    

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? 
             getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(MODULE_PATH . '/default'),
    realpath(MODULE_PATH . '/api'),
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../library/zendframework'),
    realpath(APPLICATION_PATH . '/../library/doctrine'),
    realpath(APPLICATION_PATH . '/../library/gfdd'),
    realpath(APPLICATION_PATH . '/../library/gfdd/Longpoll'),
    realpath(APPLICATION_PATH . '/../library/gfdd/Utils'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()->run();
