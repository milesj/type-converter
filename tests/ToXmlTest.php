<?php

namespace mjohnson\utility\tests;

use mjohnson\utility\TypeConverter;

class ToXmlTest extends \PHPUnit_Framework_TestCase
{
	public function testBasicArrayToXml() {
		$data = array('is' => 'array');

		$this->assertTrue(TypeConverter::isArray($data));

		$expected_xml = $this->getXmlDeclaration() . '<root><is>array</is></root>';
		$actual_xml   = TypeConverter::toXml($data);

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testBasicJsonToXml() {
		$data = json_encode(array('is' => 'json'));

		$this->assertTrue(TypeConverter::isJson($data));

		$expected_xml = $this->getXmlDeclaration() . '<root><is>json</is></root>';
		$actual_xml   = TypeConverter::toXml($data);

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testBasicSerializeToXml() {
		$data = serialize(array('is' => 'serialize'));

		$this->assertTrue(TypeConverter::isSerialized($data));

		$expected_xml = $this->getXmlDeclaration() . '<root><is>serialize</is></root>';
		$actual_xml   = TypeConverter::toXml($data);

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testBasicXmlToXml() {
		$data = simplexml_load_string('<root><is>xml</is></root>');

		$this->assertTrue(TypeConverter::isXml($data));

		$expected_xml = $this->getXmlDeclaration() . '<root><is>xml</is></root>';
		$actual_xml   = TypeConverter::toXml($data);

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testSingleObjectToXml()
	{
		// Create object
		$data = $this->createObject(1);

		// Convert to XML, with root of 'root' and tag wrappers of 'item'
		$actual_xml = TypeConverter::toXml($data);

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration() . '<root><id>1</id><name>Object 1</name></root>';

		// Compare as plain strings
		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testSingleComplexObjectToXml()
	{
		// Create object
		$data = $this->createComplexObject(1);

		// Convert to XML, with root of 'root' and tag wrappers of 'item'
		$actual_xml = TypeConverter::toXml($data);

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration()
				. '<root><id>1</id><name>Object 1</name><foo><bar>string</bar><baz>'
				. '<item><id>1.1</id><name>Object 1.1</name></item>'
				. '<item><id>1.2</id><name>Object 1.2</name></item>'
				. '<item><id>1.3</id><name>Object 1.3</name></item>'
				. '</baz></foo></root>';

		// Compare as plain strings
		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);

		// Now check optional parameters to toXml()

		// Convert to XML, with root of 'notroot' and tag wrappers of 'article'
		$actual_xml = TypeConverter::toXml($data, 'notroot', 'article');

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration()
				. '<notroot><id>1</id><name>Object 1</name><foo><bar>string</bar><baz>'
				. '<article><id>1.1</id><name>Object 1.1</name></article>'
				. '<article><id>1.2</id><name>Object 1.2</name></article>'
				. '<article><id>1.3</id><name>Object 1.3</name></article>'
				. '</baz></foo></notroot>';

		// Compare as plain strings
		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testArrayOfMultipleObjectsToXml()
	{
		// Create objects
		$object1 = $this->createObject(1);
		$object2 = $this->createObject(2);
		$object3 = $this->createObject(3);

		// Create aray of objects
		$data = array($object1, $object2, $object3);

		// Convert to XML, with root of 'root' and tag wrappers of 'item'
		$actual_xml = TypeConverter::toXml($data);

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration()
				. '<root>'
				. '<item><id>1</id><name>Object 1</name></item>'
				. '<item><id>2</id><name>Object 2</name></item>'
				. '<item><id>3</id><name>Object 3</name></item>'
				. '</root>';

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	public function testArrayOfMultipleComplexObjectsToXml()
	{
		// Create objects
		$object1 = $this->createComplexObject(1);
		$object2 = $this->createComplexObject(2);
		$object3 = $this->createComplexObject(3);

		// Create aray of objects
		$data = array($object1, $object2, $object3);

		// Convert to XML, with root of 'root' and tag wrappers of 'item'
		$actual_xml = TypeConverter::toXml($data);

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration() . '<root>'
				. '<item><id>1</id><name>Object 1</name><foo><bar>string</bar><baz>'
				. '<item><id>1.1</id><name>Object 1.1</name></item>'
				. '<item><id>1.2</id><name>Object 1.2</name></item>'
				. '<item><id>1.3</id><name>Object 1.3</name></item>'
				. '</baz></foo></article>'
				. '<item><id>2</id><name>Object 2</name><foo><bar>string</bar><baz>'
				. '<item><id>2.1</id><name>Object 2.1</name></item>'
				. '<item><id>2.2</id><name>Object 2.2</name></item>'
				. '<item><id>2.3</id><name>Object 2.3</name></item>'
				. '</baz></foo></article>'
				. '<item><id>3</id><name>Object 3</name><foo><bar>string</bar><baz>'
				. '<item><id>3.1</id><name>Object 3.1</name></item>'
				. '<item><id>3.2</id><name>Object 3.2</name></item>'
				. '<item><id>3.3</id><name>Object 3.3</name></item>'
				. '</baz></foo></item>'
				. '</root>';

		// Now check with single string tag.

		// Convert to XML, with root of 'root' and tag wrappers of 'item'
		$actual_xml = TypeConverter::toXml($data, 'root', 'article');

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration() . '<root>'
				. '<article><id>1</id><name>Object 1</name><foo><bar>string</bar><baz>'
				. '<article><id>1.1</id><name>Object 1.1</name></article>'
				. '<article><id>1.2</id><name>Object 1.2</name></article>'
				. '<article><id>1.3</id><name>Object 1.3</name></article>'
				. '</baz></foo></article>'
				. '<article><id>2</id><name>Object 2</name><foo><bar>string</bar><baz>'
				. '<article><id>2.1</id><name>Object 2.1</name></article>'
				. '<article><id>2.2</id><name>Object 2.2</name></article>'
				. '<article><id>2.3</id><name>Object 2.3</name></article>'
				. '</baz></foo></article>'
				. '<article><id>3</id><name>Object 3</name><foo><bar>string</bar><baz>'
				. '<article><id>3.1</id><name>Object 3.1</name></article>'
				. '<article><id>3.2</id><name>Object 3.2</name></article>'
				. '<article><id>3.3</id><name>Object 3.3</name></article>'
				. '</baz></foo></article>'
				. '</root>';

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);

		// Now check with array of tags.

		// Convert to XML, with root of 'root' and tag wrappers of 'item'
		$actual_xml = TypeConverter::toXml($data, 'root', array('outer', 'inner'));

		// Compose expected XML structure
		$expected_xml = $this->getXmlDeclaration() . '<root>'
				. '<outer><id>1</id><name>Object 1</name><foo><bar>string</bar><baz>'
				. '<inner><id>1.1</id><name>Object 1.1</name></inner>'
				. '<inner><id>1.2</id><name>Object 1.2</name></inner>'
				. '<inner><id>1.3</id><name>Object 1.3</name></inner>'
				. '</baz></foo></outer>'
				. '<outer><id>2</id><name>Object 2</name><foo><bar>string</bar><baz>'
				. '<inner><id>2.1</id><name>Object 2.1</name></inner>'
				. '<inner><id>2.2</id><name>Object 2.2</name></inner>'
				. '<inner><id>2.3</id><name>Object 2.3</name></inner>'
				. '</baz></foo></outer>'
				. '<outer><id>3</id><name>Object 3</name><foo><bar>string</bar><baz>'
				. '<inner><id>3.1</id><name>Object 3.1</name></inner>'
				. '<inner><id>3.2</id><name>Object 3.2</name></inner>'
				. '<inner><id>3.3</id><name>Object 3.3</name></inner>'
				. '</baz></foo></outer>'
				. '</root>';

		$this->assertXMLStringEqualsXmlString($expected_xml, $actual_xml);
	}

	protected function createObject($id)
	{
		$object = new \stdClass;
		$object->id = $id;
		$object->name = 'Object ' . $id;

		return $object;
	}

	protected function createComplexObject($id)
	{
		$object = $this->createObject($id);
		$object->foo = (object) null; // Creates property foo as stdClass
		// Add a string
		$object->foo->bar = 'string';
		// Add an array of objects to a property
		$object->foo->baz = array(
			$this->createObject($id . '.1'),
			$this->createObject($id . '.2'),
			$this->createObject($id . '.3'),
		);

		return $object;
	}

	protected function getXmlDeclaration()
	{
		return '<?xml version="1.0" encoding="utf-8"?>';
	}
}
