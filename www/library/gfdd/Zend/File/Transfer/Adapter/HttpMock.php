<?php
class Zend_File_Transfer_Adapter_HttpMock extends Zend_File_Transfer_Adapter_Http {
 public function __construct($options = array())
    {
        if (ini_get('file_uploads') == false) {
            require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception('File uploads are not allowed in your php config!');
        }

        $this->setOptions($options);
        $this->_prepareFiles();
        //We explicitely don't use this validator in tests
        //as it checks using is_uploaded_file() function, which can not be tricked in tests
        //$this->addValidator('Upload', false, $this->_files);
    }
}