<?php

require_once('HttpReturnableException.php');

class RequestValidator {

    const HTTP_CODE_FORBIDDEN = 403;
	public static $REQUIRED_AUTH_PARAMS = array('name','sex','age','lookingFor','country','provider','phoneId');
	
	private $request;
	
	public function setRequest($request) {
		$this->request = $request;
	}
	
	public function validate() {
		$this->validateAuthAllParamsPresent();
		$this->validateParamsValues();		
	}
	
	private function validateAuthAllParamsPresent() {
		$params = $this->request->getParams();
		$requiredParamsNotPresentInRequest = array_diff($this::$REQUIRED_AUTH_PARAMS, array_keys($params));
		
		if (!empty($requiredParamsNotPresentInRequest)) {
			throw new HttpReturnableException("006: Parameter(s): ". implode(',',$requiredParamsNotPresentInRequest) . " are not present in request", self::HTTP_CODE_FORBIDDEN);
		}
		
		Logger::getLogger()->info("Request contains all necessary params: ");
	}
	
	private function validateParamsValues() {
		foreach ($this->request->getParams() as $paramKey => $param) {
			$validatorFunctionName = "validate" . ucfirst($paramKey);
			if (is_callable(array($this, $validatorFunctionName), false, $callback_name)) {
				call_user_func($callback_name, $param);
			}
		}
	}
	
	private function validateAge($age) {
		$age = intval($age);
		Logger::getLogger()->notice("Validating Age parameter with value ". $age);
		if ($age<18 || $age>80) {
			throw new HttpReturnableException("007: Age " . $age . " is not valid", self::HTTP_CODE_FORBIDDEN);
		}
	}
	
	private function validateCountry($country) {
		$countries = $this->readCountriesList();
		if (!empty($countries) && !in_array($country, $countries)) {
			throw new HttpReturnableException("008: Country code " . $country. " is not valid", self::HTTP_CODE_FORBIDDEN);
		}
	}
	
	private function validateSex($sex) {
		Logger::getLogger()->notice("Validating sex parameter with value ". $sex);
		if ($sex != "M" && $sex != "F") {
			throw new HttpReturnableException("009: Sex " . $sex . " is not valid", self::HTTP_CODE_FORBIDDEN);
		}
	}

	private function validateLookingFor($lookingFor) {
		Logger::getLogger()->notice("Validating lookingFor parameter with value ". $lookingFor);
		if ($lookingFor != "M" && $lookingFor != "F" && $lookingFor != "MF") {
			throw new HttpReturnableException("010: LookingFor param " . $lookingFor. " is not valid", self::HTTP_CODE_FORBIDDEN);
		}
	}

	private function readCountriesList() {
   		$countries = array();
	    $appConfig = Zend_Registry::get('appConfig');
    	$countiesListFile = APPLICATION_PATH . "/configs/". $appConfig['coutriesListFile']; 
	 	if (file_exists($countiesListFile)) {
	    		$xml = simplexml_load_file($countiesListFile);
	    		foreach ($xml->country as $countryCode) {
	    			$countries[] = (string) $countryCode;
	    		}		
		}
		return $countries;
	}
}