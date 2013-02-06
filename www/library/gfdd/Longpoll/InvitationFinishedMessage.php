<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationFinishedMessage extends AbstractMessage {

	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('finished');
		return $xml->asXML();
	}
}