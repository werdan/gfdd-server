<?php
require_once 'controllers/InvitationController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class InvitationControllerAbuseTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
       
    public function testSaveTheAbuseRequest(){
        list($boy, $girl) = $this->createBoyAndGirl();
        $invite = $this->createInvitationWithBoyAndGirl($boy, $girl);
        Zend_Registry::set('currentUser', $boy);
        $this->setTestPOSTFields(array('secretKey'=>$boy->secretKey));
        $abuse = new InvitationAbuse();
        $id = $abuse->saveTheAbuseRequest($invite->id, $boy->id, $boy->latitude, 
                $boy->longitude);
        $inv_abuse = InvitationAbuse::getById($id);
        $this->assertTrue($inv_abuse instanceof InvitationAbuse);
    }
    
    public function testThisUserAlreadyMakeAbuse(){
        list($boy, $girl) = $this->createBoyAndGirl();        
        $invite = $this->createInvitationWithBoyAndGirl($boy, $girl);
        Zend_Registry::set('currentUser', $boy);
        $this->setTestPOSTFields(array('secretKey'=>$boy->secretKey));
        $abuse = new InvitationAbuse();
        $id = $abuse->saveTheAbuseRequest($invite->id, $boy->id, $boy->latitude, 
                $boy->longitude);
        $this->dispatch('/api/invitation/abuse');    
        
        //checks response and action
        $code = $this->response->getHttpResponseCode();
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $code);
    }
    
    public function testAbuseAction(){

        list($boy, $girl) = $this->createBoyAndGirl();        
        $invitation = $this->createInvitationWithBoyAndGirl($boy, $girl);
        $boy->latitude = $invitation->Place->latitude;
        $boy->longitude = $invitation->Place->longitude;
        $boy->save();

        $this->setTestPOSTFields(array('secretKey'=> $boy->secretKey, "coordinates" => $boy->latitude . "," .$boy->longitude));
	    $this->dispatch('/api/invitation/abuse');
        //checks response and action
        $code = $this->response->getHttpResponseCode();
        $this->assertResponseCode(self::HTTP_CODE_OK, $code);
        $this->assertAction('abuse');

        //cheks failed meeting state
        $this->assertEquals(1, $invitation->abused);
        
        // checks guilty user state 
        $abuse = Zend_Abuse::getInstance(); 
        $time_restrict = $abuse->getUserTimeRestrict();
        $this->assertEquals($girl->restrictbefore, $time_restrict);
        
        $exists = (bool) InvitationAbuse::checkIfUserAbuseExists($boy->id, $invitation->id);
        $this->assertTrue($exists);
    }
}
