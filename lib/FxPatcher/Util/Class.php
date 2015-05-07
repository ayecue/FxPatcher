<?php

namespace FxPatcher\Util;

use Pimcore\Model\Object\ClassDefinition as ClassDefinition;

class Class {
	public function createClassByJson($classname,$pathToJson) {
		$json = file_get_contents($pathToJson);

		try {
			$class = ClassDefinition::create();
			$class->setName($classname);
			$class->save();
		} catch (Exception $e) {
			return false;
		}

	try {
		ClassDefinition\Service::importClassDefinitionFromJson($class,$json);
	} catch (\Exception $e) {
		$class->delete();

		return false;
	}

		return true;
	}

	public function removeClass($classname) {
		try {
			$class = ClassDefinition::getByName($classname);

			if ($class) {
				$class->delete();
			}
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	public function hasClass($classname) {
		return ClassDefinition::getByName($classname);
	}
}