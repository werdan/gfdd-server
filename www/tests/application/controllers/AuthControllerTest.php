<?php
require_once 'controllers/AuthController.php';

class AuthControllerTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    function testInexistentSecretKey() {
        $this->request
            ->setMethod('GET')
            ->setPost(array(
            'secureKey' => 'somethingincorrect'
        ));
        $this->dispatch('/api/people/list');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('prohibited');
    }


    function testIncorrectImage() {
        $this->setTestPOSTFields();
        $this->createTestUserpicAndSetFiles(100,100);

        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('error');
    }

    function testFirstSigninRequest() {
        $this->prepareFirstAuthData();
        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
        $this->assertAction('signin');

        $content = $this->response->outputBody();
        $responseParsedXml = new SimpleXMLElement($content);
        $this->assertFalse(empty($responseParsedXml->secretKey));

        $appConfig = Zend_Registry::get('appConfig');
        $secretKeys = $responseParsedXml->secretKey;
        $secretKey = (string) $secretKeys[0];
        $this->assertEquals(substr(md5('4455677' . $appConfig['secretKeySalt']),0,16), substr($secretKey,0,16));

        $user = User::getBySecretKey($secretKey);
        $this->assertTrue(!empty($user));
        $this->assertTrue(strpos($user->photoFileName,"TestImage") !== false);
        $this->assertTrue($user->id > 0);
        $this->assertTrue(!empty($user->latitude), "No coordinates saved");
    }

    function testUpdateAuthInfo() {
        $user = $this->createNewUser("test", 24);
        $user->secretKey = md5("test");
        $user->save();

        $this->setTestPOSTFields();
        $this->createTestUserpicAndSetFiles(240,320);

        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());
        $this->assertAction('signin');

        $content = $this->response->outputBody();
        $responseParsedXml = new SimpleXMLElement($content);
        $this->assertTrue(md5("test") == $responseParsedXml->secretKey);

        $user = User::getBySecretKey(md5("test"));
        $this->assertNotNull($user);
        $this->assertEquals("32", $user->age);

        $this->assertTrue(( $user->lastRequestTimeStamp + 100 ) > time());
    }


    function testAuthParamsValidation() {
        $this->request
            ->setMethod('POST')
            ->setPost(array(
            'name' => 'John',
            'sex' => 'M',
            'lookingFor' => 'F',
            'country' => 'UA',
            'provider' => 'MTS',
            'phoneId' => '4455677'
        ));
        $this->createTestUserpicAndSetFiles(240,320);
        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('error');
    }

    public function testGETonSigninAction() {

        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_METHOD_NOT_ALLOWED, $this->response->getHttpResponseCode());
    }

    public function testAgeValidation() {
        $this->request
            ->setMethod('POST')
        //There is not age parameter set
            ->setPost(array(
            'name' => 'John',
            'age' => '101',
            'sex' => 'M',
            'lookingFor' => 'F',
            'country' => 'UA',
            'provider' => 'MTS',
            'phoneId' => '4455677'
        ));
        $this->createTestUserpicAndSetFiles(240,320);
        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('error');

    }

    public function testCountryValidation() {
        $this->request
            ->setMethod('POST')
            ->setPost(array(
            'name' => 'John',
            'age' => '18',
            'sex' => 'M',
            'lookingFor' => 'F',
            'country' => 'UAS',
            'provider' => 'MTS',
            'phoneId' => '4455677'
        ));
        $this->createTestUserpicAndSetFiles(240,320);
        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('error');
    }

    public function testSexValidation() {
        $this->request
            ->setMethod('POST')
            ->setPost(array(
            'name' => 'John',
            'age' => '18',
            'sex' => 'S',
            'lookingFor' => 'F',
            'country' => 'UA',
            'provider' => 'MTS',
            'phoneId' => '4455677'
        ));
        $this->createTestUserpicAndSetFiles(240,320);
        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('error');
    }

    public function testLookingForValidation() {
        $this->request
            ->setMethod('POST')
            ->setPost(array(
            'name' => 'John',
            'age' => '18',
            'sex' => 'M',
            'lookingFor' => 'SF',
            'country' => 'UA',
            'provider' => 'MTS',
            'phoneId' => '4455677'
        ));
        $this->createTestUserpicAndSetFiles(240,320);
        $this->dispatch('/api/auth/signin');
        $this->assertResponseCode(self::HTTP_CODE_FORBIDDEN, $this->response->getHttpResponseCode());
        $this->assertAction('error');

        $content = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($content, "LookingFor") == 1);
    }

    private function prepareFirstAuthData() {
        $data = $this->getRequestData();
        unset($data['secretKey']);

        $this->request
            ->setMethod('POST')
            ->setPost($data);
        $this->createTestUserpicAndSetFiles(240,320);
    }
}