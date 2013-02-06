<?php
require_once 'controllers/PeopleController.php';

class PeopleControllerTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    public function testPeopleAround() {
        $lat = -43.12573;
        $long = -62.3225;

        $currentUser = $this->createNewUser("Bill", 30, "test");
        $currentUser->sex = "M";
        $currentUser->lookingFor = "F";
        $currentUser->save();

        $maleUser = $this->createNewUser("John", 18 , "sommmd");
        $maleUser->sex = "M";
        $maleUser->lookingFor = "F";
        $maleUser->latitude = $lat;
        $maleUser->longitude = $long;
        $maleUser->save();

        $marry = $this->createNewUser("Merry", 21);
        $marry->latitude = $lat;
        $marry->longitude = $long;
        $marry->save();

        $chloe = $this->createNewUser("Chloe", 32);
        $chloe->latitude = $lat;
        $chloe->longitude = $long;
        $chloe->save();

        $bisex = $this->createNewUser("Jimmy", 32);
        $bisex->sex = "M";
        $bisex->lookingFor = "MF";
        $bisex->longitude = $long;
        $bisex->latitude = $lat;
        $bisex->save();

        $this->setTestGETFields(array('coordinates' => $lat . "," . $long));
        $this->createTestUserpicAndSetFiles(240,320);

        $this->dispatch('/api/people/list');
        $this->assertResponseCode(200, $this->response->getHttpResponseCode());
        $this->assertAction('list');

        $content = $this->response->outputBody();
        $responseParsedXml = new SimpleXMLElement($content);
        $this->assertTrue(count($responseParsedXml) == 2);
        $this->assertTrue($responseParsedXml->person[count($responseParsedXml->person)-1]->age > 0);
    }

    public function testLookingForBisex() {
        $lat = -43.12573;
        $long = -62.3225;

        $currentUser = $this->createNewUser("Билли", 30, "test");
        $currentUser->sex = "M";
        $currentUser->lookingFor = "MF";
        $currentUser->save();

        $maleUser = $this->createNewUser("John", 18 , "sommmd");
        $maleUser->sex = "M";
        $maleUser->lookingFor = "F";
        $maleUser->latitude = $lat;
        $maleUser->longitude = $long;
        $maleUser->save();

        $marry = $this->createNewUser("Merry", 21);
        $marry->latitude = $lat;
        $marry->longitude = $long;
        $marry->save();

        $chloe = $this->createNewUser("Chloe", 32);
        $chloe->latitude = $lat;
        $chloe->longitude = $long;
        $chloe->save();

        $bisex = $this->createNewUser("Jimmy", 32);
        $bisex->sex = "M";
        $bisex->lookingFor = "MF";
        $bisex->latitude = $lat;
        $bisex->longitude = $long;
        $bisex->save();

        $this->setTestGETFields(array('coordinates' => $lat . "," . $long));
        $this->createTestUserpicAndSetFiles(240,320);

        $this->dispatch('/api/people/list');
        $this->assertResponseCode(200, $this->response->getHttpResponseCode());
        $this->assertAction('list');

        $content = $this->response->outputBody();
        $responseParsedXml = new SimpleXMLElement($content);
        $this->assertTrue(count($responseParsedXml->person) == 3);
        $this->assertTrue($responseParsedXml->person[count($responseParsedXml->person)-1]->age > 0);

        foreach ($responseParsedXml->person as $person) {
            $userInList = User::getById($person->id);
            $this->assertTrue(strpos($currentUser->lookingFor, $userInList->lookingFor) !== false);
        }
    }

    public function testCoordinatesProcessing() {
        $user = $this->createNewUser("Test3", 24, "test");
        $user->latitude = 15.123456;
        $user->longitude = 23.456789;
        $user->save();

        $this->setTestGETFields();
        $this->createTestUserpicAndSetFiles(240,320);

        $this->dispatch('/api/people/list');
        $this->assertResponseCode(200, $this->response->getHttpResponseCode());
        $this->assertAction('list');

        $newUser = User::getById($user->id);

        $this->assertEquals("Test3", $newUser->name);
        $this->assertEquals(md5("test"), $newUser->secretKey);
        $this->assertEquals(-45.234677, $newUser->longitude);
        $this->assertEquals(87.123679, $newUser->latitude);
    }
}
