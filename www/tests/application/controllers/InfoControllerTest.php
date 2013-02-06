<?php
/**
 * Radius controller test
 * 
 * @author s.a.kudryashov@gmail.com
 */
require_once 'CountryDefiner.php';

class InfoControllerTest extends Zend_Test_PHPUnit_AbstractApiTestCase {
    
    public function testRateAction(){
        $user = $this->createNewUser('Viktor', '55');
        $this->getRequest()->setParam(self::SECRET_KEY_PARAM, $user->secretKey);
        $this->dispatch('/api/info/rangerate');
        $code = $this->response->getHttpResponseCode();

        $body = $this->response->getBody();
        $this->assertEquals(self::HTTP_CODE_OK, $code);
        $smxml = new SimpleXMLElement($body);
        
        $cost = $smxml->range->cost->__toString();
        $currency = $smxml->range->currency->__toString();
        $config = Zend_Registry::get('appConfig');
        $prefix = CountryDefiner::get();
        
        $this->assertEquals($config['taxesRadius'][$prefix]['tax'], $cost);
        $this->assertEquals($config['taxesRadius'][$prefix]['currency'], $currency);
    }
}
