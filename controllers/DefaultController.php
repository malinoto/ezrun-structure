<?php
namespace Controllers;

use Ezrun\Core\Controller;
use Ezrun\Core\Form;
use Ezrun\Core\Request;
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
    
    public function loginAction(Request $request) {
        
        $form = new Form(array('login'));
        
        $form->setText(array('email', array()));
        $form->setPassword(array('password', array()));
        $form->setSubmit(array('submit', array('value' => 'Go!')));
        
        if($form->isPost()) {
            
            echo 'submitted';
        }
        
        return $this->render('login.html.twig', array(
            'login' => $form
        ));
    }
}