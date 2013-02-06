<?php
require_once 'Longpoll/AbstractMessageWithPlaces.php';

class InvitationPlacetimeMessage extends AbstractMessageWithPlaces {

	protected $place;
	protected $timeshift;
	
	public function setPlace($place) {
		$this->place = $place;
	}
	
	public function getPlace() {
		return $this->place;
	}
	
	public function setTimeshift($timeshift) {
		$this->timeshift = $timeshift;
	}
	
	public function getTimeshift() {
		return $this->timeshift;
	}
	
	public function getXml() {
		$longitue = $this->getSender()->longitude;
		$latitude = $this->getSender()->latitude;
		
		$xml = $this->getEmptyXml();
		$xml->addChild('invitation');
		$xml->invitation->addChild('placetime');
		$place = $this->place;
		$timeshift = $this->timeshift;

		$placeXml = $xml->invitation->placetime->addChild('place');
		$placeXml->addChild('id', $place->id);
		$placeXml->addChild('name', $place->name);

        $distanceCalc = new DistanceCalculator($this->getSender());

		$placeXml->addChild('distance', $distanceCalc->getDistanceToTargetInMeters($place));
		if ($place->selected) {
			$placeXml->addChild('selected');
		}
		$placeXml->addChild('longitude', $place->longitude);
		$placeXml->addChild('latitude', $place->latitude);
		$placeXml->addChild('address', $place->getAddressLine());
		
		$xml->invitation->placetime->addChild('timeshift', $timeshift);

        $placesXml = $xml->invitation->addChild('places');
        $this->addPlacesAroundUser($placesXml);

		return $xml->asXML();
	}
}