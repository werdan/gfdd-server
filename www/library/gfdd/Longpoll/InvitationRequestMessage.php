<?php
require_once 'Longpoll/AbstractMessage.php';

class InvitationRequestMessage extends AbstractMessage {
	
	private $timestampCreated;
	
	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('request');
		$xml->invitation->request->addChild('person');

		$xml->invitation->request->person->addChild('id', $this->getSender()->id);
		$xml->invitation->request->person->addChild('name', $this->getSender()->name);
		$xml->invitation->request->person->addChild('age', $this->getSender()->age);
		$xml->invitation->request->person->addChild('sex', $this->getSender()->sex);
		$xml->invitation->request->addChild('time', $this->getTimestampCreated());
		return $xml->asXML();
	}
	
	public function setTimestampCreated($timestampCreated) {
		$this->timestampCreated = $timestampCreated;
	}
	
	public function getTimestampCreated() {
		return $this->timestampCreated;
	}
}