<?php
include_once "PostRequest.php";
include_once "GetRequest.php";

class MockClient extends Zend_Test_PHPUnit_AbstractApiTestCase {

	private $host;
	
	const URL_AUTH_SIGNIN = "/api/auth/signin";
	const URL_PEOPLE_LIST = "/api/people/list";
    const URL_PEOPLE_LIST_NO_AUTH = "/api/people/listnoauth";
	const URL_IMAGE_PHOTO = "/api/image/photo";
	const URL_INVITATION_REQUEST = "/api/invitation/request";
	const URL_INVITATION_ACCEPT = "/api/invitation/accept";
	const URL_INVITATION_PLACETIME = "/api/invitation/placetime";
	const URL_INVITATION_AGREE = "/api/invitation/agree";
	const URL_INVITATION_REJECT = "/api/invitation/reject";
    const URL_INVITATION_LATE = "/api/invitation/late";
    const URL_INVITATION_CHECKIN = "/api/invitation/checkin";
    const URL_INVITATION_ABUSE = "/api/invitation/abuse";
    const URL_INVITATION_FINISHED = "/api/invitation/finished";
    const URL_SETTINGS_RANGE = "/api/settings/range";
    const URL_RADIUS_RATE = "/api/info/rangerate";
	const URL_MESSAGE = "/api/message/";
	
	private $lastResponse;
	private $secretKey;
	private $coordinates;
	private $userName;
	private $lastMessageId;
	
	public function __construct($host) {
		$this->host = $host;
	}

		public function registerClient($sex = "M", $lookingFor = "F", $coordinates = null) {
		$this->createTestUserpicAndSetFiles(240, 320);
		$postvars = $this->getRequestData();
		unset($postvars['secretKey']);

        if (!isset($this->userName)) {
            $this->userName = "Билли" . rand(10000,1000000);
        }

		$postvars['name'] = $this->userName;
		$postvars['sex'] = $sex;
		$postvars['lookingFor'] = $lookingFor;
		$postvars['coordinates'] = $coordinates;
		$postvars['file'] = '@' . $_FILES['userpic']['name'];

		$url = $this->getAbsoluteUrl(self::URL_AUTH_SIGNIN, array());
		$request = new PostRequest($url);
		$request->setPostVars($postvars);
		$this->lastResponse = $request->launch();
		try {
			$xml = new SimpleXMLElement($this->getResponseBody());
			$this->setSecretKey($xml->secretKey);
		} catch (Exception $e) {
			echo $this->getResponseBody();
			echo $e->getMessage();
		}
	} 

	public function registerClientIncomplete($sex = "M", $lookingFor = "F", $coordinates = null) {
		$this->createTestUserpicAndSetFiles(240, 320);
		$postvars = $this->getRequestData();
		unset($postvars['secretKey']);
		
		$this->userName = "Билли" . rand(10000,1000000);
		
		$postvars['name'] = $this->userName;
		
		///NOT FILLED-IN INTENTIONALY!!!
		unset($postvars['sex']);
		$postvars['lookingFor'] = $lookingFor;
		$postvars['coordinates'] = $coordinates;
		$postvars['file'] = '@' . $_FILES['userpic']['name'];

		$url = $this->getAbsoluteUrl(self::URL_AUTH_SIGNIN, array());
		$request = new PostRequest($url);
		$request->setPostVars($postvars);
		$this->lastResponse = $request->launch();
	} 

	public function setSecretKey($secretKey) {
		$this->secretKey = $secretKey;
	}
	
	public function getSecretKey() {
		return $this->secretKey;
	}
	
	public function setCoordinates($coordinates) {
		$this->coordinates = $coordinates;
	}
	
	public function getCoordinates() {
		return $this->coordinates;
	}

    public function setName($name) {
        $this->userName = $name;
    }

	public function getName() {
		return $this->userName;
	}
	
	public function listPeopleAround() {
		$params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
		$url = $this->getAbsoluteUrl(self::URL_PEOPLE_LIST, $params);
		$request = new GetRequest($url);
		$this->lastResponse = $request->launch();
	}

    public function listPeopleAroundNoAuth() {
        $params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
        $url = $this->getAbsoluteUrl(self::URL_PEOPLE_LIST_NO_AUTH, $params);
        $request = new GetRequest($url);
        $this->lastResponse = $request->launch();
    }

