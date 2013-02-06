<?php
/**
 * Push messages into apple server using ApnsPHP library
 */

// Using Autoload all classes are loaded on-demand
require_once 'ApnsPHP/Autoload.php';

class PushAppleService{

    /**
     * Push library object
     *
     * @var ApnsPHP_Push 
     */
    protected $_push = null;
    
    /**
     * Set and parametrize message object
     *
     * @var ApnsPHP_Message 
     */
    protected $_message = null;
    
    public function __construct(){
       $this->connect();
    } 
    
    /**
     * Procedure connecting 
     */
    protected function connect(){
        $this->_push = new ApnsPHP_Push(
	ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
	'server_cerificates_bundle_sandbox.pem');
        $this->_push->setRootCertificationAuthority('entrust_root_certification_authority.pem');

        // Connect to the Apple Push Notification Service
        $this->_push->connect();
    }
    
    /**
     * Set the message to push onto the server
     *
     * @param mixed $msg 
     */
    public function setMessage($msg){
        // Instantiate a new Message with a single recipient
        $this->_message = new ApnsPHP_Message('1e82db91c7ceddd72bf33d74ae052ac9c84a065b35148ac401388843106a7485');

        // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
        // over a ApnsPHP_Message object retrieved with the getErrors() message.
        $this->_message->setCustomIdentifier("Message-Badge-3");

        // Set badge icon to "3"
        $this->_message->setBadge(3);

        // Set a simple welcome text
        $this->_message->setText('Hello!');

        // Play the default sound
        $this->_message->setSound();

        // Set a custom property
        $this->_message->setCustomProperty('acme2', array('bang', 'whiz'));

        // Set another custom property
        $this->_message->setCustomProperty('acme3', array('bing', 'bong'));

        // Set the expiry value to 30 seconds
        $this->_message->setExpiry(30);
        
        return $this;
    }
    
    /**
     * Add message into queue 
     */
    public function addIntoQueue(){
        // Add the message to the message queue
        $this->_push->add($this->_message);        
        return $this;
    }
    
    /**
     * Send the message 
     */
    public final function send(){
        // Send all messages in the message queue
        $this->_push->send();
    }
    
    /**
     * Send the single message without queue 
     */
    public final function sendSingle(){
        $this->_push->add($this->_message);
        $this->_push->send();
    }
}
