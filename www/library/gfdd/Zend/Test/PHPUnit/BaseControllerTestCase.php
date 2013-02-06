<?php
require_once 'Zend/Application.php';

class Zend_Test_PHPUnit_BaseControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase {

    public function setUp()
    {
        $this->purgeCurrentUser();
   		$this->reinitDB();
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        $this->getFrontController()->setParam('bootstrap', $this->application->getBootstrap());
    }
    
    private function purgeCurrentUser() {
    	$registry = Zend_Registry::getInstance();
	   	if (isset($registry['currentUser'])) {
		   	unset($registry['currentUser']);
	   	}
    	
    }

    public function appBootstrap()
    {
      $this->application = new Zend_Application(
            APPLICATION_ENV, 
            APPLICATION_PATH . '/configs/application.ini'
        );
      $this->application->bootstrap();
    }
        
    protected function printDoctrineProfiler() {
      $profiler=Zend_Registry::get('dbProfiler');
      $time = 0;
      foreach ( $profiler as $event ) {
         $time += $event -> getElapsedSecs ();
         echo $event ->getName () . " [" . sprintf ("%f", $event -> getElapsedSecs ()) . "]\n";
         echo $event ->getQuery () . "\n";
         $params = $event ->getParams ();
         if( ! empty ( $params )) {
             var_dump ( $params );
             echo "\n";
         }
      }
      echo "Total time : " . $time . "\n";
    }
    
    //Recreate all tables
    protected function reinitDB() {
		try {
	    	$manager = Doctrine_Manager::getInstance();
	        $tables = $manager->getCurrentConnection()->import->listTables();
	        foreach ($tables as $table) {
	          $manager->getCurrentConnection()->export->dropTable($table);
	        }
	        $manager->getCurrentConnection()->dropDatabase();
	        $manager->createDatabases($manager->getCurrentConnection());
	        $doctrineConfig = Zend_Registry::get('doctrineConfig');
	        Doctrine::createTablesFromModels($doctrineConfig['models_path']);     
	        
			foreach (Doctrine::getLoadedModels() as $model)
			{
  				Doctrine::getTable($model)->clear();
			}
	        
    	} catch (Exception $e) {
    		var_dump($e);
    	}    	
    	
    }
}