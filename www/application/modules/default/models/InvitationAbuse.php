<?php
/**
 * InvitationAbuse  model for invitation_abuse table
 *
 * @author Kudryashov Sergey iden.82@gmail.com
 */

class InvitationAbuse extends BaseInvitationAbuse{
    
    /**
     * Save the abuse request
     *
     * @param int $invitation_id
     * @param int $userId
     * @param decimal $latitude
     * @param decimal $longitude
     * @return int
     */
    public function saveTheAbuseRequest($invitation_id, $userId, $latitude, $longitude)
    {
        $this->invitations_id = $invitation_id;
        $this->initiateId = $userId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->save();
        return $this->id;
    }

    /**
     * Checks if user already send abuse request for given invitation
     *
     * @param int $userId
     * @param int $inviteId
     * @return int 
     */
    public static function checkIfUserAbuseExists($userId, $inviteId)
    {
        $count = Doctrine_Query::create()
             ->select()
             ->from('InvitationAbuse i')
             ->where('i.invitations_id= ?', $inviteId)
             ->andWhere('i.initiateId= ?', $userId)
             ->count();
        return $count;
    }

    /**
     * Return entity by id
     *
     * @param int $id 
     */
    public static function getById($id){
        $abuse = Doctrine_Query::create()
             ->from('InvitationAbuse i')
             ->where('i.id= ?',$id)
             ->fetchOne();
        return $abuse;
    }
    
    /**
     * Save the entity in db
     */
    public function save()
    {
//    	if (empty($this->timestampCreated)) $this->timestampCreated = time();
//    	if (empty($this->accepted)) $this->accepted = false;
//    	if (empty($this->rejected)) $this->rejected = false;
//    	if (empty($this->agreed)) $this->agreed = false;
    	parent::save();
    }
}
