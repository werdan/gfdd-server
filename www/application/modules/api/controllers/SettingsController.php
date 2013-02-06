<?php

require_once 'controllers/AbstractApiController.php';

class Api_SettingsController extends AbstractApiController {

    protected function getAllowedHTTPMethod() {
       return self::HTTP_METHOD_POST;
    }

    public function rangeAction(){
        //TODO: asserts ....
        $currentUser = $this->getCurrentUser();
        $currentUser->extendedRange = 1;

        $cfg = Zend_Registry::get('appConfig');
        $timeout = $cfg['timeoutExtendedRange'];
        $currentUser->rangeTimestamp = time() + $timeout;
        $currentUser->save();
    }
}

