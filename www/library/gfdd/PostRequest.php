<?php
include_once "HttpRequest.php";

class PostRequest extends HttpRequest {
	
	private $postvars = array();

	public function launch() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postvars);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		$this->setLastHttpCode($httpCode);
		$this->setResponseBody($head);
		
		curl_close($ch);
		return $this;
	}
	
	public function setPostVars($postvars) {
		$this->postvars = $postvars;
	}
}