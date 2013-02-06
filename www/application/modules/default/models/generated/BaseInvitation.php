<?php

abstract class BaseInvitation extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('invitations');
        $this->hasColumn('id', 'integer', 12, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'unsigned' => true,
             'length' => '12',
             ));
        $this->hasColumn('timestampCreated', 'int', null, array(
             'type' => 'int',
             'notnull' => true,
             'unsigned' => true,
             ));
        $this->hasColumn('inviterId', 'int', null, array(
             'type' => 'int',
             'notnull' => true,
             'unsigned' => true,
             ));
        $this->hasColumn('inviteeId', 'int', null, array(
             'type' => 'int',
             'notnull' => true,
             'unsigned' => true,
             ));
        $this->hasColumn('placeId', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('timeshift', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('finalTime', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('userIdProposedTimeplace', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('accepted', 'int', null, array(
             'type' => 'int',
             'notnull' => true,
             ));
        $this->hasColumn('rejected', 'int', null, array(
             'type' => 'int',
             'notnull' => true,
             ));
        $this->hasColumn('agreed', 'int', null, array(
             'type' => 'int',
             'notnull' => true,
             ));
        $this->hasColumn('inviterIsLate', 'int', null, array(
             'type' => 'int',
             ));
        $this->hasColumn('inviteeIsLate', 'int', null, array(
             'type' => 'int',
             ));
        $this->hasColumn('inviterCheckedIn', 'int', null, array(
            'type' => 'int',
        ));
        $this->hasColumn('inviteeCheckedIn', 'int', null, array(
            'type' => 'int',
        ));
        $this->hasColumn('abused', 'int', null, array(
             'type' => 'int',
             'length'=>1
             ));
        $this->hasColumn('cancelled', 'int', null, array(
             'type' => 'int',
             'length'=>1
             ));
        $this->hasColumn('finished', 'int', null, array(
             'type' => 'int',
             'length'=>1,
             'unsigned' => true
             ));
        
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('User as Users', array(
             'local' => 'id',
             'foreign' => 'invitationId'));

        $this->hasOne('Place', array(
             'local' => 'placeId',
             'foreign' => 'id'));
    }
}