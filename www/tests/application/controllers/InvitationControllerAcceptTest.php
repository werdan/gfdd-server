<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerAcceptTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
  public function testAcceptInvitation() {
        list($user, $invitee, $invitation, $places) = $this->createMockInvitationWithPlaces();
        $invitation->accepted = 0;
        $invitation->save();

        $dCalc = new DistanceCalculator($invitee);
        $this->createPlacesSetInsideArea($dCalc->getSearchAreaAroundUser());

        $this->setTestPOSTFields(array(self::SECRET_KEY_PARAM => $invitee->secretKey));
        $this->dispatch('/api/invitation/accept');
        $code = $this->response->getHttpResponseCode();
        $this->assertResponseCode(self::HTTP_CODE_OK, $code);
        $this->assertAction('accept');
       
        $this->assertTrue($user->Invitation->accepted ? true : false);
        $message = MessageQueue::getMessageFor($user->id);
        $this->assertTrue($message->messageType == "InvitationAcceptMessage");

        $messageXml = new SimpleXMLElement($message->messageText);

        $this->assertTrue(count($messageXml->invitation->accepted->places[0]) > 2);
  }

    public function testAcceptInvitationNoPlacesToPropose() {
        $invitee = $this->createNewUser("John", 30, "test");
        $user = $this->createNewUser("Andy", 31);
        $this->linkWithInvitation($user, $invitee);

        $this->setTestPOSTFields(array(self::SECRET_KEY_PARAM => $invitee->secretKey));
        $this->dispatch('/api/invitation/accept');
        $code = $this->response->getHttpResponseCode();
        $this->assertResponseCode(self::HTTP_CODE_OK, $code);
        $this->assertAction('accept');

        $this->assertTrue($user->Invitation->accepted);
        $message = MessageQueue::getMessageFor($user->id);
        $this->assertTrue($message->messageType == "InvitationAcceptMessage");

        $messageXml = new SimpleXMLElement($message->messageText);
        $this->assertTrue(count($messageXml->invitation->accepted->places[0]) == 0);
    }


  public function testDoubleAcceptInvitation() {
  	   $invitee = $this->createNewUser("John", 30, "test");
	   $user = $this->createNewUser("Andy", 31);
	   $invitation = $this->linkWithInvitation($user, $invitee);
	   $invitation->accepted = true;
	   $invitation->save();
	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/accept');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testAcceptNotExistentInvitation() {
  	   $user = $this->createNewUser("John", 30, "test");
	   
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/accept');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }
  
  public function testAcceptByCreator() {
  	   $invitee = $this->createNewUser("John", 30);
	   $user = $this->createNewUser("Andy", 31, "test");
	   $this->linkWithInvitation($user, $invitee);
	   $this->setTestPOSTFields();
	   $this->dispatch('/api/invitation/accept');  
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
  }

}
