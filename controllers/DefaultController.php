<?php
namespace Controllers;

use Ezrun\Core\Controller;
use Models\TestModel;

class DefaultController extends Controller {
    
    public function indexAction() {
        
        return $this->render('index.html.twig', array());
    }
    
    public function testAction() {
        
        $testmodel = new TestModel();
        
        var_dump($testmodel->findAll(array('id' => 1)));
        
        return $this->render('start:test.html.twig');
    }
}