<?php

namespace FxPatcher;

class Script {
	const DOM_VERSION = "1.0";
	const DOM_ENCODING = "utf-8";

	private $DOM = NULL;

	public function setDOM($handle){
		$this->DOM = $handle;
		return $this;
	}

	public function getDOM(){
		return $this->DOM;
	}

	public function initDOM(){
		$this->setDOM(new \DOMDocument(self::DOM_VERSION,self::DOM_ENCODING));
	}

	public function addJavascript(\simple_html_dom $html,$src,$selector = "head"){
		$found = $html->find($selector);

		if (isset($found) && !empty($found)) {
			foreach ($found as $head) {
				$this->initDOM();
				$scriptElement = $this->getDOM()->createElement("script");
				$scriptElement->setAttribute("type","text/javascript");
				$scriptElement->setAttribute("src",$src);
				$this->getDOM()->appendChild($scriptElement);
				$head->innertext = $head->innertext . $this->getDOM()->saveHTML();

				return TRUE;
			}
		}
	}
}