<?php

abstract class BaseInvitationAbuse extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('invitation_abuse');
        $this->hasColumn('id', 'integer', 12, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'unsigned' => true,
             'length' => '12',
             ));
        $this->hasColumn('invitations_id', 'int', 12, array(
             'type' => 'int',
             'notnull' => true,
             'unsigned' => true,
            'length' => '12',
             ));
        $this->hasColumn('initiateId', 'int', 12, array(
             'type' => 'int',
             'unsigned' => true,
             'length' => '12',
             ));        
        $this->hasColumn('timestampcreated', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('latitude', 'decimal', 8, array(
             'type' => 'decimal',
             'length' => '8',
             'scale' => '6',
             ));
        $this->hasColumn('longitude', 'decimal', 8, array(
             'type' => 'decimal',
             'length' => '8',
             'scale' => '6',
             ));        
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Invitation', array(
             'local' => 'invitations_id',
             'foreign' => 'id'));
        $this->hasOne('User', array(
             'local' => 'initiateId',
             'foreign' => 'id')); 
    }
}