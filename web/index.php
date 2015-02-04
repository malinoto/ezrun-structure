<?php

$namespaces = require_once(dirname(__FILE__) . '/../config/namespaces.php');

require_once(dirname(__FILE__) . '/../config/config_parser.php');

use \Core\Render;
use \Core\Router;
use \Core\TwigGlobalVariables;

$configParser = new ConfigParser();

//template engine
$loader = new Twig_Loader_Filesystem(views_path);
$twig   = new Twig_Environment($loader, array(
    'cache' => twig_cache ? site_cachedir : false,
    'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());
$twig->addExtension(new TwigGlobalVariables($configParser->getParameters()));

//router
$router = new Router($configParser->getRouting());

//render
$render = new Render($router, $twig);
$render->show();