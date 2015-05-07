<?php

namespace FxPatcher\Util\Installer;

interface InstallItem {
	public static function install();
	public static function isInstalled();
	public static function uninstall();
}