<?php
/**
 * A class that handles the detection and conversion of certain resource formats / content types into other formats.
 * The current formats are supported: XML (RSS, Atom), JSON, Array, Object
 *
 * @author		Miles Johnson - www.milesj.me
 * @copyright	Copyright 2006-2010, Miles Johnson, Inc.
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 */

class TypeConverter {

	/**
	 * Should we append the root node into the array when going from XML -> array.
	 *
	 * @access public
	 * @var boolean
	 */
	public static $rootInXml = true;

	/**
	 * Returns a string for the detected type.
	 *
	 * @access public
	 * @param mixed $data
	 * @return string
	 * @static
	 */
	public static function is($data) {
		if (self::isArray($data)) {
			return 'array';

		} else if (self::isObject($data)) {
			return 'object';

		} else if (self::isJson($data)) {
			return 'json';

		} else if (self::isXml($data)) {
			return 'xml';
		}

		return 'other';
	}

	/**
	 * Check to see if data passed is an array.
	 *
	 * @access public
	 * @param mixed $data
	 * @return boolean
	 * @static
	 */
	public static function isArray($data) {
		return is_array($data);
	}

	/**
	 * Check to see if data passed is a JSON object.
	 *
	 * @access public
	 * @param mixed $data
	 * @return boolean
	 * @static
	 */
	public static function isJson($data) {
		return (@json_decode($data) !== null);
	}

	/**
	 * Check to see if data passed is an object.
	 *
	 * @access public
	 * @param mixed $data
	 * @return boolean
	 * @static
	 */
	public static function isObject($data) {
		return is_object($data);
	}

	/**
	 * Check to see if data passed is an xml document.
	 *
	 * @access public
	 * @param mixed $data
	 * @return boolean
	 * @static
	 */
	public static function isXml($data) {
		$xml = @simplexml_load_string($data);
		return ($xml === null) ? false : $xml;
	}

	/**
	 * Transforms a resource into an array.
	 *
	 * @access public
	 * @param mixed $resource
	 * @return array
	 * @static
	 */
	public static function toArray($resource) {
		if (self::isArray($resource)) {
			return $resource;

		} else if (self::isObject($resource)) {
			return self::buildArray($resource);

		} else if (self::isJson($resource)) {
			return json_decode($resource, true);

		} else if ($xml = self::isXml($resource)) {
			return self::xmlToArray($xml);
		}

		return $resource;
	}

	/**
	 * Transforms a resource into a JSON object.
	 *
	 * @access public
	 * @param mixed $resource
	 * @return json
	 * @static
	 */
	public static function toJson($resource) {
		if (self::isJson($resource)) {
			return $resource;

		} else {
			if ($xml = self::isXml($resource)) {
				$resource = self::xmlToArray($xml);
			}

			return json_encode($resource);
		}

		return $resource;
	}

	/**
	 * Transforms a resource into an object.
	 *
	 * @access public
	 * @param mixed $resource
	 * @return object
	 * @static
	 */
	public static function toObject($resource) {
		if (self::isObject($resource)) {
			return $resource;

		} else if (self::isArray($resource)) {
			return self::buildObject($resource);

		} else if (self::isJson($resource)) {
			return json_decode($resource);

		} else if ($xml = self::isXml($resource)) {
			return $xml;
		}

		return $resource;
	}

	/**
	 * Transforms a resource into an XML document.
	 *
	 * @access public
	 * @param mixed $resource
	 * @param boolean $asXml - Return as straight XML and not an object
	 * @return object
	 * @static
	 */
	public static function toXml($resource, $asXml = true) {
		if (self::isXml($resource)) {
			return $resource;
		}

		$resource = self::toArray($resource);

		if (!empty($resource)) {
			$root = 'root';
			$data = $resource;

			if (count($resource) == 1) {
				$keys = array_keys($resource);

				if (is_array($resource[$keys[0]])) {
					$root = $keys[0];
					$data = $resource[$keys[0]];
				}
			}

			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8" ?><'. $root .'></'. $root .'>');
			$response = self::buildXml($xml, $data);

			if ($asXml) {
				return $response->asXML();
			}

			return $response;
		}

		return $resource;
	}

	/**
	 * Turn an object into an array. Alternative to array_map magic.
	 *
	 * @access public
	 * @param object $object
	 * @return array
	 */
	public static function buildArray($object) {
		$array = array();

		foreach ($object as $key => $value) {
			if (is_object($value)) {
				$array[$key] = self::buildArray($value);
			} else {
				$array[$key] = $value;
			}
		}

		return $array;
	}

	/**
	 * Turn an array into an object. Alternative to array_map magic.
	 *
	 * @access public
	 * @param array $array
	 * @return object
	 */
	public static function buildObject($array) {
		$obj = new stdClass();

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$obj->{$key} = self::buildObject($value);
			} else {
				$obj->{$key} = $value;
			}
		}

		return $obj;
	}

	/**
	 * Turn an array into an XML document. Alternative to array_map magic.
	 *
	 * @access public
	 * @param object $xml
	 * @param array $array
	 * @return object
	 */
	public static function buildXml(&$xml, $array) {
		if (is_array($array)) {
			foreach ($array as $element => $value) {

				// Regular element
				if (is_string($value)) {
					$xml->addChild($element, $value);

				// Element has child elements or attributes
				} else if (is_array($value)) {

					// Multiple elements with same name
					if (isset($value[0])) {
						foreach ($value as $subElement) {
							if (is_array($subElement)) {
								self::buildXml($xml, array($element => $subElement));
							} else {
								$xml->addChild($element, $subElement);
							}
						}

					// Element with attributes
					} else if (isset($value['attributes']) && isset($value['value'])) {
						$node = $xml->addChild($element, $value['value']);

						foreach ($value['attributes'] as $attr => $attrValue) {
							$node->addAttribute($attr, $attrValue);
						}

						self::buildXml($node, $value['value']);

					// Regular element with children
					} else {
						$node = $xml->addChild($element);
						self::buildXml($node, $value);
					}
				}
			}
		}

		return $xml;
	}

	/**
	 * Convert a SimpleXML object into an array (last resort).
	 *
	 * @access public
	 * @param object $xml
	 * @return array
	 */
	public static function xmlToArray($xml) {
		if (!$xml->children()) {
			return (string)$xml;
		}

		$array = array();
		foreach ($xml->children() as $element => $node) {
			$totalElement = count($xml->{$element});

			if (!isset($array[$element])) {
				$array[$element] = "";
			}

			// Has attributes
			if ($attributes = $node->attributes()) {
				$data = array(
					'attributes' => array(),
					'value' => (count($node) > 0) ? self::xmlToArray($node, false) : (string)$node
				);

				foreach ($attributes as $attr => $value) {
					$data['attributes'][$attr] = (string)$value;
				}

				if ($totalElement > 1) {
					$array[$element][] = $data;
				} else {
					$array[$element] = $data;
				}

			// Just a value
			} else {
				if ($totalElement > 1) {
					$array[$element][] = self::xmlToArray($node, false);
				} else {
					$array[$element] = self::xmlToArray($node, false);
				}
			}
		}

		if (TypeConverter::$rootInXml) {
			return array($xml->getName() => $array);
		} else {
			return $array;
		}
	}

}
