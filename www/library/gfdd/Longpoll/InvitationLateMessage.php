<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationLateMessage extends AbstractMessage {

	private $latefor;
	
	public function setLateFor($latefor) {
		$this->latefor = $latefor;
	}
	
	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('late');
		$xml->invitation->late->addChild('latefor', $this->latefor);
		return $xml->asXML();
	}
}