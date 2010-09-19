# Type Converter v1.0 #

A class that handles the detection and conversion of certain resource formats / content types into other formats.
The current formats are supported: XML (RSS, Atom), JSON, Array, Object

## Requirements ##

* PHP 5.2.x, 5.3.x
* SimpleXML - http://php.net/manual/en/book.simplexml.php

## Documentation ##

The class is pretty straight forward. If you want to convert something to another format, use the "to" methods.

	$object = TypeConverter::toObject($resource);
	$array = TypeConverter::toArray($resource);
	$json = TypeConverter::toJson($resource);
	$xml = TypeConverter::toXml($resource);

If you want to detect what resource type it is, use the "is" methods.
If you use the "to" methods above, it does automatic "is" detection.

	TypeConverter::isObject($resource);
	TypeConverter::isArray($resource);
	TypeConverter::isJson($resource);
	TypeConverter::isXml($resource);

If you want a string representation of what a resource is, use the default is() method.

	$resource = array();
	TypeConverter::is($resource); // array