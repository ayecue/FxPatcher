<?php

namespace FxPatcher;

class Resolver {
	const DEFAULT_JAVASCRIPT_PATH = "/plugins/FxPatcher/static/js/";
	const DEFAULT_DOCUMENT_PREFIX = "document/";
	const DEFAULT_ADMIN_PREFIX = "admin/";

	const STATE_ADMIN = 1;
	const STATE_DOCUMENT = 4;

	private $documentPaths;
	private $adminPaths;

	function __construct(){
		$this->documentPaths = array(self::DEFAULT_JAVASCRIPT_PATH . self::DEFAULT_DOCUMENT_PREFIX);
		$this->adminPaths = array(self::DEFAULT_JAVASCRIPT_PATH . self::DEFAULT_ADMIN_PREFIX);
	}

	function add($path){
		$this->documentPaths[] = $path . self::DEFAULT_DOCUMENT_PREFIX;
		$this->adminPaths[] = $path . self::DEFAULT_ADMIN_PREFIX;
	}

	private function _resolve($paths){
		$result = array();

		foreach($paths as $path) {
			$patches = glob(PIMCORE_DOCUMENT_ROOT . $path . "*.js");

			foreach ($patches as $patch) {
				$result[] = $path . basename($patch);
			}
		}

		return $result;
	}

	function resolve($state){
		if ($state === self::STATE_DOCUMENT) {
			return $this->_resolve($this->documentPaths);
		}

		return $this->_resolve($this->adminPaths);
	}
}