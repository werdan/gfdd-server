<?php
class DistanceCalculatorTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    public function testDistanceToTargetInMeters() {

        /**
         * Check using http://jan.ucc.nau.edu/cvm-cgi-bin/latlongdist.pl
         */
        $user = new User();
        $user->latitude = 55.45123;
        $user->longitude = 37.37562;
        $user->extendedRange = 1;

        $place = new Place();
        $place->latitude = 55.4863;
        $place->longitude = 37.4156;

        $distanceCalc = new DistanceCalculator($user);
        $this->assertEquals(4645, $distanceCalc->getDistanceToTargetInMeters($place));
    }
}