<?php 
require_once 'controllers/AbstractApiController.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'Longpoll/NoMessage.php';

class Api_MessageController extends AbstractApiController {

	protected function getAllowedHTTPMethod() {
		return self::HTTP_METHOD_GET;	
	}	

	public function preDispatch()
	{
		parent::preDispatch();
		$originalLastMessageId = $this->getRequest()->getParam("lastMessageId");

		if (is_null($originalLastMessageId)) {
			Logger::getLogger()->warn('Empty lastMessageId parameter');
			return;
		}
		
		$lastMessageId = (int) $originalLastMessageId;
		$messageToMarkAsRead = Message::getById($lastMessageId);
		$recipient = $this->getCurrentUser();
		
		if ($messageToMarkAsRead instanceof Message 
				&& $messageToMarkAsRead->recipientId == $recipient->id) {
			$messageToMarkAsRead->setAsRead();
		} else {
			throw new HttpReturnableException('004: Wrong lastMessageId parameter value', self::HTTP_CODE_FORBIDDEN);
		}
	}
	
	public function indexAction() {
		$currentUser = $this->getCurrentUser();
		$message = MessageQueue::getMessageFor($currentUser->id);
		if ($message instanceof Message) {
			$this->view->assign("responseXml", $message->messageText);
		} else {
			$dummyMessage = new NoMessage();
			$this->view->assign("responseXml", $dummyMessage->getXml());
		}
	}
	
}