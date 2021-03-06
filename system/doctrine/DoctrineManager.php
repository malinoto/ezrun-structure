<?php

use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\EntityManager;

class DoctrineManager extends \Ezrun\Core\System\SystemAbstract {
    
    protected $config;
    protected $em;
    protected $driver;
    protected $helperSet;
    protected $cmf;
    
    public function prepareVariables() {
        
        $dbParams = array(
            'driver'   => db_driver,
            'user'     => db_user,
            'password' => db_pass,
            'dbname'   => db_name,
            'charset'  => db_charset,
        );
        
        $this->config = Setup::createAnnotationMetadataConfiguration(array(yaml_path), false);
        $this->em     = EntityManager::create($dbParams, $this->config);
        
        // custom datatypes (not mapped for reverse engineering)
        $this->em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
        $this->em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        
        $this->driver = new YamlDriver(array(yaml_path));
        
        $this->config->setMetadataDriverImpl($this->driver);
        
        $this->helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
            'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($this->em->getConnection()),
            'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($this->em)
        ));
        
        $this->cmf = new Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
        $this->cmf->setEntityManager($this->em);
    }
    
    public function executeCommand() {
        
        switch($this->getCommand()) {
            
            case 'orm:generate-entities':
                $this->generateEntities();
                break;
            case 'orm:schema-tool:create':
                $this->schemaCreate();
                break;
            case 'orm:schema-tool:update':
                $this->schemaUpdate();
                break;
            case 'orm:schema-tool:drop':
                $this->schemaDrop();
                break;
            default:
                echo 'Unknown command';
                break;
        }
        
        //ConsoleRunner::run($helperSet);
        //exec("" . vendor_path . "bin/doctrine {$this->getCommand()}", $output);
    }
    
    private function generateEntities() {
        
        $metadata = $this->cmf->getAllMetadata();

        $generator = new \Doctrine\ORM\Tools\EntityGenerator(); 
        $generator->setRegenerateEntityIfExists(true); 
        $generator->setUpdateEntityIfExists(true); 
        $generator->setGenerateStubMethods(true);
        $generator->setGenerateAnnotations(true);
        $generator->setBackupExisting(false);
        $generator->generate($metadata, entities_path);
    }
    
    private function schemaCreate() {
        
        $schema_tool    = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata       = $this->cmf->getAllMetadata();
        
        return $schema_tool->createSchema($metadata);
    }
    
    private function schemaUpdate() {
        
        $schema_tool    = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata       = $this->cmf->getAllMetadata();
        
        return $schema_tool->updateSchema($metadata);
    }
    
    private function schemaDrop() {
        
        $schema_tool    = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata       = $this->cmf->getAllMetadata();
        
        $schema_tool->dropSchema($metadata);
    }
}
