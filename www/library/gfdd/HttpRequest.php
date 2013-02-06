<?php
abstract  class HttpRequest {

	protected  $url;
	
	protected  $lastHttpCode;
	
	protected  $responseBody;
	
	public function __construct($url) {
		$this->url = $url;
	}
	
	public function getHttpCode() {
		return $this->lastHttpCode;
	}
	
	protected function setLastHttpCode($code) {
		$this->lastHttpCode = $code;
	}
	
	public function getResponseBody() {
		return $this->responseBody;
	}
	
	protected  function setResponseBody($body) {
		$this->responseBody = $body;
	}
	
	abstract public function launch();
	
}