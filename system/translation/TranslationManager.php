<?php

use Symfony\Component\Yaml\Yaml;
use Ezrun\Core\TwigCustomExtension;

class TranslationManager extends \Ezrun\Core\System\SystemAbstract {
    
    protected $files                = array();
    protected $exclude_extensions   = array('css', 'scss', 'less');
    protected $labels               = array();
    
    public function executeCommand() {
        
        switch($this->getCommand()) {
            
            case 'trans:generate-files':
                $this->getTemplateFiles();
                $this->generateFiles();
                
                break;
            default:
                echo 'Unknown command';
                break;
        }
    }
    
    private function generateFiles() {
        
        $twig = new Twig_Environment();
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new TwigCustomExtension($this->getConfigParser()->getParameters(), false));
        
        foreach ($this->getFiles() as $file) {
            
            $tree = file_get_contents($file);
            $this->parseTree($tree);
        }
        
        $this->createLabels();
    }
    
    private function getTemplateFiles($dir = '') {
        
        $current_path   = realpath(views_path . $dir) . ds;
        $files          = scandir($current_path);
        
        foreach($files as $file) {
            
            if(!in_array($file, array('.', '..'))) {
                
                if(is_dir($current_path . $file)) $this->getTemplateFiles($file);
                else {
                    
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    
                    if(!in_array($ext, $this->exclude_extensions))
                        $this->addFile($current_path . $file);
                }
            }
        }
    }
    
    private function addFile($file) {
        
        array_push($this->files, $file);
        
        return $this;
    }
    
    private function getFiles() {
        
        return $this->files;
    }
    
    private function addLabel($label) {
        
        array_push($this->labels, strtoupper($label));
        
        return $this;
    }
    
    private function getLabels() {
        
        return array_unique($this->labels, SORT_STRING);
    }
    
    private function parseTree($tree) {
        
        $matches_filter = array();
        
        preg_match_all('/\{\{(\s)?\'([^\']*)\'\|trans(\s)?\}\}/iu', $tree, $matches_filter);
        
        if(isset($matches_filter[2])) {
            
            foreach($matches_filter[2] as $label) {
                
                $this->addLabel($label);
            }
        }
        
        $matches_function = array();
        
        preg_match_all('/\{\{(\s)?trans\(\'([^\']*)\'\)(\s)?\}\}/iu', $tree, $matches_function);
        
        if(isset($matches_function[2])) {
            
            foreach($matches_function[2] as $label) {
                
                $this->addLabel($label);
            }
        }
    }
    
    private function createLabels() {
        
        $languages = unserialize(languages);
        
        foreach($languages as $lang_key => $lang_value) {
            
            $complete_labels    = array();
            $translation_file   = languages_path . '_' . $lang_key . '.yml';
            
            if(is_file($translation_file))
                $complete_labels = Yaml::parse($translation_file, true, false);
            
            foreach($this->getLabels() as $label) {
                
                if(!isset($complete_labels[$label]))
                    $complete_labels[$label] = $label;
            }
            
            ksort($complete_labels);
            
            $yml = Yaml::dump($complete_labels);
            
            $handle = fopen($translation_file, "w");
            fwrite($handle, $yml);
            fclose($handle);
        }
    }
}