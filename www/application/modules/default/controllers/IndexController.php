<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
       $this->_helper->layout()->setLayout('index');
    }

    public function indexAction()
    {    
     $this->view->assign('title', 'Инua');
    }   


    
}

