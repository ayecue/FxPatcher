<?php

namespace FxPatcher\Util\Installer\InstallItem;

class Base {
	public static function install(){
		return true;
	}

	public static function isInstalled(){
		return true;
	}

	public static function uninstall(){
		return true;
	}
}