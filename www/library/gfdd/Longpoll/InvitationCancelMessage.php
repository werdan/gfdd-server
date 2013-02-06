<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationCancelMessage extends AbstractMessage {
	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('canceled');
		return $xml->asXML();
	}
}