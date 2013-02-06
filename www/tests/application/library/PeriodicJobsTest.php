<?php

require_once('PeriodicJobs.php');
require_once('Longpoll/InvitationCancelMessage.php');

class PeriodicJobsTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
 
  public function testCancelTimeoutInvitation() {
      list($boy, $girl, $invitation) = $this->createInvitationKit();
      $invitation->timestampCreated = time()-10000;
      $invitation->accepted = 0;
      $invitation->save();

      $invitationId = $invitation->id;

      $jobContainer = new PeriodicJobs();
      $jobContainer->cancelTimeoutedInvitations();

      $boy2 = User::getById($boy->id);
      $girl2 = User::getById($girl->id);

      $updatedInvitation = Invitation::getById($invitationId);
      $this->assertTrue($updatedInvitation->cancelled == 1);
      $this->assertTrue(empty($boy2->Invitation));
      $this->assertTrue(empty($girl2->Invitation));

      $m = MessageQueue::getMessageFor($boy->id);
      $this->assertTrue($m->messageType == 'InvitationCancelMessage');
  }
}