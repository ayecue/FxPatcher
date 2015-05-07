<?php

namespace FxPatcher\Util;

use Pimcore\Config as PimcoreConfig;

class Database {
	private static $dbConfig;
	private static $db;

	public function setDatabaseConfig(){
		if (empty(self::$dbConfig)) {
			$config = PimcoreConfig::getSystemConfig()->toArray();
			self::$dbConfig = $config["database"];
		}
		return $this;
	}

	public function getDatabaseConfig(){
		self::setDatabaseConfig();
		return self::$dbConfig;
	}

	public function setDatabase(){
		$dbConfig = self::getDatabaseConfig();
		if (empty(self::$db)) {
			self::$db = \Zend_Db::factory($dbConfig['adapter'],array(
			    'host' => $dbConfig["params"]['host'],
			    'username' => $dbConfig["params"]['username'],
			    'password' => $dbConfig["params"]['password'],
			    'dbname' => $dbConfig["params"]['dbname'],
			    "port" => $dbConfig["params"]['port']
			));
			self::$db->getConnection();
		}
		return $this;
	}

	public function getDatabase(){
		self::setDatabase();
		return self::$db;
	}

	public function insertSQLFileToDatabase($sqlFilePath) {
		try {
			$db = self::getDatabase();

			$mysqlInstallScript = file_get_contents($sqlFilePath);

			// remove comments in SQL script
			$mysqlInstallScript = preg_replace("/\s*(?!<\")\/\*[^\*]+\*\/(?!\")\s*/","",$mysqlInstallScript);

			// get every command as single part
			$mysqlInstallScripts = explode(";",$mysqlInstallScript);

			// execute every script with a separate call, otherwise this will end in a PDO_Exception "unbufferd queries, ..." seems to be a PDO bug after some googling
			foreach ($mysqlInstallScripts as $m) {
				$sql = trim($m);
				if(strlen($sql) > 0) {
					$sql .= ";";
					$db->query($m);
				}
			}
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	public function hasTableInDatabase($table) {
		try {
			$db = self::getDatabase();

			$result = $db->describeTable($table);
		} catch (\Exception $e) {
			return false;
		}

		return !empty($result) && is_array($result);
	}
}