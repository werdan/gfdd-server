<?php
class MessageQueue {
	
	public static function getMessageFor($userId) {
		$q  = Doctrine_Query::create()
             ->from('Message m')
             ->addWhere('m.recipientId= ?',$userId)
             ->addWhere('m.readTimestamp IS NULL')
             ->orderBy('m.createdAtTimestamp ASC');
        Logger::getSQLLogger()->info($q->getSqlQuery());
		$message = $q->fetchOne();
		
		if ($message instanceof Message) {
			$messageTextXml = new SimpleXMLElement($message->messageText);
    		$messageTextXml->addChild('messageId', $message->id);
    		$message->messageText = $messageTextXml->asXML();
		}
		return $message;
 	}
	
	public static function markAsReadAllUnreadMessagesBetweenUsers($user1, $user2) {
		Logger::getLogger()->info("Preparing for marking as read all unread messages between $user1->id and $user2->id");
		
		$params = array($user1->id, $user2->id, $user1->id, $user2->id);
		
		$q = Doctrine_Query::create()
    		->update('Message m')
    		->set('m.readTimestamp', time())
    		->where('m.readTimestamp IS NULL')
    		->andWhere('m.senderId = ? OR m.senderId = ? OR m.recipientId = ? OR m.recipientId = ?', $params);
        Logger::getSQLLogger()->info($q->getSqlQuery());
        $q->execute();
        Logger::getLogger()->info("Successfully marked as read all unread messages between $user1->id and $user2->id");
	}
}