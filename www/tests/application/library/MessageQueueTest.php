<?php
class MessageQueueTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
 
  public function testDeleteBetween() {
  	 $user1 = new User();
  	 $user1->id = 1;
  	 
  	 $user2 = new User();
  	 $user2->id = 2;

  	 $m = new InvitationRequestMessage($user1, $user2);
  	 $m->save();
  	 $veryOldMessageId = $m->getMessage()->id; 
  	 $veryOldMessage = Message::getById($veryOldMessageId);
  	 $veryOldMessage->readTimestamp = time();
  	 $veryOldMessage->save();
  	 
  	 $m = new InvitationRequestMessage($user1, $user2);
  	 $m->save();
  	 $oldMessageId = $m->getMessage()->id; 
  	 
  	 MessageQueue::markAsReadAllUnreadMessagesBetweenUsers($user1,$user2);
  	 $m = new InvitationRejectMessage($user2, $user1);
  	 $m->save();

  	 $this->assertTrue($m->getMessage()->id != $oldMessageId);
  	 
  	 unset($veryOldMessage);
  	 $veryOldMessage = Message::getById($veryOldMessageId);
  	 $oldMessage = Message::getById($oldMessageId);
	 
  	 $this->assertTrue(!empty($veryOldMessage));
  	 $this->assertTrue($oldMessage->readTimestamp > 1000);
  }
   
} 