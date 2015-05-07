<?php

namespace FxPatcher\Util\Installer\InstallItem;

use Pimcore\Config as PimcoreConfig;
use FxPatcher\Util\Config as PluginConfig;
use FxPatcher\Util\Installer\InstallItem\Base as BaseItem;
use FxPatcher\Util\Installer\InstallItem as InterfaceItem;

class Config extends BaseItem implements InterfaceItem {
	public static function getSettingsConfigPath() {
		return PIMCORE_PLUGINS_PATH . '/FxPatcher/install/website.xml';                
	}

	public static function install(){
		$pluginSettingsPath = self::getSettingsConfigPath();
		$websiteData = PimcoreConfig::getWebsiteConfig();

		$websiteConfig = new PluginConfig($websiteData);
		$pluginConfig = PluginConfig::getByXml($pluginSettingsPath);

		$pluginConfig->merge($websiteConfig)->save();

		return true;
	}

	public static function isInstalled(){
		$pluginSettingsPath = self::getSettingsConfigPath();
		$websiteData = PimcoreConfig::getWebsiteConfig();

		$websiteConfig = new PluginConfig($websiteData);
		$pluginConfig = PluginConfig::getByXml($pluginSettingsPath);
		$diff = $pluginConfig->diff($websiteConfig);

		return empty($diff);
	}
}