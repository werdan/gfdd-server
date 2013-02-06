<?php 
require_once 'controllers/AbstractApiController.php';
require_once 'Longpoll/InvitationRequestMessage.php';
require_once 'Longpoll/InvitationRejectMessage.php';
require_once 'Longpoll/InvitationAcceptMessage.php';
require_once 'Longpoll/InvitationPlacetimeMessage.php';
require_once 'Longpoll/InvitationAgreeMessage.php';
require_once 'Longpoll/InvitationFinishedMessage.php';
require_once 'Longpoll/InvitationLateMessage.php';
require_once 'Longpoll/InvitationCheckinMessage.php';
require_once 'Longpoll/NoMessage.php';
require_once 'Longpoll/MessageQueue.php';
require_once 'DistanceCalculator.php';

class Api_InvitationController extends AbstractApiController {
        
	protected function getAllowedHTTPMethod() {
		return self::HTTP_METHOD_POST;	
	}

    public function requestAction() {

		$inviteeId = $this->getRequest()->getParam("id");
		$invitee = User::getById($inviteeId);
		$this->assertUserNotEmpty($invitee);
		$this->assertUserNotInvitesHimself($inviteeId);

		$currentUser = $this->getCurrentUser();
		$this->assertNoActiveInvitations($currentUser, self::HTTP_CODE_FORBIDDEN);
		$this->assertNoActiveInvitations($invitee, self::HTTP_CODE_CONFLICT);

		$invitation = new Invitation();
		$invitation->inviterId = $currentUser->id;
		$invitation->inviteeId = $invitee->id;

		$currentUser->Invitation = $invitation;
		$invitee->Invitation = $invitation;

		$currentUser->save();
		$this->setCurrentUser($currentUser);
		$invitee->save();

		$message = new InvitationRequestMessage($currentUser, $invitee);
		$message->setTimestampCreated($invitation->timestampCreated);
		$message->save();

		Logger::getLogger()->info('Accept invitation request successfully processed');
		
	}

	public function acceptAction() {
		$this->assertActiveInvitation();
		$this->assertNotInvitationCreator();
		$this->assertNotAcceptedInvitation();
		
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		$invitation->accepted = true;
		$invitation->save();
		
		$inviterId = $invitation->inviterId;
		$inviter = User::getById($inviterId); 
		
		$message = new InvitationAcceptMessage($currentUser, $inviter);
		$message->save();
		Logger::getLogger()->info('Accept invitation request successfully processed');
		
	}

	public function rejectAction() {
		$this->assertActiveInvitation();
		$currentUser = $this->getCurrentUser();
		
		$invitation = $currentUser->Invitation;
		
		if ($currentUser->id == $invitation->inviterId) {
			$recipientId = $invitation->inviteeId;
		} else {
			$recipientId = $invitation->inviterId;
		}
		$recipient = User::getById($recipientId);
		Logger::getLogger()->info("Reject message will be sent to userId = " .$recipientId);
		
		MessageQueue::markAsReadAllUnreadMessagesBetweenUsers($currentUser, $recipient);
		$message = new InvitationRejectMessage($currentUser, $recipient);
		$message->save();
                
		$invitation->rejected = true;
		$invitation->clearUsers();
		Logger::getLogger()->info('Reject invitation request successfully processed');
	}
	
	public function placetimeAction() {
		$timeshift = (int) $this->getRequest()->getParam("timeshift");
		$placeId = (int) $this->getRequest()->getParam("placeId");
		
		$this->assertValidTimeshift($timeshift);
		$place = $this->assertValidPlace($placeId);
		
		$this->assertActiveInvitation();
		$this->assertCurrentInvitationAccepted();
		
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		$invitation->timeshift = $timeshift;
		$invitation->finalTime = time() + $invitation->timeshift*60;
		$invitation->Place = $place;
		$invitation->userIdProposedTimeplace = $currentUser->id; 
		$invitation->save();

		$counterPartUser = $this->getCounterPartInvitationUser($invitation);
				
		$message = new InvitationPlacetimeMessage($currentUser, $counterPartUser);
		$message->setPlace($invitation->Place);
		$message->setTimeshift($invitation->timeshift);
		$message->save(); 
		
		Logger::getLogger()->info('Placetime invitation request successfully processed');
		
	}
	
	public function agreeAction() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		$this->assertPlaceTimeProposed($invitation);
		$this->assertCorrectPartyAgrees($invitation);
		$invitation->agreed = true;
		$invitation->finalTime = $this->calculateRoundedTime($invitation->timeshift);
		$invitation->save();

		$counterPartUser = $this->getCounterPartInvitationUser($invitation);		
		$message = new InvitationAgreeMessage($currentUser, $counterPartUser);
		$message->setFinalTime($invitation->finalTime);
		$message->save(); 
		
