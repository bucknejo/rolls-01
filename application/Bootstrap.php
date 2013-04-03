<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{


    protected function _initResourceLoader() {
        $this->_resourceLoader->
                addResourceType('service', 'services', 'Service');

    }
    
    function _initConfig() {

        $config = new Zend_Config_Ini(
                APPLICATION_PATH. '/configs/config.ini',
                APPLICATION_ENV);
        Zend_Registry::set('config', $config);
        return $config;

    }
        
    
}

