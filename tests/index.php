<?php
/**
 * A class that handles the detection and conversion of certain resource formats / content types into other formats.
 * The current formats are supported: XML, JSON, Array, Object, Serialized
 *
 * @author      Miles Johnson - http://milesj.me
 * @copyright   Copyright 2006-2011, Miles Johnson, Inc.
 * @license     http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link        http://milesj.me/code/php/type-converter
 */

// Turn on errors
error_reporting(E_ALL);

function debug($var) {
	echo '<pre>'. print_r($var, true) .'</pre>';
}

function dump($key, $value) {
	echo $key .' ('. ($value ? 'true' : 'false') .') ';
}

// Include class
include_once '../type_converter/TypeConverter.php';

// Create variables
$array	= array('is' => 'array');
$json	= json_encode(array('is' => 'json'));
$ser	= serialize(array('is' => 'serialize'));
$xml	= '<?xml version="1.0" encoding="utf-8"?><root><is>xml</is></root>';
$object	= new stdClass();
$object->is = 'object';

// Determine the type
debug('TypeConverter::is()');
debug(TypeConverter::is($array));
debug(TypeConverter::is($object));
debug(TypeConverter::is($json));
debug(TypeConverter::is($ser));
debug(TypeConverter::is($xml));

// Validate against all types
foreach (array('isArray', 'isObject', 'isJson', 'isSerialized', 'isXml') as $method) {
	debug('TypeConverter::'. $method .'()');
	
	dump('array', TypeConverter::$method($array));
	dump('object', TypeConverter::$method($object));
	dump('json', TypeConverter::$method($json));
	dump('serialize', TypeConverter::$method($ser));
	dump('xml', TypeConverter::$method($xml));
}

// Convert all the types
foreach (array('toArray', 'toObject', 'toJson', 'toSerialize', 'toXml') as $method) {
	debug('TypeConverter::'. $method .'()');

	if ($method == 'toXml') {
		debug(htmlentities(TypeConverter::toXml($array)));
		debug(htmlentities(TypeConverter::toXml($object)));
		debug(htmlentities(TypeConverter::toXml($json)));
		debug(htmlentities(TypeConverter::toXml($ser)));
		debug(htmlentities(TypeConverter::toXml($xml)));
	} else {
		debug(TypeConverter::$method($array));
		debug(TypeConverter::$method($object));
		debug(TypeConverter::$method($json));
		debug(TypeConverter::$method($ser));
		debug(TypeConverter::$method($xml));
	}
}

// Convert a complicated XML file to an array
$xml = file_get_contents('test.xml');

foreach (array('none', 'merge', 'group', 'overwrite') as $format) {
	debug('TypeConverter::xmlToArray('. $format .')');
	
	switch ($format) {
		case 'none':
			debug(TypeConverter::xmlToArray($xml, TypeConverter::XML_NONE));
		break;
		case 'merge':
			debug(TypeConverter::xmlToArray($xml, TypeConverter::XML_MERGE));
		break;
		case 'group':
			debug(TypeConverter::xmlToArray($xml, TypeConverter::XML_GROUP));
		break;
		case 'overwrite':
			debug(TypeConverter::xmlToArray($xml, TypeConverter::XML_OVERWRITE));
		break;
	}
}
