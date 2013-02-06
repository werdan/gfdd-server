<?php

require_once 'controllers/AbstractApiController.php';
require_once 'RequestValidator.php';

/**
 * Auth API controller
 * @author ansam
 */
class Api_AuthController extends AbstractApiController {

	public function init()
	{
		$this->_helper->layout()->setLayout('auth');
	}
	
	protected function getAllowedHTTPMethod() {
		return self::HTTP_METHOD_POST;	
	}	
		
	public function signinAction() {
		Logger::getLogger()->info("Processing authentication request");

		$requestValidator = new RequestValidator();
                $params = $this->getRequest();
		$requestValidator->setRequest($params);
		$requestValidator->validate();
		
		$photoFileName = $this->uploadAndValidateUserPhoto();
		$this->createOrUpdateUserFromRequest($photoFileName);
		$this->setNewCoordinatesFromRequest();
		$this->view->assign('secretKey', $this->getCurrentUser()->secretKey);
	}
	
	
	private function createOrUpdateUserFromRequest($photoFileName) {
		$user = $this->getCurrentUser();		
		if (empty($user->id)) {
			$user = User::createUserFromRequest($this->getRequest());			
		} else {
			$user->updateUserFromRequest($this->getRequest());			
		}	
		$user->photoFileName = $photoFileName;

        //Reset etag
        $user->eTag = "";
		$user->save();
		$this->setCurrentUser($user);		
	}
	
	private function uploadAndValidateUserPhoto() {
		$upload = $this->getUploader();
    	if ($upload->isValid()) {
      		$upload->receive();
    	} else {
			throw new HttpReturnableException("005: User photo file is not valid: ". implode(";", $upload->getMessages()), self::HTTP_CODE_FORBIDDEN);
    	}
    	return $upload->getFileName();
	}
	
	private function getUploader() {
		$appConfig = Zend_Registry::get('appConfig');
		$uploaderClassName = $appConfig['UploaderClass']; 
		if (!empty($uploaderClassName)) {
				$upload = new $uploaderClassName();
				if ($upload instanceof Zend_File_Transfer_Adapter_Abstract) {
					$upload->setDestination($appConfig['userPhotosDestinationFolder']);
   					$upload->addValidator('ImageSize', false,
	   		        array('minwidth' => 240,
				        'maxwidth' => 1024,
				        'minheight' => 320,
				        'maxheight' => 1024)
				    );
				    $upload->addFilter('Rename',array('target' => $appConfig['userPhotosDestinationFolder']));
					return $upload;
				}
		}
		throw new Exception("UploaderClass is not defined or point to inexistent/wrong class");
	}
	
}
