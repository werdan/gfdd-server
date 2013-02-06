<?php

require_once("Logger.php");
require_once('Longpoll/InvitationCancelMessage.php');


class PeriodicJobs {
    
    public function cancelrangeAction(){
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $time = time();
        $cfg = Zend_Registry::get('appConfig');
        $timeshift = $cfg['timeshiftThanRangeBecameOld'];
        $rangetime = $time - $timeshift;
        $users = User::getExtendedRangesOlderThan($rangetime);
        
        if(!empty($users)){
            foreach($users as $user){
                $user->range = null;
                $user->rangeTimestamp = null;
            }
        }
    } 
    
    public function cancelTimeoutedInvitations()
    {
        Logger::getConsoleLogger()->debug("Starting job: cancel timeout invitations");
        $cfg = Zend_Registry::get('appConfig');
        $timeout = $cfg['timeoutToCancelAcceptedInvitation'];
        $expiredInvitations = Invitation::getTimeoutCancelledInvitations($timeout);

        foreach ($expiredInvitations as $invitation){
            $inviter = User::getById($invitation->inviterId);
            $invitee = User::getById($invitation->inviteeId);

            Logger::getConsoleLogger()->info("Cancelled invitation between sent by user " . $inviter->id . " to user " . $invitee->id);

            $message = new InvitationCancelMessage($invitee, $inviter);
            $message->save();
            $message = new InvitationCancelMessage($inviter, $invitee);
            $message->save();

            $invitation->cancelled = true;
            $invitation->clearUsers();
        }
        Logger::getConsoleLogger()->debug("Ending job: cancel timeout invitations");
    }
}
