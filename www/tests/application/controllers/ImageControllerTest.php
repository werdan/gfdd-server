<?php
require_once 'controllers/ImageController.php';

class ImageControllerTest extends Zend_Test_PHPUnit_AbstractApiTestCase {

    public function testUserPhoto() {
        $user = $this->createNewUser("Marry", 24, "test");
        $user->secretKey = md5("test");
        $this->createTestUserpicAndSetFiles(240,320);
        $createdPhotoFileName = $_FILES['userpic']['name'];
        $this->assertTrue(!empty($createdPhotoFileName));
        $user->photoFileName = $createdPhotoFileName;
        $user->save();

        //Check first response
        $this->setTestGETFields();
        $this->dispatch('/api/image/photo?id=' . $user->id);
        $this->assertResponseCode(self::HTTP_CODE_OK, $this->response->getHttpResponseCode());

        $etag = $this->getHTTPHeaderValueByName($this->response->getHeaders(), "Etag");
        $this->assertTrue(strlen($etag) == 32);
        $this->assertAction('photo');

        $content = $this->response->outputBody();
        $tmpFile = tempnam(sys_get_temp_dir(), 'ImageDownloaded');
        $file = fopen($tmpFile, "a+w");
        fwrite($file, $content);
        fclose($file);
        $imginfo = getimagesize($tmpFile);
        $this->assertTrue(strpos($imginfo['mime'], 'jpeg') !== false);
        $this->assertTrue($imginfo[0] > 100);


        //Second response for the same image
        $this->setTestGETFields();
        $this->request->setHeader("If-None-Match", $etag);
        $this->dispatch('/api/image/photo?id=' . $user->id);
        $this->assertResponseCode(self::HTTP_CODE_NOT_MODIFIED, $this->response->getHttpResponseCode());
    }

}