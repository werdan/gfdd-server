<?php
abstract class AbstractMessage {
	
	protected $sender;
	protected $recipient;
	protected $timestamp;
	protected $message;
	
	abstract public function getXml();

	public function getType() {
		return get_class($this);
	}

	public function __construct($sender, $recipient) {
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->timestamp = time();
	}
	
	
	public function save() {
		$message = new Message();

        //Special workarround for system messages, which don't have senders
        $message->senderId = $this->getSender()->id;
        $message->recipientId = $this->getRecipient()->id;
		$message->messageText = $this->getXml();
		$message->createdAtTimestamp = $this->timestamp;
		$message->messageType = $this->getType();
		$message->save();
		$this->message = $message;
	}
	
	protected function getEmptyXml() {
		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><message />");
        return $xml;
	}

	public function getMessage() {
		return $this->message;
	}
	
	public function getSender() {
		return $this->sender;
	}

	public function getRecipient() {
		return $this->recipient;
	}
}