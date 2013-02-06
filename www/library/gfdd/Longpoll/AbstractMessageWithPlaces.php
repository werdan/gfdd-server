<?php

abstract class AbstractMessageWithPlaces extends AbstractMessage {

    protected function addPlacesAroundUser($simpleXmlObj) {
        $sender = $this->getSender();
        $distanceCalc = new DistanceCalculator($sender);
        $recipient = $this->getRecipient();
        $distanceCalcPair = new DistanceCalculator($recipient);

        $senderArea = $distanceCalc->getSearchAreaAroundUser();
        $recipientArea = $distanceCalcPair->getSearchAreaAroundUser();
        $overlapArea = $distanceCalc->getOverlapArea($senderArea,$recipientArea);

        $places = Place::getPlacesInArea($overlapArea);

        foreach($places as $place) {
            Logger::getLogger()->debug("Found place: " . $place->name);
            $placeXml = $simpleXmlObj->addChild('place');
            $placeXml->addChild('id', $place->id);
            $placeXml->addChild('name', $place->name);
            $placeXml->addChild('distance', $distanceCalc->getDistanceToTargetInMeters($place));
            if ($place->selected) {
                $placeXml->addChild('selected');
            }
            $placeXml->addChild('longitude', $place->longitude);
            $placeXml->addChild('latitude', $place->latitude);
            $placeXml->addChild('address', $place->getAddressLine());
        }
    }
}