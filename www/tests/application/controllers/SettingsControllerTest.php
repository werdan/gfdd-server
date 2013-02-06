<?php
class SettingsControllerTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
    
    public function testRangeAction(){
        $user = $this->createNewUser('Viktor', '55');

        $this->setTestPOSTFields(array(self::SECRET_KEY_PARAM => $user->secretKey));
        $this->dispatch('/api/settings/range');
        $code = $this->response->getHttpResponseCode();
        $this->assertEquals(self::HTTP_CODE_OK, $code);

        $updatedUser = User::getById($user->id);
        $this->assertEquals(1, $updatedUser->extendedRange);

        $this->assertTrue($updatedUser->rangeTimestamp >= time());
    }
}
