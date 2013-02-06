<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerLateTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

  public function testLate() {
  	   $user = $this->createNewUser("John", 32);
  	   $invitee = $this->createNewUser("Marry", 31, "test");
  	   $invitation = $this->linkWithInvitation($user, $invitee);
  	   $invitation->agreed = true;
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields(array('latefor' => 15, self::SECRET_KEY_PARAM => $invitee->secretKey));
  	   $this->dispatch('/api/invitation/late');  
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());

       $updatedInvitation = Invitation::getById($invitation->id);
       $this->assertFalse($updatedInvitation->inviterIsLate ? true : false);
       $this->assertTrue($updatedInvitation->inviteeIsLate ? true : false, "InviteeIsLate not true");

	   $message = MessageQueue::getMessageFor($user->id);
       $this->assertTrue($message->messageType == "InvitationLateMessage");
	   $xmlMessage = new SimpleXMLElement($message->messageText);
	   $this->assertTrue($xmlMessage->invitation->late->latefor > 10);	   
  }
  
  public function testLateNoInvitation() {
  	   $user = $this->createNewUser("John", 32, "test");
  	   
	   $this->setTestPOSTFields(array('latefor' => 15));
	   $this->dispatch('/api/invitation/late');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testLateIncorrectLateforParam() {
  	   $user = $this->createNewUser("John", 32);
  	   $invitee = $this->createNewUser("Marry", 31, "test");
  	   $invitation = $this->linkWithInvitation($user, $invitee);
  	   $invitation->agreed = true;
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields(array('latefor' => 150));
  	   $this->dispatch('/api/invitation/late');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testLateBeforeAgreed() {
  	   $user = $this->createNewUser("John", 32);
  	   $invitee = $this->createNewUser("Marry", 31, "test");
  	   $invitation = $this->linkWithInvitation($user, $invitee);
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields(array('latefor' => 15));
  	   $this->dispatch('/api/invitation/late');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testLateAlreadySent_Inviter() {
  	   $user = $this->createNewUser("John", 32, "test");
  	   $invitee = $this->createNewUser("Marry", 31);
  	   $invitation = $this->linkWithInvitation($user, $invitee);
  	   $invitation->agreed = true;
  	   $invitation->inviterIsLate = true;
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields(array('latefor' => 15));
	   $this->dispatch('/api/invitation/late');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }

  public function testLateAlreadySent_Invitee() {
  	   $user = $this->createNewUser("John", 32);
  	   $invitee = $this->createNewUser("Marry", 31, "test");
  	   $invitation = $this->linkWithInvitation($user, $invitee);
  	   $invitation->agreed = true;
  	   $invitation->inviteeIsLate = true;
  	   $invitation->save();
  	   
	   $this->setTestPOSTFields(array('latefor' => 15));
	   $this->dispatch('/api/invitation/late');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }

}  
