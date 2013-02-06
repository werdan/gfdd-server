<?php
class DistanceCalculator{
    
    const METERS_IN_SECOND_ON_EQUATOR = 30.9;
    const SECONDS_IN_DEGREE = 3600;

    private $sourceUser;
    
    public function DistanceCalculator($user) {
        bcscale(6);
        $this->sourceUser = $user;
    }

    //TODO: is not working for transequator/0-meridian locations
    public function getDistanceToTargetInMeters($target){
        $deltaLatitudeDegrees = abs($this->sourceUser->latitude - $target->latitude);
        $deltaLatitudeMeters = $this->convertMeridianSecondsToMeters(bcmul($deltaLatitudeDegrees, self::SECONDS_IN_DEGREE));
        $deltaLongitudeDegrees = abs($this->sourceUser->longitude - $target->longitude);
        $deltaLongitudeMeters = $this->convertParallelSecondsToMeters(bcmul($deltaLongitudeDegrees, self::SECONDS_IN_DEGREE));
        $sum_pow = abs(bcpow($deltaLatitudeMeters, 2) + bcpow($deltaLongitudeMeters, 2));
        return round(bcsqrt($sum_pow),0);
    }

    public function getSearchAreaAroundUser()
    {
        $r = Zend_Registry::get('appConfig');
        $defaultRadiusUserAround = $r['defaultRadiusUserAround'];
        $extendedRadiusUserAround = $r['extendedRadiusUserAround'];

        $radius = (!empty($this->sourceUser->extendedRange)) ? $extendedRadiusUserAround : $defaultRadiusUserAround;
        $area = array();

        $area['top_left']['lat'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->latitude) + $radius);
        $area['top_left']['long'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->longitude) - $radius);

        $area['bottom_left']['lat'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->latitude) - $radius);
        $area['bottom_left']['long'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->longitude) - $radius);

        $area['top_right']['lat'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->latitude) + $radius);
        $area['top_right']['long'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->longitude) + $radius);

        $area['bottom_right']['lat'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->latitude) - $radius);
        $area['bottom_right']['long'] = $this->convertToPolar($this->convertToDecart($this->sourceUser->longitude) + $radius);

        return $area;
    }


    public function getOverlapArea($area1, $area2) {
        $pointsToCompare = array();
        foreach(array_merge($area1,$area2) as $point) {
            $pointsToCompare[] = $point;
        }
        $maxLatitude = self::getMaxCoordinate($pointsToCompare,'lat');
        $minLatitude = self::getMinCoordinate($pointsToCompare,'lat');

        $maxLongitude = self::getMaxCoordinate($pointsToCompare,'long');
        $minLongitude = self::getMinCoordinate($pointsToCompare,'long');

        $overlapArea = array();
        $overlapArea['top_left'] = array('lat'=>$maxLatitude, 'long'=>$minLongitude);
        $overlapArea['top_right'] = array('lat'=>$maxLatitude, 'long'=>$maxLongitude);
        $overlapArea['bottom_left'] = array('lat'=>$minLatitude, 'long'=>$minLongitude);
        $overlapArea['bottom_right'] = array('lat'=>$minLatitude, 'long'=>$maxLongitude);

        return $overlapArea;
    }

    private function getMetersInSecond(){
        $latitudeRadians = $this->convertDegreesToRadians($this->sourceUser->latitude);
        return abs(bcmul(self::METERS_IN_SECOND_ON_EQUATOR,cos(abs($latitudeRadians))));
    }

    private function convertDegreesToRadians($degree) {
        return bcmul(bcdiv($degree, 360), 2* M_PI);
    }

    private function convertMeridianSecondsToMeters($seconds) {
        return bcmul($seconds, self::METERS_IN_SECOND_ON_EQUATOR);
    }
    
    private function convertParallelSecondsToMeters($seconds) {
        return bcmul($seconds, $this->getMetersInSecond());
    }

    private function getMaxCoordinate($points, $coordinateIndex) {
        return max(self::mergeCoordinatesByIndex($points, $coordinateIndex));
    }

    private function getMinCoordinate($points, $coordinateIndex) {
        return min(self::mergeCoordinatesByIndex($points, $coordinateIndex));
    }

    private function mergeCoordinatesByIndex($points, $coordinateIndex) {
        $coordinatesToCompare = array();
        foreach ($points as $point) {
            $coordinatesToCompare[] = $point[$coordinateIndex];
        }
        return $coordinatesToCompare;
    }

    private function convertToDecart( $coordinate) {
        return $coordinate + 90;
    }

    private function convertToPolar($coordinate) {
        return $coordinate - 90;
    }
}
