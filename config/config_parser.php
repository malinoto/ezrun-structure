<?php

use Symfony\Component\Yaml\Yaml;

class ConfigParser {
    
    protected $paramters;
    protected $environment;
    protected $routing;
    
    public function __construct() {
        
        $this->setEnvironment();
        $this->parse();
        $this->prepare();
    }
    
    /**
     * Parse yaml file and set variables
     */
    private function parse() {
        
        //parameters
        $config_file    = dirname(__FILE__) . '/definitions.yml';
        $parameters     = Yaml::parse($config_file, true, true);
        
        foreach($parameters as $key => $value) {
            
            if($key == 'imports') {
                
                foreach($value as $resource) {
                    
                    $nested_file        = dirname(__FILE__) . '/' . $resource['resource'];
                    $nested_parameters  = Yaml::parse($nested_file, true, true);
                    
                    foreach($nested_parameters['parameters'] as $param_name => $param_value) {
                        
                        $this->addParameter($param_name, $param_value);
                    }
                }
            }
            else if($key == 'parameters') {
                
                foreach($value as $param_name => $param_value) {

                    $this->addParameter($param_name, $param_value);
                }
            }
        }
        
        //routing
        $routing_file   = dirname(__FILE__) . '/routing.yml';
        $routes         = Yaml::parse($routing_file, true, true);
        
        foreach($routes as $key => $value) {
            
            $this->addRouting($key, $value);
        }
    }
    
    /**
     * Prepare settings.yaml variables for global use
     */
    private function prepare() {
        
        $parameters = $this->getParameters();
        
        //set the default domain name
        if(!isset($_SERVER['HTTP_HOST'])) 	$_SERVER['HTTP_HOST'] = $parameters['cli_domain'];
        if(!isset($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME'] = $parameters['cli_domain'];

        $phpself = strpos($_SERVER['PHP_SELF'], '/') > -1
                ? strrchr($_SERVER['PHP_SELF'], '/')
                : $_SERVER['PHP_SELF'];
        
        if(php_sapi_name() === 'cli')
            $domain_dir = '';
        else
            $domain_dir = preg_replace('#/(admin|ajax|thumbs).*$#i', '',
                    str_replace($phpself, '', $_SERVER['PHP_SELF']));
        
        define('domain', $_SERVER['HTTP_HOST'] . $domain_dir);

        //cookies domain
        $cdomain_array = explode('.', domain);
        define('cookies_domain', (count($cdomain_array) > 2
                ? str_replace($cdomain_array[0] . '.', '', domain)
                : domain
        ));

        //paths
        define('ds', DIRECTORY_SEPARATOR);
        define('root', realpath(dirname(__FILE__) . ds . '..' . ds) . ds);

        //local paths
        define('lib_path', root . 'lib' . ds);
        define('core_path', root . 'core' . ds);
        define('config_path', root . 'config' . ds);
        define('vendor_path', root . 'vendor' . ds);

        define('classes_path', core_path . 'classes' . ds);
        define('helpers_path', core_path . 'helpers' . ds);
        
        //doctrine
        define('doctrine_path', config_path . 'doctrine' . ds);
        define('entities_path', doctrine_path . 'entities' . ds);
        define('yaml_path', doctrine_path . 'yaml' . ds);
        
        define('template_name',	'default');
        define('cachedir', root . 'cache' . ds);
        define('site_cachedir', cachedir . 'site' . ds . template_name . ds);
        define('admin_cachedir', cachedir . 'admin' . ds . template_name . ds);
        
        define('models_path', root . 'models' . ds);

        define('views_path', root . 'views' . ds . 'site' . ds . template_name . ds);
        define('admin_views_path', root . 'views' . ds . 'admin'  . ds . template_name . ds);

        define('controllers_path', root . 'controllers' . ds);
        define('admin_controllers_path', root . 'controllers' . ds . 'admin' . ds);
        define('partials_path', root . 'controllers' . ds . 'partials' . ds);

        define('languages_path', config_path . 'languages' . ds);
        define('public_path', root . 'web' . ds);
        define('js_path', public_path . 'js' . ds);
        define('css_path', views_path . 'css' . ds);
        define('images_path', public_path . 'images' . ds . template_name . ds);

        define('upload_path', root . 'upload' . ds);
        define('pics_path', upload_path . 'pics' . ds);
        define('upload_pics_path', pics_path . 'original' . ds);
        define('thumbs_path', pics_path . 'thumbs' . ds);
        define('temporary_upload_folder', public_path . 'temp' . ds);
        define('media_path', public_path . 'media' . ds);

        //web paths
        define('http_protocol', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
                ? 'https://'
                : 'http://'
        ));
        define('webpath', http_protocol . domain . '/');
        define('admin_webpath', webpath . 'admin/');
        define('js_webpath', webpath . 'js/');
        define('images_webpath', webpath . 'images/' . template_name . '/');
        define('thumbs_webpath', webpath . 'thumbs/');
        define('media_webpath', webpath . 'media/');
        
        //smtp
        define('smtp_server', $parameters['smtp_server']);
        define('smtp_port', $parameters['smtp_port']);
        define('smtp_host', $parameters['smtp_host']);
        
        
        foreach($this->getParameters() as $key => $value) {
            
            if(!defined($key) && !is_array($value))
                define($key, $value);
        }
        
        //global
        $global_file    = dirname(__FILE__) . '/settings.yml';
        $globals        = Yaml::parse($global_file, true, true);
        
        foreach($globals as $key => $value) {
            
            //parameters
            if($key == 'parameters') {
                
                foreach($value as $pkey => $pvalue) {
                    
                    $this->addParameter($pkey, $pvalue);
                }
            }
            
            //set twig global variables
            if($key == 'twig') {
                
                foreach($value['globals'] as $gkey => $gvalue) {
                    
                    $var_name   = preg_replace('/%/iu', '', $gvalue);
                    $var_value  = $this->getParameter($var_name) !== null
                                 ? $this->getParameter($var_name)
                                 : constant($var_name);
                    
                    if($var_value !== null) {
                        
                        $parameter_value            = array();
                        $parameter_value[$var_name] = $var_value;
                        
                        $this->addParameter('twig', $parameter_value);
                    }
                }
                
            }
        }
    }
    
    public function setEnvironment() {
        
        $environment = getenv('EZrunEnvironment');
        if(empty($environment)) $environment = 'dev';
        
        $this->environment = $environment;
        
        return $this;
    }
    
    public function getEnvironment() {
        
        return $this->environment;
    }
    
    public function addParameter($label, $value) {
        
        if(isset($this->parameters[$label]) && is_array($this->parameters[$label])) {
            
            if(is_array($value)) {
                
                $this->parameters[$label] = array_merge($this->parameters[$label], $value);
                //$this->parameters[$label] = array_unique($this->parameters[$label]);
            }
            else
                array_push($this->parameters[$label], $value);
        }
        else
            $this->parameters[$label] = $value;
        
        return $this;
    }
    
    public function getParameters() {
        
        return $this->parameters;
    }
    
    public function getParameter($label) {
        
        return isset($this->parameters[$label]) ? $this->parameters[$label] : null;
    }
    
    public function addRouting($label, $value) {
        
        $this->routing[$label] = $value;
        
        return $this;
    }
    
    public function getRouting() {
        
        return $this->routing;
    }
}