		Logger::getLogger()->info('Agree invitation request successfully processed');
	}
	
	public function lateAction() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		$latefor = (int) $this->getRequest()->getParam("latefor");
		
		$this->assertActiveInvitation();
		$this->assertNotAgreedInvitation();
		$this->assertLateNotSentBefore();
		$this->assertValidTimeshift($latefor);

        if ($currentUser->id == $invitation->inviterId) {
            $invitation->inviterIsLate = true;
        } else {
            $invitation->inviteeIsLate = true;
        }

		$counterPartUser = $this->getCounterPartInvitationUser($invitation);		
		$message = new InvitationLateMessage($currentUser, $counterPartUser);
		$message->setLateFor($latefor);
		$message->save();
		
		Logger::getLogger()->info('Late invitation request successfully processed');
	}

    public function checkinAction(){
        $this->assertActiveInvitation();
        $this->assertCurrentInvitationAccepted();
        $this->assertAgreedInvitation();
        $this->assertUserInProperPlace();

        $currentUser = $this->getCurrentUser();
        $invitation = $currentUser->Invitation;

        if ($currentUser->id == $invitation->inviterId) {
            $invitation->inviterCheckedIn = true;
        } else {
            $invitation->inviteeCheckedIn = true;
        }
        $invitation->save();

        $counterPartUser = $this->getCounterPartInvitationUser($invitation);
        $message = new InvitationCheckinMessage($currentUser, $counterPartUser);
        $message->save();

        Logger::getLogger()->info(sprintf('User with id %s was successfully checked in', $currentUser->id));
    }

    public function finishedAction(){
        $currentUser = $this->getCurrentUser();
        $this->assertActiveInvitation();
        $this->assertUserIsCheckedIn();

        $pairUser = $this->getCounterPartInvitationUser($currentUser->Invitation);
        $currentUser->Invitation->finished = 1;
        $currentUser->Invitation->save();
        $currentUser->save();

        $message = new InvitationFinishedMessage($currentUser, $pairUser);
        $message->save();

        $pairUser->Invitation->finished = 1;
        $pairUser->Invitation->save();
        $currentUser->Invitation->clearUsers();
    }

    public function abuseAction(){
        $currentUser = $this->getCurrentUser();
        $invitation = $currentUser->Invitation;

        $this->assertAbuseSentAfterMeeting($invitation);
        $this->assertActiveInvitation();
        $this->assertThisUserAlreadyMakeAbuse($currentUser, $invitation->id);
        $this->assertUserInProperPlace();

        //save the abuse request
        $invitationAbuse = new InvitationAbuse();

        // maybe lat and long data must be taken from request object?
        $invitationAbuse->saveTheAbuseRequest($invitation->id, $currentUser->id, $currentUser->latitude,
            $currentUser->longitude);

        $invitation->abused = 1;
        $invitation->save();

        $counterPartUser = $this->getCounterPartInvitationUser($currentUser->Invitation);
        $abuse = Zend_Abuse::getInstance();

        // at here we`re encapsulate user restriction logics
        $abuse->performRestrictionActionByUser($counterPartUser->id);
        Logger::getLogger()->info('Abuse request action successfully processed');
    }

    protected function assertUserInProperPlace(){
        $currentUser = $this->getCurrentUser();
        $place = $currentUser->Invitation->Place;
        $r = Zend_Registry::get('appConfig');
        $precisionInMeters = $r['checkinPrecision'];

        $distanceCalc = new DistanceCalculator($currentUser);
        $distanceToPlace = $distanceCalc->getDistanceToTargetInMeters($place);

        if ($distanceToPlace <= $precisionInMeters) {
            return true;
        } else {
            throw new HttpReturnableException('501: User is in a wrong place, expected no more then ' . $precisionInMeters . "m to place, currently : ". $distanceToPlace,
                self::HTTP_CODE_FORBIDDEN);
        }
    }

    private function assertInstanceInvitationObject($invitation){
            if (!($invitation instanceof Invitation) || $invitation->id == 0 ){
                 throw new HttpReturnableException('101: Incorrect invitation or user invitation is empty',
                                self::HTTP_CODE_FORBIDDEN);
            }
    }
        
    protected function assertThisUserAlreadyMakeAbuse($user, $invitationId)
        {
            $userId = $user->id;
            $df = new InvitationAbuse();
            $count = $df->checkIfUserAbuseExists($userId, $invitationId);            
            if ($count > 0) {
                    throw new HttpReturnableException("601: User already sent abuse request for this invitation",
                            self::HTTP_CODE_FORBIDDEN);
            }            
    }

	
	private function assertCurrentInvitationAccepted() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		if ($invitation->accepted == false) {
			throw new HttpReturnableException("201: Invitation has not been accepted", self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Checked invitation of current user has been already accepted.");
	}
	
	private function assertNotAcceptedInvitation() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		if ($invitation->accepted == true) {
			throw new HttpReturnableException("202: Invitation already accepted", self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Checked invitation of current user has not been already accepted.");
	}

    private function assertAgreedInvitation() {
        $currentUser = $this->getCurrentUser();
        $invitation = $currentUser->Invitation;
        if ($invitation->agreed != true) {
            throw new HttpReturnableException("401: Invitation party not agreed on place/time", self::HTTP_CODE_FORBIDDEN);
        }
        Logger::getLogger()->notice("Checked that current user has agreed on place/time");
    }

    private function assertUserIsCheckedIn() {
        $currentUser = $this->getCurrentUser();
        $invitation = $currentUser->Invitation;
        if ($invitation->inviterId == $currentUser->id && !$invitation->inviterCheckedIn ||
            $invitation->inviteeId == $currentUser->id && !$invitation->inviteeCheckedIn) {
            throw new HttpReturnableException('502: User has to checkin before finishing invitation', self::HTTP_CODE_FORBIDDEN);
        }
        Logger::getLogger()->notice("Checked that current user is checkedin before finishing invitation");
    }

	private function assertNotAgreedInvitation() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		if ($invitation->agreed != true) {
			throw new HttpReturnableException("402: Invitation has not been agreed", self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Checked invitation of current user has been agreed.");
	}
	
	
	private function assertNotInvitationCreator() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		if ($invitation->inviterId == $currentUser->id) {
			throw new HttpReturnableException("203: Can not accept own invitation", self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Checked that current user is not invitation creator.");
	}
	
	private function assertNoActiveInvitations($user, $httpErrorCode) {
		if ($this->userHasActiveInvitation($user)) {
			throw new HttpReturnableException("102: User with id ". $user->id . " already has active inivitation set.", $httpErrorCode);
		}
		Logger::getLogger()->notice("Checked that user with id= ". $user->id ." has no active invitation.");
	}
	
	private function assertActiveInvitation() {
		$currentUser = $this->getCurrentUser();
		if (!$this->userHasActiveInvitation($currentUser)) {
			throw new HttpReturnableException("106: No active invitation", self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("103: Checked that current user has an active invitation.");
	}
			
	private function assertUserNotInvitesHimself($inviteeId) {
		if ($this->getCurrentUser()->id == $inviteeId) {
			throw new HttpReturnableException("104: Attempt to invite himself, user id =". $this->getCurrentUser()->id, self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Checked that user doesn't invite himself");
	} 
	
	private function assertValidTimeshift($timeshift) {
		if ($timeshift <10 || $timeshift>90) {
			throw new HttpReturnableException("403: Invalid timeshift ".$timeshift, self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Timeshift ". $timeshift . " is valid");
	}
	
	private function assertValidPlace($placeId) {
		$place = Place::getById($placeId);
		if (empty($place) || $place->id != $placeId) {
			throw new HttpReturnableException("404: Invalid place with id=".$placeId, self::HTTP_CODE_FORBIDDEN);
		}
		Logger::getLogger()->notice("Place with id=". $placeId . " is valid");
		return $place;
	}
	
	private function assertPlaceTimeProposed($invitation) {
		if ($invitation->Place instanceof Place 
				&& $invitation->Place->id > 0
				&& $invitation->userIdProposedTimeplace > 0) {
			return;
		}
			throw new HttpReturnableException("405: Place and time were not proposed but Agree message is sent", self::HTTP_CODE_FORBIDDEN);
	}
	
	private function assertCorrectPartyAgrees($invitation) {
		$currentUser = $this->getCurrentUser();
		if ($invitation->id == $currentUser->id) {
			throw new HttpReturnableException("406: User proposed current place&time can not agree it before second party", self::HTTP_CODE_FORBIDDEN);
		}
	}

    protected function assertAbuseSentAfterMeeting(Invitation $invitation){
        $time_meeting = $invitation->finalTime;
        $now = time();
        if($time_meeting > $now){
            throw new HttpReturnableException("602: Abuse message was sent before meeting",
                self::HTTP_CODE_FORBIDDEN);
        }else{
            return true;
        }
    }


	private function assertLateNotSentBefore() {
		$currentUser = $this->getCurrentUser();
		$invitation = $currentUser->Invitation;
		if ($invitation->inviterId == $currentUser->id && $invitation->inviterIsLate ||
		    $invitation->inviteeId == $currentUser->id && $invitation->inviteeIsLate) {
			throw new HttpReturnableException('503: User already sent LATE message', self::HTTP_CODE_FORBIDDEN);
		}
	}
	
	private function calculateRoundedTime($timeShift) {
  	   $currentMinutes = date("i");
	   $lowerDigitMinutes = $currentMinutes-floor($currentMinutes/10)*10;
	   if ($lowerDigitMinutes > 0 & $lowerDigitMinutes < 5) {
	   		$roundingMinutes = 5-$lowerDigitMinutes;
	   } else if ($lowerDigitMinutes > 5 & $lowerDigitMinutes <= 9) {
	   	  	$roundingMinutes = 10-$lowerDigitMinutes;
	   } else {
		    $roundingMinutes = 0;
	   }
	   	
	   $date = new DateTime();
	   $date->modify('+' .  $roundingMinutes . ' minute');
	   $date->modify('+' .  $timeShift . ' minute');
	   return date_format($date, 'U');
  }
	
  private function getCounterPartInvitationUser($invitation) {
  	    $currentUser = $this->getCurrentUser();
  		$counterPartId = ($invitation->inviteeId == $currentUser->id ? $invitation->inviterId : $invitation->inviteeId);
		return User::getById($counterPartId);
  }
}
