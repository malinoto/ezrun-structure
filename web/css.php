<?php

$namespaces = require_once(dirname(__FILE__) . '/../config/namespaces.php');

require_once(dirname(__FILE__) . '/../config/config_parser.php');

use Ezrun\Core\TwigCustomExtension;

$configParser = new ConfigParser();

//template engine
$loader = new Twig_Loader_Filesystem(css_path);
$twig   = new Twig_Environment($loader, array(
    'cache' => twig_cache ? site_cachedir : false,
));
$twig->addExtension(new TwigCustomExtension($configParser->getParameters()));

header('Content-Type: text/css; charset=utf-8');

echo $twig->render($_SERVER['REQUEST_URI']);