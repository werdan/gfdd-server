<?php
include_once 'MockClient.php';

class BasicClientFlowTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

//	const HOST = "goldenfishdateanddish.com";
	const HOST = "gfdd.lxc";

	public function testIncompleteClientRegistration() {
		$client	= new MockClient(self::HOST);
		$client->registerClientIncomplete();
        $this->assertEquals(403, $client->getLastHttpCode());
		$this->assertTrue(substr_count($client->getResponseBody() , 'Parameter') == 1);

	}

    public function testPeopleAroundNoAuth() {
        $client1 = new MockClient(self::HOST);
        $client1->registerClient("F","M","-90.22,48.12");

        $client2 = new MockClient(self::HOST);

        $client2->listPeopleAround();
        $this->assertEquals(403, $client2->getLastHttpCode());

        $client2->setCoordinates("-90.22,48.12");
        $client2->listPeopleAroundNoAuth();

        $this->assertEquals(200, $client2->getLastHttpCode());
        $xml = new SimpleXMLElement($client2->getResponseBody());
        $this->assertTrue(count($xml->person) > 0 );
    }

	public function testPeopleAroundAndUserpic() {
		//100% not to have empty response
		$client	= new MockClient(self::HOST);
		$client->registerClient("F","M","-90.22,48.12");

		$client2= new MockClient(self::HOST);

        $cyrillicName = "Маша";
        $client2->setName($cyrillicName);

        $client2->registerClient("M", "F","-90.22,48.12");

        $this->assertEquals(200, $client->getLastHttpCode());

        $secretKey = $client->getSecretKey();
		$this->assertTrue(strlen($secretKey) == 32 );

		$client->listPeopleAround();
		$this->assertEquals(200, $client->getLastHttpCode());

		$xml = new SimpleXMLElement($client->getResponseBody());
		$this->assertTrue(count($xml->person) > 0 );

		$personId = $xml->person[0]->id;

        $cyrillicNameOkFlag = false;
        foreach($xml->person as $person) {
		   $this->assertTrue(strlen($xml->person->imageHash) == 0);
           if ($person->name == $cyrillicName) {
               $cyrillicNameOkFlag = true;
           }
        }
        $this->assertTrue($cyrillicNameOkFlag);

		$client->getPhoto($personId);

		$tmpFile = tempnam(sys_get_temp_dir(), 'ImageDownloaded');
		$file = fopen($tmpFile, "a+w");
		fwrite($file, $client->getResponseBody());
		fclose($file);
		$imginfo = getimagesize($tmpFile);
		$this->assertTrue(strpos($imginfo['mime'], 'jpeg') !== false);
		$this->assertTrue($imginfo[0] > 100);

        //Second request for peopleAround. This time expecting correct imageHashes
		$client->listPeopleAround();
		$xml = new SimpleXMLElement($client->getResponseBody());
        $person0ImageHash = $this->assertTrue(strlen($xml->person[0]->imageHash) == 32);

	}

	public function testBasicInvitationCycle() {
		$boy	= new MockClient(self::HOST);
		$boy->registerClient("F","M", "55.9674,37.42");

		$girl = new MockClient(self::HOST);
		$girl->registerClient("M", "F", "55.9674,37.42");

        $client3 = new MockClient(self::HOST);
        $client3->registerClient("F", "M", "55.9674,37.42");

		$boy->listPeopleAround();
		$xml = new SimpleXMLElement($boy->getResponseBody());

		$personId = 0;
		foreach ($xml->person as $person) {
			if ($person->name == $girl->getName()) {
				$personId = $person->id;
			}
            $this->assertTrue($person->sex == "M");
		}
        $this->assertTrue($personId > 0, "Client2 is not found in the list of people around");
		$boy->sendInvitationRequest($personId);

		$this->assertEquals(200, $boy->getLastHttpCode());
		$girl->getIncomingMessage();

		$messageXml = new SimpleXMLElement($girl->getResponseBody());
		$timestampCreated = $messageXml->invitation->request->time;
		$this->assertTrue($timestampCreated > 1000);
		
		//Trying to send invitation when it is not permitted

		$girl->sendInvitationRequest(12);
		$this->assertEquals(403, $girl->getLastHttpCode());

		$girl->acceptInvitation();
		$this->assertEquals(200, $girl->getLastHttpCode());

		$boy->getIncomingMessage();

		$messageXml = new SimpleXMLElement($boy->getResponseBody());

		$placeId = (int) $messageXml->invitation->accepted->places->place->id;
		$placeName = (string) $messageXml->invitation->accepted->places->place->name;
		$this->assertEquals($placeName,'Супер-ресторан');

		$boy->proposePlaceTime($placeId, 15);
		$this->assertEquals(200, $girl->getLastHttpCode());
		$girl->getIncomingMessage();

		$messageXml = new SimpleXMLElement($girl->getResponseBody());
		$this->assertTrue(isset($messageXml->invitation->placetime->place->id));
		$this->assertTrue(isset($messageXml->invitation->placetime->timeshift));

		$girl->agreeOnPlacetime();
		$this->assertEquals(200, $girl->getLastHttpCode());

		$boy->getIncomingMessage();
		$this->assertEquals(200, $boy->getLastHttpCode());
		$messageXml = new SimpleXMLElement($boy->getResponseBody());
		$time = $messageXml->invitation->agreed->time;

		$this->assertTrue($time > 1000);

		$boy->sendLate(15);
		$this->assertEquals(200, $boy->getLastHttpCode());

		$girl->getIncomingMessage();
		$messageXml = new SimpleXMLElement($girl->getResponseBody());
		$latefor = $messageXml->invitation->late->latefor;

		$this->assertTrue($latefor > 10);

        $girl->checkin();
        $this->assertEquals(200, $girl->getLastHttpCode());

        $boy->getIncomingMessage();
        $messageXml = new SimpleXMLElement($boy->getResponseBody());
        $this->assertTrue(isset($messageXml->invitation->checkedin));


        $boy->setCoordinates('12.00,45.01231');
        $boy->checkin();
        $this->assertEquals(403, $boy->getLastHttpCode());

        $boy->setCoordinates("55.9674,37.42");
        $boy->checkin();
        $this->assertEquals(200, $boy->getLastHttpCode());

        $girl->finishMeeting();
        $this->assertEquals(200, $girl->getLastHttpCode());

        $boy->finishMeeting();
        $this->assertEquals(403, $boy->getLastHttpCode());
    }

    public function testMyselfNotInPeopleAround() {
        $client	= new MockClient(self::HOST);
        $client->registerClient("F","M","-43.22,22.12");
        $client->listPeopleAround();
        $xml = new SimpleXMLElement($client->getResponseBody());
        $this->assertTrue(count($xml->person) == 0 );
    }


	public function testRejectCycle() {
		$client	= new MockClient(self::HOST);
		$client->registerClient("F","M","-90.22,48.12");

		$client2 = new MockClient(self::HOST);
		$client2->registerClient("M", "F","-90.22,48.12");

		$client->listPeopleAround();
		$xml = new SimpleXMLElement($client->getResponseBody());

		$personId = 0;
		foreach ($xml->person as $person) {
			if ($person->name == $client2->getName()) {
				$personId = $person->id;
			}
		}
		$client->sendInvitationRequest($personId);

		$client2->getIncomingMessage();
		$client2->rejectInvitation();
		$this->assertEquals(200, $client2->getLastHttpCode());
		$response = $client2->getResponseBody();
		$this->assertTrue(empty($response));

		$client2->getIncomingMessage();
		$this->assertEquals(200, $client2->getLastHttpCode());

		$messageXml = new SimpleXMLElement($client2->getResponseBody());
		$this->assertTrue($messageXml->count() == 0);

		$client->getIncomingMessage();
		$this->assertEquals(200, $client->getLastHttpCode());
		$messageXml = new SimpleXMLElement($client->getResponseBody());
		$this->assertTrue(isset($messageXml->invitation->rejected));
	}

	public function testAbuse() {
        $boy	= new MockClient(self::HOST);
        $boy->registerClient("F","M", "55.9674,37.42");

        $girl = new MockClient(self::HOST);
        $girl->registerClient("M", "F", "55.9674,37.42");

        $client3 = new MockClient(self::HOST);
        $client3->registerClient("F", "M", "55.9674,37.42");

        $boy->listPeopleAround();
        $xml = new SimpleXMLElement($boy->getResponseBody());

        $personId = 0;
        foreach ($xml->person as $person) {
            if ($person->name == $girl->getName()) {
                $personId = $person->id;
            }
            $this->assertTrue($person->sex == "M");
        }
        $boy->sendInvitationRequest($personId);
        $girl->acceptInvitation();
        $boy->getIncomingMessage();
        $messageXml = new SimpleXMLElement($boy->getResponseBody());
        $placeId = (int) $messageXml->invitation->accepted->places->place->id;
        $boy->proposePlaceTime($placeId, 15);
        $girl->agreeOnPlacetime();

        $boy->setCoordinates('12.00,45.01231');
        $boy->sendAbuse();
        $this->assertEquals(403, $boy->getLastHttpCode());
	}

	public function testEnableExtendSearchRange() {

		$client	= new MockClient(self::HOST);
		$client->registerClient("F","M","-90.22,48.12");
		$this->assertEquals(200, $client->getLastHttpCode());

		$client->enableExtendSearchRange();
		$this->assertEquals(200, $client->getLastHttpCode());
	}

	public function testGetCostOfIncreaseSearchRadius() {

		$client	= new MockClient(self::HOST);
		$client->registerClient("F","M","-90.22,48.12");
		$this->assertEquals(200, $client->getLastHttpCode());

		$client->getCostOfIncreaseSearchRadius();
		$this->assertEquals(200, $client->getLastHttpCode());

		$messageXml = new SimpleXMLElement($client->getResponseBody());

		$this->assertTrue(isset($messageXml->range->cost));
		$this->assertTrue(isset($messageXml->range->currency));
	}
}
