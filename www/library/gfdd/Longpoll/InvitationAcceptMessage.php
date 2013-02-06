<?php
require_once 'Longpoll/AbstractMessageWithPlaces.php';
require_once 'DistanceCalculator.php';

class InvitationAcceptMessage extends AbstractMessageWithPlaces {

	public function getXml() {
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('accepted');
		$placesXml = $xml->invitation->accepted->addChild('places');
		$this->addPlacesAroundUser($placesXml);
		return $xml->asXML();
	}
}