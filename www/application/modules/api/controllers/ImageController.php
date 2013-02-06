<?php 
require_once 'controllers/AbstractApiController.php';

class Api_ImageController extends AbstractApiController {

	protected function getAllowedHTTPMethod() {
		return self::HTTP_METHOD_GET;	
	}	
		
	public function photoAction() {
		$userId = $this->getRequest()->getParam("id");
		$user = User::getById($userId);
		if ($user == false) {
			throw new HttpReturnableException("011: Can not return photo, user ID is not specified" , self::HTTP_CODE_NOT_FOUND);
		}
                $hash = $this->getRequest()->getHeader('If-None-Match');
                if (!$hash || !$user->eTag || $user->eTag != $hash ) {
                    $im = @imagecreatefromjpeg($user->photoFileName);
                    ob_start();
                    imagejpeg($im);
                    $content = ob_get_contents();
                    $etag = md5($content);
                    $this->getResponse()->setHeader('Content-Type', 'image/jpeg');
                    $this->getResponse()->setHeader('ETag', $etag);
                    ob_end_clean();		
                    imagedestroy($im);
                    Logger::getLogger()->info("Returning userpic for user with id = " .$userId);
                    $this->view->image = $content;
                    $user->eTag = $etag;
                    $user->save();
                }else {
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender();
                    $this->getResponse()->setHttpResponseCode(self::HTTP_CODE_NOT_MODIFIED);
                }
	}
	
}