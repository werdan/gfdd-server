<?php

require_once 'AbstractApiController.php';
require_once 'CountryDefiner.php';

class Api_InfoController extends AbstractApiController {

    protected function getAllowedHTTPMethod() {
       return self::HTTP_METHOD_GET;		
    }
    
    public function rangerateAction(){
       
        $this->assertIsTrueUser($this->getCurrentUser()->id);
        $config = Zend_Registry::get('appConfig');
        //TODO: implement countries not RU
        $prefix = CountryDefiner::get();

        $this->view->range_cost = $config['taxesRadius'][$prefix]['tax'];
        $this->view->currency = $config['taxesRadius'][$prefix]['currency']; 
    }
}

