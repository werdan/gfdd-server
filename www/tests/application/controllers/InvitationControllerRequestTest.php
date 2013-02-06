<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerRequestTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    public function testRequestInvitation() {
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
  	   $invitee = $this->createNewUser("John", 30);  	   
	   $this->createTestUserpicAndSetFiles(240,320);
  	   
  	   $this->setTestPOSTFields(array('id' => $invitee->id));
	   $this->dispatch('/api/invitation/request');      
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('request');
       
       $requester = User::getById($requester->id);
       $this->assertTrue(!empty($requester->Invitation));
       $this->assertTrue($requester->Invitation->id > 0);
       $this->assertTrue($invitee->Invitation->id == $requester->Invitation->id);
       
       $message = MessageQueue::getMessageFor($invitee->id);
       $this->assertTrue($message instanceof Message);
       $this->assertTrue($message->recipientId == $invitee->id);
       $this->assertTrue($message->senderId == $requester->id);
       $this->assertTrue($message->messageType == "InvitationRequestMessage");
       $this->assertNotNull($message->createdAtTimestamp);
       $this->assertNotNull($message->messageType);
       $messageText = $message->messageText;
       $messageXml = new SimpleXMLElement($messageText);
       $this->assertTrue($messageXml->invitation->request->person->id == $requester->id);
       $this->assertTrue($messageXml->invitation->request->person->name == $requester->name);
       $this->assertTrue($messageXml->invitation->request->person->sex == $requester->sex);
       $this->assertTrue($messageXml->invitation->request->person->age == $requester->age);
  }
  
  public function testRequestInviteHimself() {
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
	   $this->createTestUserpicAndSetFiles(240,320);

  	   $this->setTestPOSTFields(array('id' => $requester->id));
	   $this->dispatch('/api/invitation/request');      
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }

  public function testRequestDoubleInvitationSending() {
  	   $invitee = $this->createNewUser("John", 30);  	   
  	   $newUser = $this->createNewUser("Bill", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
  	   $this->linkWithInvitation($requester, $invitee);

  	   $this->setTestPOSTFields(array('id' => $newUser->id));
	   $this->dispatch('/api/invitation/request');      
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }  
  
  public function testRequestInviteeHasBeenAlreadyInvited() {
  	   $invitation = new Invitation();
  	   $invitee = $this->createNewUser("John", 30);
	   $thirdUser = $this->createNewUser("Andy", 31);
	   $this->linkWithInvitation($thirdUser, $invitee);
	   
  	   $requester = $this->createNewUser("Marry2", 24, "test");  	   
	   $this->createTestUserpicAndSetFiles(240,320);

  	   $this->setTestPOSTFields(array('id' => $invitee->id));
	   $this->dispatch('/api/invitation/request');      
	   $this->assertResponseCode(self::HTTP_CODE_CONFLICT, $this->response->getHttpResponseCode());
  }
  
  public function testRequestNoSuchUser() {
  	   $user = User::getById(33);
  	   $this->assertTrue(empty($user));
  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
	   $this->createTestUserpicAndSetFiles(240,320);
  	   
  	   $this->setTestPOSTFields(array('id' => 33));
	   $this->dispatch('/api/invitation/request');      
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testRequestNoInvitee() {
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
  	
  	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/request');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
       $this->assertAction('error');
  	
  }
}
