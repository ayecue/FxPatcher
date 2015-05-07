<?php

namespace FxPatcher\Util\Installer\InstallItem;

use Pimcore\Config as PimcoreConfig;
use FxPatcher\Resolver as Resolver;
use FxPatcher\Util\Installer\InstallItem\Base as BaseItem;
use FxPatcher\Util\Installer\InstallItem as InterfaceItem;

class Directory extends BaseItem implements InterfaceItem {
	const WRITE_MODE = 0777;

	public static function install(){
		$data = PimcoreConfig::getWebsiteConfig();
		$dirs = array(
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath,
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath . Resolver::DEFAULT_DOCUMENT_PREFIX,
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath . Resolver::DEFAULT_ADMIN_PREFIX
		);

		foreach($dirs as $dir){
			if (!file_exists($dir)) {
				mkdir($dir,self::WRITE_MODE,true);
			}
		}

		return false;
	}

	public static function isInstalled(){
		$data = PimcoreConfig::getWebsiteConfig();
		$dirs = array(
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath,
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath . Resolver::DEFAULT_DOCUMENT_PREFIX,
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath . Resolver::DEFAULT_ADMIN_PREFIX
		);

		foreach($dirs as $dir){
			if (!file_exists($dir)) {
				return false;
			}
		}


		return true;
	}

	public static function uninstall(){
		$data = PimcoreConfig::getWebsiteConfig();
		$dirs = array(
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath,
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath . Resolver::DEFAULT_DOCUMENT_PREFIX,
			PIMCORE_WEBSITE_PATH . $data->fxPatcherPath . Resolver::DEFAULT_ADMIN_PREFIX
		);

		foreach($dirs as $dir){
			if (file_exists($dir)) {
				rmdir($dir);
			}
		}


		return true;
	}
}