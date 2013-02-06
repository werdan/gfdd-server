<?php
class Invitation extends BaseInvitation {
    
    public static function getTimeoutCancelledInvitations($maxSecondsInvitationNotAccepted)
    {
        $query = Doctrine_Query::create()
                ->from('Invitation i')
                ->andwhere('i.timestampCreated < ?', time() - $maxSecondsInvitationNotAccepted)
                ->andWhere('i.accepted = ?', 0);
        return $query->execute();
    }
    

    /**
     * Return the invitation opponent user
     *
     * @param int $userId
     * @param int $invitationId
     * @return int 
     */
    public function getUserOpponentInvitation($userId, $invitationId)
    {
        $invitation = Invitation::getById($invitationId);
        if($userId == $invitation->inviteeId){
            return $invitation->inviterId;
        }elseif($userId == $invitation->inviterId){
            return $invitation->inviteeId;
        }        
    }

    public static function getById($id) {
		$user  = Doctrine_Query::create()
             ->from('Invitation i')
             ->where('i.id= ?',$id)
             ->fetchOne();
        return $user;
	}
        
        /**
         * Return count user abused invitations
         *
         * @param int $userid
         * @return mixed 
         */
        public function getCountAbusedInvitationsByUser($userid){
                        
           $sql = Doctrine_Query::create()                    
             ->from('invitation i')
             ->where('i.inviterid = ?', $userid)
             ->orWhere('i.inviteeid = ?', $userid)
             ->andWhere('i.abused = ?', 1);
            $sql_check = $sql->__toString();
            $count = $sql->count();  
            return $count;
        }
        
        /**
         * Get invitations by user
         *
         * @param type $userid 
         * @return mixed
         */

    public function save() {
    	if (empty($this->timestampCreated)) $this->timestampCreated = time();
    	if (empty($this->accepted)) $this->accepted = 0;
    	if (empty($this->rejected)) $this->rejected = false;
    	if (empty($this->agreed)) $this->agreed = false;
    	$this->inviterCheckedIn = empty($this->inviterCheckedIn) ? false : true;
    	$this->inviteeCheckedIn = empty($this->inviteeCheckedIn) ? false : true;
    	parent::save();
    }

    public function clearUsers() {
        $this->unlink('Users', array(), true);
        $this->clearRelated();
        $anyUser = User::getAnyActiveUser();
        $anyUser->getTable()->clear();
        $this->save();
    }
}