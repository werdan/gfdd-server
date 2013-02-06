<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerFinishedTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
    public function testFinishedInvitationSentByNotCheckedInUser(){
        list($boy, $girl, $invitation) = $this->createInvitationKit();
        $invitation->inviterCheckedIn = 0;

        $this->getRequest()->setParam(self::SECRET_KEY_PARAM, $boy->secretKey);
        $this->dispatch('/api/invitation/finished');
        $code = $this->getResponse()->getHttpResponseCode();

        $this->assertEquals(self::HTTP_CODE_FORBIDDEN, $code);
    }

    public function testFinishedInvitation(){
        list($boy, $girl, $invitation) = $this->createInvitationKit();
        $invitation->inviterCheckedIn = true;
        $invitation->save();
        $this->assertFalse($invitation->finished ? true : false);

        $this->getRequest()->setParam(self::SECRET_KEY_PARAM, $boy->secretKey);
        $this->dispatch('/api/invitation/finished');
        $code = $this->getResponse()->getHttpResponseCode();

        $updatedInvitation = Invitation::getById($invitation->id);
        $updateBoy = User::getById($boy->id);
        $updateGirl = User::getById($girl->id);

        $this->assertAction('finished');
        $this->assertEquals(self::HTTP_CODE_OK, $code);

        $this->assertEquals(null, $updateBoy->Invitation);
        $this->assertEquals(null, $updateGirl->Invitation);
        $this->assertTrue($updatedInvitation->finished ? true : false);

        $message = MessageQueue::getMessageFor($girl->id);
        $this->assertTrue($message instanceof Message);
        $this->assertTrue($message->recipientId == $girl->id);
        $this->assertTrue($message->senderId == $boy->id);
        $this->assertTrue($message->messageType == "InvitationFinishedMessage");
        $this->assertNotNull($message->createdAtTimestamp);
        $this->assertNotNull($message->messageType);

        $messageText = $message->messageText;
        $messageXml = new SimpleXMLElement($messageText);
        $this->assertNotNull($messageXml->invitation->finished);

    }


    public function testDoubleFinishedInvitation(){
        list($boy, $girl, $invitation) = $this->createInvitationKit();
        $invitation->inviterCheckedIn = true;
        $invitation->save();
        $this->assertFalse($invitation->finished ? true : false);

        $this->getRequest()->setParam(self::SECRET_KEY_PARAM, $boy->secretKey);
        $this->dispatch('/api/invitation/finished');
        $code = $this->getResponse()->getHttpResponseCode();
        $this->assertEquals(self::HTTP_CODE_OK, $code);

        $this->getRequest()->setParam(self::SECRET_KEY_PARAM, $girl->secretKey);
        $this->dispatch('/api/invitation/finished');
        $code = $this->getResponse()->getHttpResponseCode();
        $this->assertEquals(self::HTTP_CODE_FORBIDDEN, $code);
    }
}
