<?php

$namespaces = require_once(dirname(__FILE__) . '/config/namespaces.php');

require_once(dirname(__FILE__) . '/config/config_parser.php');

$configParser = new ConfigParser();

use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$dbParams = array(
    'driver'   => db_driver,
    'user'     => db_user,
    'password' => db_pass,
    'dbname'   => db_name,
    'charset'  => db_charset,
);

$config = Setup::createAnnotationMetadataConfiguration(array(yaml_path), false);
$em     = EntityManager::create($dbParams, $config);

$driver = new YamlDriver(array(yaml_path));
$config->setMetadataDriverImpl($driver);
        
$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));
