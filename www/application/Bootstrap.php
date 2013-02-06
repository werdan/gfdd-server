<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initView() {
		$view = new Zend_View();
		$view->setEncoding('UTF-8');
		$view->doctype('XHTML1_STRICT');
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		return $view;
	}

       
        public function _initDoctrine()
	{
		require_once 'Doctrine.php';
		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->pushAutoloader(array('Doctrine', 'autoload'));

		$doctrineConfig = $this->getOption('doctrine');
		Zend_Registry::set('doctrineConfig',$doctrineConfig);

		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING,Doctrine::MODEL_LOADING_AGGRESSIVE);
		$manager->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
		$manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);

		// Add models and generated base classes to Doctrine autoloader
		Doctrine::loadModels(array($doctrineConfig['generated_models_path'],$doctrineConfig['models_path']));

		//This check is required as in BaseControllerTestCase application is bootstraped twice and when Doctrine Profiler is
		//activated - openConnection() is captured as event while it is not and this leads to Error
		if (count($manager->getConnections()) == 0) {
			$manager->openConnection($doctrineConfig['connection_string']);
		}
		
		if ($doctrineConfig['create_tables'] == 1) {
			Doctrine_Core::createTablesFromModels($doctrineConfig['models_path']);
		}

		//Initialize collation/encoding
		if ($doctrineConfig['collation_query']) {
			$manager->connection()->execute($doctrineConfig['collation_query']);
		}

		$profiler = new Doctrine_Connection_Profiler ();
		$manager->setListener($profiler);
		Zend_Registry::set('dbProfiler', $profiler);

		return $manager;
	}
        
        public function _initApplicationConfig() {
                date_default_timezone_set('Europe/Moscow');
		$appConfig = $this->getOption('settings');
		Zend_Registry::set('appConfig', $appConfig);
	}

	protected function _initTimeZone() {
		//TODO: Extract to application config
		date_default_timezone_set('Europe/Moscow');
	}
}
