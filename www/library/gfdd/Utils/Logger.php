<?php
/**
 * Convenient one-line usable logger
 * @author ansam
 *
 */
class Logger {

	private static $instance;
	private static $SQLinstance;
	
	public static function getLogger() {
			if(!isset(self::$instance)) {
				$appConfig = Zend_Registry::get('appConfig');
				$logFile = $appConfig['log'];
				self::$instance = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
			}
			return self::$instance;
	}

    public static function getConsoleLogger() {
        if(!isset(self::$instance)) {
            $appConfig = Zend_Registry::get('appConfig');
            $logFile = $appConfig['log'];
            self::$instance = new Zend_Log(new Zend_Log_Writer_Stream('php://stderr'));
        }
        return self::$instance;
    }


	public static function getSQLLogger() {
			if(!isset(self::$SQLinstance)) {
				$appConfig = Zend_Registry::get('appConfig');
				$logFile = $appConfig['SQLlog'];
				self::$SQLinstance = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
			}
			return self::$SQLinstance;
	}
	
}
