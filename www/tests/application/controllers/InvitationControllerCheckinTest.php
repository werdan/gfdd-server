<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerCheckinTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
    public function testCheckinIncorrectCoordinates(){
        list($boy, $girl, $invitation) = $this->createInvitationKit();
        $latitude = $invitation->Place->latitude;
        $longitude = $invitation->Place->longitude;

        $coordinates = sprintf('%s,%s',
                $latitude + 20,
                $longitude + 20);

        $this->setTestPOSTFields(array('coordinates' => $coordinates,
                                        self::SECRET_KEY_PARAM => $boy->secretKey));
        $this->dispatch('/api/invitation/checkin');
        $code = $this->response->getHttpResponseCode();
        $this->assertEquals(self::HTTP_CODE_FORBIDDEN, $code);
    }
    
    public function testCheckin(){      
        list($boy, $girl, $invitation) = $this->createInvitationKit();
        $lat = $invitation->Place->latitude;
        $long = $invitation->Place->longitude;
        $coordinates = sprintf('%s,%s', $lat, $long);
        $boy->latitude = $lat;
        $boy->longitude = $long;
        $boy->save();

        $this->setTestPOSTFields(array('coordinates' => $coordinates,
                                 self::SECRET_KEY_PARAM => $boy->secretKey));

        $this->dispatch('/api/invitation/checkin');
        $code = $this->response->getHttpResponseCode();
        $this->assertEquals(self::HTTP_CODE_OK, $code);

        $updatedInvitation = Invitation::getById($invitation->id);
        $this->assertTrue($updatedInvitation->inviterCheckedIn ? true : false);
        $this->assertFalse($updatedInvitation->inviteeCheckedIn ? true : false);

        //Second user checkins

        $this->setTestPOSTFields(array('coordinates' => $coordinates,
            self::SECRET_KEY_PARAM => $girl->secretKey));

        $this->dispatch('/api/invitation/checkin');
        $code = $this->response->getHttpResponseCode();
        $this->assertEquals(self::HTTP_CODE_OK, $code);

        $updatedInvitation = Invitation::getById($invitation->id);
        $this->assertTrue($updatedInvitation->inviterCheckedIn ? true : false);
        $this->assertTrue($updatedInvitation->inviteeCheckedIn ? true : false);

        $message = MessageQueue::getMessageFor($boy->id);
        $this->assertTrue($message->messageType == "InvitationCheckinMessage");

    }
}
