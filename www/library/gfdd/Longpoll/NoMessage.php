<?php
require_once 'Longpoll/AbstractMessage.php';

class NoMessage extends AbstractMessage {

	public function getXml() {
		$xml = $this->getEmptyXml();
		return $xml->asXML();
	}
	
	public function __construct() {
		
	}
	
}