<?php

class InvitationTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    /**
     * Tests finding users around in given area
     */
    public function testGetTimeoutedInvitations() {
        list($boy,$girl,$invitation) = $this->createInvitationKit();

        $invitation->timestampCreated = time()-350;
        $invitation->accepted = 0;
        $invitation->save();

        $invitations = Invitation::getTimeoutCancelledInvitations(400);
        $this->assertTrue(count($invitations) == 0, "Expecting no invitations, got " . count($invitations));

        $invitations = Invitation::getTimeoutCancelledInvitations(300);
        $this->assertTrue(count($invitations) == 1, "Expecting 1 invitation, got " . count($invitations));
    }
}
