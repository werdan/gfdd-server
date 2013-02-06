<?php
require_once 'Utils/Logger.php';

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                $this->getResponse()->setHttpResponseCode(AbstractApiController::HTTP_CODE_NOT_FOUND);
                $this->view->message = 'Page not found';
                break;
            default:
            	if ($errors->exception instanceof HttpReturnableException) {
				    $this->getResponse()->setHttpResponseCode($errors->exception->getHttpCode());
				    $this->view->assign("errorMessage", $errors->exception->getMessage());
            	} else {
    	            $this->view->message = 'Application error';
	                $this->getResponse()->setHttpResponseCode(AbstractApiController::HTTP_CODE_INTERNAL_SERVER_ERROR);
            	}
            	break;
        }
        
        $this->logException($errors);
		$this->displayException($errors);        
    }
    
	public function prohibitedAction() {
		$this->getResponse()->clearBody();
		$this->getResponse()->setHttpResponseCode(AbstractApiController::HTTP_CODE_FORBIDDEN);
	}

    private function logException($errors) {
    	if ($errors->exception instanceof HttpReturnableException) {
        	Logger::getLogger()->warn($errors->exception->getMessage());
        } else {
        	Logger::getLogger()->crit($errors->exception);
        }
    	
    }
    
    private function displayException($errors) {
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        $this->view->request   = $errors->request;
    	
    }

}

