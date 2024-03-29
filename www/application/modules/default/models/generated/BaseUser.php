<?php

/**
 * BaseUser
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property int $age
 * @property string $sex
 * @property string $lookingFor
 * @property string $country
 * @property string $provider
 * @property string $phoneId
 * @property string $secretKey
 * @property string $photoFileName
 * @property integer $lastRequestTimeStamp
 * @property integer $invitationId
 * @property decimal $latitude
 * @property decimal $longitude
 * @property integer $checkIn
 * @property Invitation $Invitation
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseUser extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('users');
        $this->hasColumn('id', 'integer', 12, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'unsigned' => true,
             'length' => '12',
             ));
        $this->hasColumn('name', 'string', null, array(
             'type' => 'string'
             ));
        $this->hasColumn('age', 'int', null, array(
             'type' => 'int'
             ));
        $this->hasColumn('sex', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             ));
        $this->hasColumn('lookingFor', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             ));
        $this->hasColumn('country', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             ));
        $this->hasColumn('provider', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             ));
        $this->hasColumn('phoneId', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             ));
        $this->hasColumn('secretKey', 'string', 32, array(
             'type' => 'string',
             'length' => 32,
             'unique' => true,
             ));
        $this->hasColumn('photoFileName', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             ));
        $this->hasColumn('lastRequestTimeStamp', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('invitationId', 'integer', 12, array(
             'type' => 'integer',
             'unsigned' => true,
             'length' => '12',
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
        $this->hasColumn('clientOs', 'string', 32, array(
             'type' => 'string'
             ));
        $this->hasColumn('restrictbefore', 'integer', 12, array(
             'type' => 'integer',
             'length' => '10'
             ));
        $this->hasColumn('extendedRange', 'integer', 1, array(
             'type' => 'integer',
             'unsigned' => true
             ));
        $this->hasColumn('rangeTimestamp', 'integer', 16, array(
             'type' => 'integer',
             'unsigned' => true
             ));
        $this->hasColumn('eTag', 'string', 40, array(
             'type' => 'string'
             ));

        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Invitation', array(
             'local' => 'invitationId',
             'foreign' => 'id'));
    }
}