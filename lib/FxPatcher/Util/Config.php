<?php

namespace FxPatcher\Util;

use Pimcore\Config as PimcoreConfig;

class Config {
	private $data;

	static function getByWebsiteConfig(){
		return new self(PimcoreConfig::getWebsiteConfig());
	}

	static function getByXml($path) {
		if (!is_file($path)) {
            return array();
        }

        $rawConfig = new \Zend_Config_Xml($path);

        return new self($rawConfig);
	}

	function __construct($data = NULL){
		$this->data = $data;
	}

	function getData(){
		return $this->data;	
	}

	function save(){
		$arr = $this->data->toArray();

		foreach ($arr as $key => $entry) {
            $new = new \WebsiteSetting();

            $new->setName($key);
            $new->setType($entry['type']);
            $new->setData($entry['data']);
            $new->setSiteId($entry['siteId']);

            $new->save();
        }
	}

	function diff(\FxPatcher\Util\Config $config){
		$arr = array();
		$a = $this->data->toArray();
		$b = $config->getData()->toArray();

    	foreach ($a as $key => $value) {
    		if (!array_key_exists($key,$b)) {
    			$arr[$key] = $value;
    		}
    	}

    	return $arr;
	}

	function merge(\FxPatcher\Util\Config $config){
		$diff = $this->diff($config);
		$newConfig = new \Zend_Config($diff);

		return new self($newConfig);
	}

	function toObject(){
		return (object) $this->data->toArray();
	}
}