    public function sendInvitationRequest($personId) {
		$params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey(), "id" => $personId);
		$url = $this->getAbsoluteUrl(self::URL_INVITATION_REQUEST, array());
		$request = new PostRequest($url);
		$request->setPostVars($params);
		$this->lastResponse = $request->launch();
	}
	
	public function acceptInvitation() {
		$params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
		$url = $this->getAbsoluteUrl(self::URL_INVITATION_ACCEPT, array());
		$request = new PostRequest($url);
		$request->setPostVars($params);
		$this->lastResponse = $request->launch();
	}

	public function rejectInvitation() {
		$params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
		$url = $this->getAbsoluteUrl(self::URL_INVITATION_REJECT, array());
		$request = new PostRequest($url);
		$request->setPostVars($params);
		$this->lastResponse = $request->launch();
	}
	
	public function agreeOnPlacetime() {
		$params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
		$url = $this->getAbsoluteUrl(self::URL_INVITATION_AGREE, array());
		$request = new PostRequest($url);
		$request->setPostVars($params);
		$this->lastResponse = $request->launch();
	}

	public function sendLate($latefor) {
		$params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey(), "latefor" => $latefor);
		$url = $this->getAbsoluteUrl(self::URL_INVITATION_LATE, array());
		$request = new PostRequest($url);
		$request->setPostVars($params);
		$this->lastResponse = $request->launch();
	}
    
    public function checkin() {
        $params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
        $url = $this->getAbsoluteUrl(self::URL_INVITATION_CHECKIN, array());
        $request = new PostRequest($url);
        $request->setPostVars($params);
        $this->lastResponse = $request->launch();
    }
    
    public function sendAbuse() {
        $params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
        $url = $this->getAbsoluteUrl(self::URL_INVITATION_ABUSE, array());
        $request = new PostRequest($url);
        $request->setPostVars($params);
        $this->lastResponse = $request->launch();
    }
    
    public function enableExtendSearchRange() {
        $params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
        $url = $this->getAbsoluteUrl(self::URL_SETTINGS_RANGE, array());
        $request = new PostRequest($url);
        $request->setPostVars($params);
        $this->lastResponse = $request->launch();
    }
    
    public function finishMeeting() {
        $params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
        $url = $this->getAbsoluteUrl(self::URL_INVITATION_FINISHED, array());
        $request = new PostRequest($url);
        $request->setPostVars($params);
        $this->lastResponse = $request->launch();
    }
    
    public function getCostOfIncreaseSearchRadius() {
        $params = array('coordinates' => $this->getCoordinates(), "secretKey" => $this->getSecretKey());
        $url = $this->getAbsoluteUrl(self::URL_RADIUS_RATE, $params);
        $request = new GetRequest($url);
        $this->lastResponse = $request->launch();
    }
    
	public function proposePlaceTime($placeId, $timeshift) {
		$params = array('coordinates' => $this->getCoordinates(), 
						"secretKey" => $this->getSecretKey(),
						'placeId' => $placeId,
						'timeshift' => $timeshift);
		$url = $this->getAbsoluteUrl(self::URL_INVITATION_PLACETIME, array());
		$request = new PostRequest($url);
		$request->setPostVars($params);
		$this->lastResponse = $request->launch();
	}
	
	public function getIncomingMessage() {
		$params = array('coordinates' => $this->getCoordinates(), 
						"secretKey" => $this->getSecretKey());
		if ($this->lastMessageId > 0) {
			$params['lastMessageId'] = $this->lastMessageId;
		}
		$url = $this->getAbsoluteUrl(self::URL_MESSAGE, $params);
		$request = new GetRequest($url);
		$this->lastResponse = $request->launch();
		$responseXml = new SimpleXMLElement($this->getResponseBody());
		if (isset($responseXml->messageId) and  intval($responseXml->messageId) > 0) {
			$this->lastMessageId = intval($responseXml->messageId);
		}
	}
	
	public function getPhoto($personId) {
		$params = array('id' => $personId);
		$url = $this->getAbsoluteUrl(self::URL_IMAGE_PHOTO, $params);
		$request = new GetRequest($url);
		$this->lastResponse = $request->launch();
	}

	public function getLastHttpCode() {
		return $this->lastResponse->getHttpCode();
	}
	
	public function getResponseBody() {
		return $this->lastResponse->getResponseBody();
	}
	
	private function getAbsoluteUrl($methodUrl, $params) {
		$queryString = "";
		foreach ($params as $paramName => $paramValue) {
			$queryString .= (strlen($queryString) > 0 ? "&" : "") . $paramName ."=" . $paramValue;
		}
		return $this->host. $methodUrl . "?" . $queryString;
	}
}
