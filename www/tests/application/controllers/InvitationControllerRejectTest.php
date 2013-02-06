<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerRejectTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
  public function testRejectInvitation() {
  	   $invitee = $this->createNewUser("John", 30, "test");
	   $user = $this->createNewUser("Andy", 31);

	   $invitation = $this->linkWithInvitation($user, $invitee);
	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/reject');  
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('reject');

       $user = User::getById($user->id);
       $invitee = User::getById($invitee->id);
       $this->assertTrue(empty($user->Invitation), "Inviter is linked to Invitation");
       $this->assertTrue(empty($invitee->Invitation), "Invitee is linked to Invitation");
       $this->assertTrue($invitation->rejected, "Not rejected");
       
       $message = MessageQueue::getMessageFor($user->id);
       $this->assertTrue($message->messageType == "InvitationRejectMessage");
       $messageXml = new SimpleXMLElement($message->messageText);
       $this->assertTrue(isset($messageXml->invitation->rejected));
  }
    
  public function testRejectNotExistentInvitation() {
  	   $user = $this->createNewUser("John", 30, "test");
	   $user->save();
	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/reject');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testRejectByCreator() {
  	   $invitee = $this->createNewUser("John", 30);
	   $user = $this->createNewUser("Andy", 31, "test");

	   $invitation = $this->linkWithInvitation($user, $invitee);
	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/reject');  
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('reject');       
       
       $user = User::getById($user->id);
       $invitee = User::getById($invitee->id);

       
       $this->assertTrue($invitation->rejected, "Not rejected");
       $this->assertTrue(empty($user->Invitation), "Inviter is linked to Invitation");
       $this->assertTrue(empty($invitee->Invitation), "Invitee is linked to Invitation");
    }
}
