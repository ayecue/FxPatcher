<?php

namespace FxPatcher;

use Pimcore\API\Plugin as PluginLib;
use FxPatcher\Controller\Plugin\Injector as FxPatcherInjector;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {
	public static function install(){
		return "Are you sure Plugin successfully installed.";
	}
	
	public static function uninstall(){
		return "Are you sure Plugin successfully uninstalled.";
	}

	public static function isInstalled(){
		return true;
	}

	public static function getTranslationFile($language) {
        if(file_exists(PIMCORE_PLUGINS_PATH . "/FxPatcher/texts/" . $language . ".csv")){
            return "/FxPatcher/texts/" . $language . ".csv";
        }
        
        return "/FxPatcher/texts/en.csv";
    }

    public function preDispatch() {
		$injector = new FxPatcherInjector();
		$instance = \Zend_Controller_Front::getInstance();
		$instance->registerPlugin($injector);	
	}
}