<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationAgreeMessage extends AbstractMessage {

	private $finalTime;
	
	public function getFinalTime() {
		return $this->finalTime;
	}
	
	public function setFinalTime($time) {
		$this->finalTime = $time;
	}
	
	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('agreed');
		$xml->invitation->agreed->addChild('time', $this->getFinalTime());
		return $xml->asXML();
	}
}