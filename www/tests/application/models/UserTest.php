<?php

class UserTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    /**
     * Tests finding users around in given area
     */
    public function testUsersAround()
    {
        $latitute = 52.123456;
        $longtitude = 52.123456;
        $user = $this->createNewBoy('test',12, $longtitude, $latitute);
        $user->save();
        $myId = $user->id;

        $distanceCalc = new DistanceCalculator($user);
        $area = $distanceCalc->getSearchAreaAroundUser();

        $latituteAllowedDelta = $area['bottom_right']['lat']-$area['top_left']['lat'];
        $longtituteAllowedDelta = $area['top_left']['long']-$area['bottom_right']['long'];
        $randomDivisor = rand(1,4);

        $user2 = $this->createNewGirl('testa',13,
                                     $area['top_left']['lat'] + $latituteAllowedDelta/$randomDivisor,
                                     $area['bottom_right']['long'] + $longtituteAllowedDelta/$randomDivisor);
        $user2 ->save();

        $randomDivisor = rand(1,4);
        $user3 = $this->createNewGirl('testa',13,
                                    $area['top_left']['lat'] + $latituteAllowedDelta/$randomDivisor,
                                    $area['bottom_right']['long'] + $longtituteAllowedDelta/$randomDivisor);
        $user3 ->save();

        $randomDivisor = rand(1,4);
        $user4 = $this->createNewGirl('testa',13,
                                    $area['top_left']['lat'] + $latituteAllowedDelta/$randomDivisor,
                                    $area['bottom_right']['long'] + $longtituteAllowedDelta/$randomDivisor);
        $user4 ->save();

        $user5 = $this->createNewGirl('expired',13,
                                    $area['top_left']['lat'] + $latituteAllowedDelta/$randomDivisor,
                                    $area['bottom_right']['long'] + $longtituteAllowedDelta/$randomDivisor);
        $user5->lastRequestTimeStamp = 1000;
        $user5 ->save();


        $users_near = $user->getPeopleAroundLookedFor();
        $this->assertTrue(count($users_near) == 3, "Expecting 3 users around, got " . count($users_near));
        foreach($users_near as $userUnderTest) {
            $this->assertTrue($userUnderTest->id != $myId);
        }
    }
}
