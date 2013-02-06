<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerPlacetimeTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
  public function testPlacetimeFirstRound() {
      list($inviter, $invitee, $invitation, $places) = $this->createMockInvitationWithPlaces();

      $invitation->accepted = true;
      $invitation->save();

      $this->assertTrue($inviter instanceof User);
      $this->assertTrue($invitee instanceof User);
      $this->assertTrue($invitation instanceof Invitation);

	   $this->setTestPOSTFields(array('placeId' => $places[0]->id, 'timeshift' => 30, self::SECRET_KEY_PARAM => $inviter->secretKey));
	   $this->dispatch('/api/invitation/placetime');  
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('placetime');

       $this->assertTrue($invitation->placeId == $places[0]->id, "Checking invitation");
       $this->assertTrue($invitation->timeshift == 30, "Checking invitation");
       $timeDifference = abs($invitation->finalTime - time() - 30*60);
       $this->assertTrue($timeDifference < 2, "Delta = " . $timeDifference . ", finalTime = " . $invitation->finalTime . ", time() = " . time());
       
       $message = MessageQueue::getMessageFor($invitee->id);
       $this->assertTrue($message->messageType == "InvitationPlacetimeMessage", "Checking message");

       $xmlContent = new SimpleXMLElement($message->messageText);
       $this->assertTrue(isset($xmlContent->invitation->placetime), "Checking message");
       $this->assertTrue(count($xmlContent->invitation->places[0]) > 2, "Checking message");
       $this->assertTrue(isset($xmlContent->invitation->places[0]->place->id), "Checking message");
  }

    public function testPlacetimeTwiceSent() {
  	   $invitee = $this->createNewUser("John", 30, "test");
	   $user = $this->createNewUser("Andy", 31);
	   
	   $place = new Place();
	   $place->save();
	   
  	   $invitation = $this->linkWithInvitation($user, $invitee);
	   $invitation->accepted = true;   	   
	   $invitation->timeshift = 15;   	   
	   $invitation->timeshift = time() + 15;   	   
	   $invitation->placeId = $place->id;   	   
	   $invitation->userIdProposedTimeplace = $invitee->id;   	   
	   $invitation->save();
	   
	   
	   $this->setTestPOSTFields(array('placeId' => $place->id, 'timeshift' => 30));
	   $this->dispatch('/api/invitation/placetime');  
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('placetime');

       $this->assertTrue($invitation->placeId == $place->id);
       $this->assertTrue($invitation->timeshift == 30);
  } 
  
  
   public function testPlacetimeSecondRound() {
       list($inviter, $invitee, $invitation, $places) = $this->createMockInvitationWithPlaces();

       $invitation->accepted = true;
       $invitation->userIdProposedTimeplace = $invitee->id;
       $invitation->timeshift = 30;
       $invitation->placeId = $places[0]->id;
       $invitation->save();

       $this->assertTrue($inviter instanceof User);
       $this->assertTrue($invitee instanceof User);
       $this->assertTrue($invitation instanceof Invitation);
       $this->setTestPOSTFields(array('id' => $inviter->id,
           'secretKey'=>$inviter->secretKey,
           'placeId' => $places[1]->id,
           'timeshift' => 15));
       $this->dispatch('/api/invitation/placetime');
       $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('placetime');

       $updatedInvitation = Invitation::getById($invitation->id);
       $this->assertTrue($updatedInvitation->placeId == $places[1]->id);
       $this->assertTrue($updatedInvitation->timeshift == 15);
  } 
  
  
  public function testPlacetimeBadTimeshift() {
   	   $user = $this->createNewUser("Andy", 31, "test");
   	   $place = new Place();
	   $place->save();

	   $this->setTestPOSTFields(array('placeId' => $place->id, 'timeshift' => 'aa'));
	   $this->dispatch('/api/invitation/placetime');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testPlacetimeBadPlace() {
   	   $user = $this->createNewUser("Andy", 31, "test");
   	   $place = new Place();
	   $place->save();

	   $this->setTestPOSTFields(array('placeId' => $place->id + 1, 'timeshift' => 30));
	   $this->dispatch('/api/invitation/placetime');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testPlacetimeNotAcceptedInvitation() {
  	   $invitee = $this->createNewUser("John", 30, "test");
	   $user = $this->createNewUser("Andy", 31);
	   
  	   $invitation = $this->linkWithInvitation($user, $invitee);
  	
   	   $place = new Place();
	   $place->save();
	   
	   $this->setTestPOSTFields(array('placeId' => $place->id, 'timeshift' => 30));
	   $this->dispatch('/api/invitation/placetime');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }

    public function testPlacetimeNoActiveInvitation() {
	   $user = $this->createNewUser("Andy", 31, "test");
  	
   	   $place = new Place();
	   $place->save();
	   
	   $this->setTestPOSTFields(array('placeId' => $place->id, 'timeshift' => 30));
	   $this->dispatch('/api/invitation/placetime');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testPlacetimeGETmethod() {
       $place = new Place();
       $place->city_id = 1;
       $place->address = 'Kim Ir Sen Avenue 88';
       $place->name = 'Our lovely Kim';
       $place->save();
           
	   $this->setTestGETFields(array('placeId' => $place->id, 'timeshift' => 30));           
	   $this->dispatch('/api/invitation/placetime');  
           $code = $this->response->getHttpResponseCode();
	   $this->assertResponseCode(self::HTTP_CODE_METHOD_NOT_ALLOWED, $code);
  }
  
}
