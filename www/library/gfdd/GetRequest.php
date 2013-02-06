<?php
include_once "HttpRequest.php";

class GetRequest extends HttpRequest {
	
	public function launch() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->setLastHttpCode($httpCode);
		$this->setResponseBody($head);
		curl_close($ch);
		return $this;
	}	
}