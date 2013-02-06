<?php
require_once 'controllers/MessageController.php';
require_once 'Longpoll/NoMessage.php';

class MessageControllerTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
	
  public function testSimpleMessagePoll() {
  	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
	   $messageText="<?xml version=\"1.0\"?><message><messageId>123</messageId><invitation><rejected/></invitation></message>";
  	
  	   $this->createMessage(111, $requester->id, time()-2);
  	   $this->createMessage(21, $requester->id, time()-3);
  	   $this->createMessage($requester->id, $invitee->id, time()-6, "test1", "InvitationRejectMessage");

  	   $this->createMessage($requester->id, $invitee->id, time()-10, $messageText, "InvitationRejectMessage");
  	   $this->createMessage(333, 444, time()-100, "wrongmessage", "InvitationRejectMessage");
  	   
  	   $m = $this->createMessage($requester->id, $invitee->id, time()-500, "messageTest2", "InvitationRejectMessage");
  	   $m->readTimestamp = $m->createdAtTimestamp - 400;
  	   $m->save();
  	   
  	   $this->setTestGETFields();
  	   
	   $this->dispatch('/api/message/');      
       $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $this->assertAction('index');
       $xmlContent = new SimpleXMLElement($this->response->outputBody());
	   $this->assertTrue(intval($xmlContent->messageId) > 0);
	   $this->assertTrue(isset($xmlContent->invitation->rejected));
  }
  
  public function testAgreeMessage() {
  	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   
  	   
  	   $m = new InvitationAgreeMessage($requester, $invitee);
  	   $this->createMessage($requester->id, $invitee->id, time()-100, $m->getXml(), "InvitationAgreeessage");

  	   $this->setTestGETFields();
	   $this->dispatch('/api/message/');      
  	   $xmlContent = new SimpleXMLElement($this->response->outputBody());
       $this->assertTrue(isset($xmlContent->invitation->agreed->time));
  	
  }
  
  public function testCorrectLastMessageId() {
 	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   

  	   $this->createMessage($requester->id, $invitee->id, time()-2, "<message>4</message>", "InvitationRejectMessage");
  	   $this->createMessage($requester->id, $invitee->id, time()-10, "<message>3</message>", "InvitationRejectMessage");
  	   $this->createMessage($requester->id, $invitee->id, time()-40, "<message><test>2</test></message>", "InvitationRejectMessage");
  	   $m = $this->createMessage($requester->id, $invitee->id, time()-100, "<message>1</message>", "InvitationRejectMessage");
	   $this->assertTrue(is_null($m->readTimestamp));
  	
  	   $this->setTestGETFields(array('lastMessageId' => $m->id));
	   $this->dispatch('/api/message/');      

	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
       $content = $this->response->outputBody();
       $xmlContent = new SimpleXMLElement($content);
	   $this->assertTrue($xmlContent->test == "2");      
	   
	   $changedMessage = Message::getById($m->id);
	   $this->assertTrue($changedMessage->readTimestamp > 0);
  }

    public function testIncorrectLastMessageId() {
 	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   

  	   $this->createMessage($requester->id, $invitee->id, time()-2, "test4", "InvitationRejectMessage");
  	   $this->createMessage($requester->id, $invitee->id, time()-10, "test3", "InvitationRejectMessage");
  	   $this->createMessage($requester->id, $invitee->id, time()-40, "test2", "InvitationRejectMessage");
  	   $m = $this->createMessage($requester->id, $invitee->id, time()-100, "test1", "InvitationRejectMessage");
  	
  	   $this->setTestGETFields(array('lastMessageId' => 123123));
	   $this->dispatch('/api/message/');      
	   $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
    }
  
  
  public function testTwoRequestsWithTheSameLastMessageId() {
 	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   

  	   $m = $this->createMessage($requester->id, $invitee->id, time()-2, "test4", "InvitationRejectMessage");
  	   $m->readTimestamp = time();
  	   $m->save();
  	   
  	   $this->setTestGETFields(array('lastMessageId' => $m->id));
	   $this->dispatch('/api/message/');      

	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
  	   
  }
  
  public function testNoMessageToSend_Simple() {
	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   

  	   $this->setTestGETFields();
  	   $this->dispatch('/api/message/');      
  	   
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
   	   $content = $this->response->outputBody();
   	   $noMessage = new NoMessage();
   	   $emptyMessage = $noMessage->getXml();
  	   $this->assertTrue($content == $emptyMessage);
  }

  public function testNoMessageToSend_LastMessageId() {
	   $invitee = $this->createNewUser("John", 30);  	   
  	   $requester = $this->createNewUser("Marry", 24, "test");  	   

  	   $m = $this->createMessage($requester->id, $invitee->id, time()-2, "test4", "InvitationRejectMessage");
  	   $m->readTimestamp = time();
  	   $m->save();

  	   $this->setTestGETFields(array('lastMessageId' => $m->id));
  	   $this->dispatch('/api/message/');      
  	   
	   $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
   	   $content = $this->response->outputBody();
   	   $noMessage = new NoMessage();
   	   $emptyMessage = $noMessage->getXml();
  	   $this->assertTrue($content == $emptyMessage);
  }

  private function createMessage($recipientId, $senderId, $createdAt = null, $messageText = null, $messageType =null) {
  	   $m = new Message();
  	   $m->recipientId = $recipientId;
  	   $m->senderId = $senderId; 
  	   $m->createdAtTimestamp = $createdAt;
  	   $m->messageText = $messageText;
	   $m->messageType = $messageType;
	   $m->save();
	   return $m;
  }
  
} 