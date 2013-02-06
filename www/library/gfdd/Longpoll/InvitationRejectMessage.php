<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationRejectMessage extends AbstractMessage {
	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('rejected');
		return $xml->asXML();
	}
}