<?php

abstract class BaseMessage extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('messages');
        $this->hasColumn('id', 'integer', 12, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'unsigned' => true,
             'length' => '12',
             ));
        $this->hasColumn('messageType', 'string', null, array(
             'type' => 'string',
             ));
        $this->hasColumn('messageText', 'string', null, array(
             'type' => 'string',
             ));
        $this->hasColumn('senderId', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('recipientId', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('createdAtTimestamp', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));
        $this->hasColumn('readTimestamp', 'int', null, array(
             'type' => 'int',
             'unsigned' => true,
             ));

        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}