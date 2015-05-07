<?php

namespace FxPatcher;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Config as PimcoreConfig;
use FxPatcher\Controller\Plugin\Injector as FxPatcherInjector;
use FxPatcher\Util\Installer as PluginInstaller;
use FxPatcher\Util\Config as PluginConfig;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {
	private $_configuration = NULL;

	public static function install(){
		PluginInstaller::install();
	}
	
	public static function uninstall(){
		PluginInstaller::uninstall();
	}

	public static function isInstalled(){
		return PluginInstaller::isInstalled();
	}

	public static function getTranslationFile($language) {
        if(file_exists(PIMCORE_PLUGINS_PATH . "/FxPatcher/texts/" . $language . ".csv")){
            return "/FxPatcher/texts/" . $language . ".csv";
        }
        
        return "/FxPatcher/texts/en.csv";
    }

    public function preDispatch() {
    	$configuration = $this->getConfiguration();
		$injector = new FxPatcherInjector($configuration);
		$instance = \Zend_Controller_Front::getInstance();
		$instance->registerPlugin($injector);	
	}
	
	public function getConfiguration(){
		if ($this->_configuration == NULL) {
			$this->_configuration = PluginConfig::getByWebsiteConfig()->toObject();
		}

		return $this->_configuration;
	}
}