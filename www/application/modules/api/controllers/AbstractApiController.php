<?php

require_once 'Utils/Logger.php';
require_once 'Utils/XMLTools.php';
require_once 'Utils/HttpReturnableException.php';

/**
 * Auth API controller
 * @author ansam
 */
abstract class AbstractApiController extends Zend_Controller_Action {

    const HTTP_CODE_OK = 200;
    const HTTP_CODE_NOT_MODIFIED = 304;
	const HTTP_CODE_BAD_REQUEST = 400;
	const HTTP_CODE_FORBIDDEN = 403;
	const HTTP_CODE_NOT_FOUND = 404;
	const HTTP_CODE_METHOD_NOT_ALLOWED = 405;
    const HTTP_CODE_NOT_ACCEPTABLE = 406;
    const HTTP_CODE_CONFLICT = 409;
	const HTTP_CODE_INTERNAL_SERVER_ERROR = 500;
		
	const HTTP_METHOD_GET = "GET";
	const HTTP_METHOD_POST = "POST";	
	const SECRET_KEY_PARAM = "secretKey";
	public static $SPECIAL_ACTIONS_WITH_NO_AUTH = array('signin','prohibited', "listnoauth", "photo");

    //TODO: move to configuration or delete if not necessary
    const USERPIC_PATH = '/../media/userpics/';


    abstract protected function getAllowedHTTPMethod();

	public function init()
	{
		$this->_helper->layout()->setLayout('auth');
	}

	/**
	 * Check authentication information
	 * @see library/zendframework/Zend/Controller/Zend_Controller_Action::preDispatch()
	 */
	public function preDispatch()
	{
		Logger::getLogger()->info("Preprocessing request for action '".$this->getRequest()->getActionName()."'");
		$this->logRequestParams();
		
		$this->assertAllowedMethod();
		
		$secretKey = $this->_getParam($this::SECRET_KEY_PARAM);
		$user = User::getBySecretKey($secretKey);
		$this->setCurrentUser($user);

        Logger::getLogger()->debug("Checking for bypass for special actions");
		if ($this->bypassForSpecialActions($this->getRequest()->getActionName())) return;
        if (empty($secretKey) || empty($user)) {
            Logger::getLogger()->warn("Incorrect secretKey:" . (!empty($secretKey) ? "= ".$secretKey : " empty field"));
            $this->_forward('prohibited','error','default');
            return;
        }
        Logger::getLogger()->info("Secret key identifies user with id = ". $user->id);
        $this->setNewCoordinatesFromRequest();
	}

	private function assertAllowedMethod() {
            switch ($this->getAllowedHTTPMethod()) {
			case self::HTTP_METHOD_GET:
				if (!$this->getRequest()->isGET()) {
					throw new HttpReturnableException("001: Wrong request method: ". $this->getRequest()->getMethod() .". Expecting GET" , self::HTTP_CODE_METHOD_NOT_ALLOWED);
				}
				break;
			case self::HTTP_METHOD_POST:
				if (!$this->getRequest()->isPOST()) {
					throw new HttpReturnableException("001: Wrong request method: ". $this->getRequest()->getMethod() .". Expecting POST" , self::HTTP_CODE_METHOD_NOT_ALLOWED);
				}
				break;
			default:
				throw new HttpReturnableException("002: Incorrect allowed method for action: " .$this->getAllowedHTTPMethod(), self::HTTP_CODE_INTERNAL_SERVER_ERROR);
            }
	}
	
	public function postDispatch()
	{
		$user = $this->getCurrentUser();
		if ($user instanceof User) {
			$user->setLastRequestTimeStamp();
			$user->save();
			$this->setCurrentUser($user);
		}
	}
	
	private function bypassForSpecialActions($actionName) {
			if (in_array($actionName, self::$SPECIAL_ACTIONS_WITH_NO_AUTH)) {
				Logger::getLogger()->info("Action '". $actionName . "' was recognized as a special one");
				return true;
			}
	}
	
	protected function setNewCoordinatesFromRequest() {
		Logger::getLogger()->notice("Processing coordinates");
		$coordinates = $this->getRequest()->getParam('coordinates');
		if (!empty($coordinates)) {
			$coordinateArray = explode(",",$coordinates);
			if (count($coordinateArray) == 2 &&
			   (int) $coordinateArray[0] <= 90 && 
			   (int) $coordinateArray[0] >= -90 && 
			   (int) $coordinateArray[1] <= 180 &&
			   (int) $coordinateArray[1] >= -180 && 
			   is_numeric($coordinateArray[0]) &&
			   is_numeric($coordinateArray[1]) ) {

			   Logger::getLogger()->debug("Found valid coordinates in request: " . $coordinates);
			   $user = $this->getCurrentUser();
			   Logger::getLogger()->debug("Setting coordinates for user id=" . $user->id);
			   $user->latitude = round(floatval($coordinateArray[0]),6);
			   $user->longitude = round(floatval($coordinateArray[1]),6);
			   $user->save();
			   $this->setCurrentUser($user);
			} else {
				Logger::getLogger()->warn('Wrong coordinates sent: ' . $coordinates);
			}
		} else {
			Logger::getLogger()->warn("No coordinates to parse found");
		}
	}
	
    protected  function assertUserNotEmpty($invitee) {
		if ($invitee == false || empty($invitee->id) || $invitee->id < 0) {
			throw new HttpReturnableException("105: Invitee user is not set", self::HTTP_CODE_FORBIDDEN);
		}
	}
	
	protected function setCurrentUser($user) {
		if (!empty($user->id)) {
			Logger::getLogger()->notice("Setting currentUser: id=". $user->id);
			Zend_Registry::set('currentUser',$user);
		}
	}
	
	protected function getCurrentUser() {
		if (Zend_Registry::isRegistered('currentUser')) {
			$currentUser = Zend_Registry::get('currentUser');
			return $currentUser;
		}
		return new User();		
	}
	
	protected function logRequestParams() {
		$paramsLog = array();
		foreach ($this->getRequest()->getParams() as $paramKey => $param) {
			$paramsLog[] = $paramKey . "=" . $param;
		}
		Logger::getLogger()->info("Params in request: " . implode(";", $paramsLog));
	}


    protected function assertIsTrueUser($userId){
        $user = $this->getCurrentUser();
        if($userId === $user->id){
            return $user;
        }else{
            throw new HttpReturnableException("003: Incorrect incoming user id",
                self::HTTP_CODE_FORBIDDEN);
        }
    }

    protected  function userHasActiveInvitation($user) {
        if (isset($user->Invitation)) {
            $invitation = $user->Invitation;
            if ($invitation instanceof Invitation && $invitation->id > 0 ) {
                return true;
            }
        }
        return false;
    }
}
