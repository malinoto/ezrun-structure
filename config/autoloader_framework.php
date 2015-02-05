<?php

class Autoloader {
    
    public function __construct() {
        
        //$this->coreLoader();
        $this->modelLoader();
    }
    
    function modelLoader() {

        /*$model_file = models_path . strtolower($modelName) . '.model.php';

        if(is_file($model_file))
            $model = require_once($model_file);*/
        
        $models_path = dirname(__FILE__) . '/../models/';

        //include model files
        foreach (scandir($models_path) as $filename) {

            $path = $models_path . $filename;
            if (is_file($path)) {
                
                require_once($path);
                
                $classname = preg_replace('/\.model\.php/iu', '', $filename);
                $classname = preg_replace('/\.php/iu', '', $classname);
                
                spl_autoload_register('Models\\' . $classname . '::Model');
            }
        }
    }

    function coreLoader() {
        
        /*$core_file = core_path . strtolower($coreName) . '.core.php';

        if(is_file($core_file))
            $core = require_once($core_file);*/
        
        $core_path = dirname(__FILE__) . '/../core/';
        
        //include model files
        foreach (scandir($core_path) as $filename) {

            $path = $core_path . $filename;
            if (is_file($path)) {

                require_once($path);
                
                $classname = preg_replace('/\.core.php/iu', '', $filename);
                
                spl_autoload_register('Core\\' . $classname . '::BaseCore');
            }
        }
    }
}

return new Autoloader();
