<?php
/**
 * Application abuse logics
 *
 * @author Kudryashov Sergey iden.82@gmail.com
 */
class Zend_Abuse{
    
    /**
     * Object instance
     *
     * @var object
     */
    private static $_instance = null;
    
    /**
     * Invite layer
     *
     * @var object 
     */
    private $_invitee;
    
    /**
     * User abuse time restrict
     *
     * @var int 
     */
    private $_restrict_before = null;
        
    /**
     * Max user abuse count before he would blocked per month
     */
    const MAX_COUNT_ABUSE_BEFORE_TIMEWISE_BLOCK = 1;
    
    /**
     * Max user abuse count before he would block forever
     */
    const MAX_COUNT_ABUSE_BEFORE_END_BLOCK = 2;
    
    /**
     * Abuse block time
     */
    const TIME_LENGTH_WHILE_BLOCKED = 2592000;
    
    /**
     * Blocked forever (about a 100 years)
     */
    const TIME_LENGTH_FOREVER_BLOCKED = 31536000000;
    
    
    private function __construct() {
        $this->_invitee = new Invitation();        
    }
    
    /**
     * Return an instance of abuse object
     *
     * @return Application_Abuse
     */
    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new Zend_Abuse();
            return self::$_instance;
        }else{
            return self::$_instance;
        }
    }
    
    /**
     * Perform a user restrictions when he abuse invitation
     *
     * @param int $userid 
     */
    public function performRestrictionActionByUser($userid){        
        $count = $this->_invitee->getCountAbusedInvitationsByUser($userid);        
        $time = $this->defineUserRestriсtionTime($count);
        $this->restrictUser($userid, $time);            
    }
    
    /**
     * Define user restriction time
     *
     * @param int $count
     * @return int 
     */
    public function defineUserRestriсtionTime($count)
    {
        //TODO: вообще говоря если логика будет усложняться, здесь имеет смысл применить
        // Strategy Pattern. Но пока так        
        $time = time();
        if($count == self::MAX_COUNT_ABUSE_BEFORE_TIMEWISE_BLOCK){
            $this->_restrict_before = $time + self::TIME_LENGTH_WHILE_BLOCKED;
            return $this->_restrict_before;
        }elseif($count > self::MAX_COUNT_ABUSE_BEFORE_TIMEWISE_BLOCK 
                    && $count >= MAX_COUNT_ABUSE_BEFORE_END_BLOCK){
            $this->_restrict_before = $time + self::TIME_LENGTH_FOREVER_BLOCKED;
            return $this->_restrict_before;
        }
    }
    
    /**
     * Return user time restriction
     *
     * @return mixed 
     */
    public function getUserTimeRestrict(){
        return $this->_restrict_before;
    }
    
    /**
     * Restrict user before
     *
     * @param type $userid
     * @param type $timeRestrict 
     */
    protected function restrictUser($userid, $timeRestrict){        
        $user = User::getById($userid);
        $user->restrictbefore = $timeRestrict;
        $user->save();
    }
}
?>