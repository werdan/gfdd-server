<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationCheckinMessage extends AbstractMessage {
	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('checkedin');
		return $xml->asXML();
	}
}