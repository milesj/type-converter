<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/type-converter
 */

namespace mjohnson\utility;

/**
 * A class that handles the detection and conversion of certain resource formats / content types into other formats.
 * The current formats are supported: XML, JSON, Array, Object, Serialized
 *
 * @version	2.0.0
 * @package	mjohnson.utility
 */
class TypeConverter {

	/**
	 * Disregard XML attributes and only return the value.
	 */
	const XML_NONE = 0;

	/**
	 * Merge attributes and the value into a single dimension; the values key will be "value".
	 */
	const XML_MERGE = 1;

	/**
	 * Group the attributes into a key "attributes" and the value into a key of "value".
	 */
	const XML_GROUP = 2;

	/**
	 * Attributes will only be returned.
	 */
	const XML_OVERWRITE = 3;

	/**
	 * Tracks the recursion level of buildXML();
	 *
	 * @var int
	 * @static
	 */
	private static $xml_depth = 0;

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

		} else if (self::isSerialized($data)) {
			return 'serialized';

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
	 * Check to see if data passed has been serialized.
	 *
	 * @access public
	 * @param mixed $data
	 * @return boolean
	 * @static
	 */
	public static function isSerialized($data) {
		$ser = @unserialize($data);

		return $ser !== false;
	}

	/**
	 * Check to see if data passed is an XML document.
	 *
	 * @access public
	 * @param mixed $data
	 * @return boolean
	 * @static
	 */
	public static function isXml($data) {
		return is_a($data, 'SimpleXMLElement');
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

		} else if (self::isSerialized($resource)) {
			return self::toArray(@unserialize($resource));

		} else if (self::isXml($resource)) {
			return self::xmlToArray($resource);
		}

		return $resource;
	}

	/**
	 * Transforms a resource into a JSON object.
	 *
	 * @access public
	 * @param mixed $resource
	 * @return string (json)
	 * @static
	 */
	public static function toJson($resource) {
		if (self::isJson($resource)) {
			return $resource;
		}

		if ($xml = self::isXml($resource)) {
			$resource = self::xmlToArray($xml);

		} else if ($ser = self::isSerialized($resource)) {
			$resource = $ser;
		}

		return json_encode($resource);
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

		} else if ($ser = self::isSerialized($resource)) {
			return self::toObject($ser);

		} else if ($xml = self::isXml($resource)) {
			return $xml;
		}

		return $resource;
	}

	/**
	 * Transforms a resource into a serialized form.
	 *
	 * @access public
	 * @param mixed $resource
	 * @return string
	 * @static
	 */
	public static function toSerialize($resource) {
		if (!self::isArray($resource)) {
			$resource = self::toArray($resource);
		}

		return serialize($resource);
	}

	/**
	 * Transforms a resource into an XML document.
	 *
	 * @access public
	 * @param mixed $resource
	 * @param string $root
	 * @param string|array $tags String or array of wrapping tags when
	 *		converting indexed array of object to XML.
	 * @return string (xml)
	 * @static
	 */
	public static function toXml($resource, $root = 'root', $tags = 'item') {
		if (self::isXml($resource)) {
			return $resource->asXML();
		}

		$array = self::toArray($resource);

		if (!empty($array)) {
			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><'. $root .'></'. $root .'>');
			$response = self::buildXml($xml, $array, $tags);

			return $response->asXML();
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
		$obj = new \stdClass();

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
	 * @param SimpleXMLElement $xml
	 * @param mixed $data
	 * @param string|array $tags String or array of wrapping tags when
	 *		converting indexed array of object to XML.
	 * @return object
	 */
	public static function buildXml(\SimpleXMLElement $xml, $data, $tags = 'item')
	{
		self::$xml_depth++;
		if (is_array($tags)) {
			// Stay within the bounds of the $tags array
			$index = min(array(self::$xml_depth, count($tags))) - 1;
			$tag = $tags[$index];
		} else {
			$tag = $tags;
		}

		foreach ($data as $key => $value) {
			if (is_int($key)) {
				$key = $tag;
			}
			if (is_object($value)) {
				$value = self::toArray($value);
			} elseif (!is_array($value)) {
				$xml->addChild($key, htmlentities($value));
				continue;
			}

			// At this point, value is an array - special keys are "attributes"
			// and "value", so we need to handle them.

			// Set value if it explicitely exists
			if (isset($value['value'])) {
				if (is_array($value['value'])) {
					$node = $xml->addChild($key);
					self::buildXml($node, $value['value'], $tags);
				} else {
					$node = $xml->addChild($key, htmlentities($value['value']));
				}
				unset($value['value']);
			} else {
				// Add a node, if there was no explicit value
				$node = $xml->addChild($key);
			}

			// Set attributes, if they explictely exist
			if (isset($value['attributes'])) {
				foreach($value['attributes'] as $aKey => $aValue) {
					$node->addAttribute($aKey, $aValue);
				}
				unset($value['attributes']);
			}

			// Handle standard value and recursion
			foreach ($value as $aKey => $aValue) {
				if (is_array($aValue) || is_object($aValue)) {
					self::buildXml($node, array($aKey => $aValue), $tags);
				} else {
					$node->addChild($aKey, htmlentities($aValue));
				}
			}
		}
		self::$xml_depth--;
		return $xml;
	}

	/**
	 * Convert a SimpleXML object into an array.
	 *
	 * @access public
	 * @param object $xml
	 * @param int $format
	 * @return array
	 */
	public static function xmlToArray($xml, $format = self::XML_GROUP) {
		if (is_string($xml)) {
			$xml = @simplexml_load_string($xml);
		}

		if (count($xml->children()) <= 0) {
			return (string)$xml;
		}

		$array = array();

		foreach ($xml->children() as $element => $node) {
			$data = array();

			if (!isset($array[$element])) {
				$array[$element] = "";
			}

			if (!$node->attributes() || $format === self::XML_NONE) {
				$data = self::xmlToArray($node, $format);

			} else {
				switch ($format) {
					case self::XML_GROUP:
						$data = array(
							'attributes' => array(),
							'value' => (string)$node
						);

						if (count($node->children()) > 0) {
							$data['value'] = self::xmlToArray($node, $format);
						}

						foreach ($node->attributes() as $attr => $value) {
							$data['attributes'][$attr] = (string)$value;
						}
					break;

					case self::XML_MERGE:
					case self::XML_OVERWRITE:
						if ($format === self::XML_MERGE) {
							if (count($node->children()) > 0) {
								$data = $data + self::xmlToArray($node, $format);
							} else {
								$data['value'] = (string)$node;
							}
						}

						foreach ($node->attributes() as $attr => $value) {
							$data[$attr] = (string)$value;
						}
					break;
				}
			}

			if (count($xml->{$element}) > 1) {
				$array[$element][] = $data;
			} else {
				$array[$element] = $data;
			}
		}

		return $array;
	}

	/**
	 * Encode a resource object for UTF-8.
	 *
	 * @access public
	 * @param mixed $data
	 * @return array|string
	 * @static
	 */
	public static function utf8Encode($data) {
		if (is_string($data)) {
			return utf8_encode($data);

		} else if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[utf8_encode($key)] = self::utf8Encode($value);
			}

		} else if (is_object($data)) {
			foreach ($data as $key => $value) {
				$data->{$key} = self::utf8Encode($value);
			}
		}

		return $data;
	}

	/**
	 * Decode a resource object for UTF-8.
	 *
	 * @access public
	 * @param mixed $data
	 * @return array|string
	 * @static
	 */
	public static function utf8Decode($data) {
		if (is_string($data)) {
			return utf8_decode($data);

		} else if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[utf8_decode($key)] = self::utf8Decode($value);
			}

		} else if (is_object($data)) {
			foreach ($data as $key => $value) {
				$data->{$key} = self::utf8Decode($value);
			}
		}

		return $data;
	}

}
