<?php

namespace FxPatcher\Util;

use Pimcore\Model\Cache as PimcoreCache;

class Installer {
	public static function getItems(){
		return array(
			'\FxPatcher\Util\Installer\InstallItem\Config',
			'\FxPatcher\Util\Installer\InstallItem\Directory'
		);
	}

	public static function install(){
		$items = self::getItems();
		$clearTags = false;

		foreach($items as $item) {
			if ($item::install() === true) {
				$clearTags = true;
			}
		}

		if ($clearTags) {
			PimcoreCache::clearTags(array('output', 'system', 'website_config'));
		}
	}

	public static function isInstalled(){
		$items = self::getItems();

		foreach($items as $item) {
			if (!$item::isInstalled()) {
				return false;
			}
		}

		return true;
	}

	public static function uninstall(){
		$items = self::getItems();

		foreach($items as $item) {
			if ($item::uninstall() === false) {
				return false;
			}
		}
	}
}
