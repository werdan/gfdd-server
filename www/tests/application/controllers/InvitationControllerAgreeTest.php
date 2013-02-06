<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerAgreeTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
  public function testAgree() {
  	   $user = $this->createNewUser("John", 32);
  	   $invitee = $this->createNewUser("Marry", 31, "test");
  	   $invitation = $this->linkWithInvitation($user, $invitee);

  	   $place = new Place();
  	   $place->save();
  	   
  	   $invitation->accepted = true;
  	   $invitation->userIdProposedTimeplace = $user->id;
  	   $invitation->Place = $place;
  	   $invitation->timeshift = 15;
  	   $invitation->finalTime = (time()-100) + 15*60; //100 sec ago time was set to "in 15 min"
  	   $lastSetFinalTime = $invitation->finalTime;
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/agree');  
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
	   
	   $this->assertTrue($invitation->agreed == true);
	   $this->assertTrue($lastSetFinalTime - $invitation->finalTime <= 300, $invitation->finalTime. " is not greater then ". $lastSetFinalTime );
	   
	   $message = MessageQueue::getMessageFor($user->id);
       $this->assertTrue($message->messageType == "InvitationAgreeMessage");
  }
  
  public function testAgreeUserProposedAgree() {
  	
  	   $user = $this->createNewUser("John", 32, "test");
  	   $invitee = $this->createNewUser("Marry", 31);
  	   $invitation = $this->linkWithInvitation($user, $invitee);

  	   $place = new Place();
  	   $place->save();
  	   
  	   $invitation->accepted = true;
  	   $invitation->userIdProposedTimeplace = $user->id;
  	   $invitation->Place = $place;
  	   $invitation->timeshift = 15;
  	   $invitation->finalTime = (time()-100) + 15*60; //100 sec ago time was set to "in 15 min"
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/agree');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }

  
  public function testAgreeNotProposedPlacetime() {
  	   $user = $this->createNewUser("John", 32);
  	   $invitee = $this->createNewUser("Marry", 31, "test");
  	   $invitation = $this->linkWithInvitation($user, $invitee);

  	   $place = new Place();
  	   $place->save();
  	   
  	   $invitation->accepted = true;
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/agree');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
	   $content = $this->getResponse()->getBody();
	   $this->assertTrue(substr_count($content, "Place and time were not proposed") == 1);
  }
}
