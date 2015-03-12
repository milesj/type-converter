# TypeConverter #

*Documentation may be outdated or incomplete as some URLs may no longer exist.*

*Warning! This codebase is deprecated and will no longer receive support; excluding critical issues.*

A class that handles the detection and conversion of certain resource formats / content types into other formats. The current formats are supported: XML, JSON, Array, Object, Serialized.

* Type detection
* Automatic analysis and conversion
* Powerful XML to array conversion with multiple structure options

## Installation ##

Install by manually downloading the library or defining a [Composer dependency](http://getcomposer.org/).

```javascript
{
    "require": {
        "mjohnson/type-converter": "2.0.0"
    }
}
```

## Detection ##

The first thing the class is capable of is detecting certain resource and format types. To detect you can use `is()` or the type specific methods `isArray()`, `isObject()`, `isXml()`, `isJson()` and `isSerialized()`. The `is()` method works a bit different in that it returns a string of the type while the others return a boolean.

```php
use mjohnson\utility\TypeConverter;

$array = array();

TypeConverter::is($array); // returns "array"
TypeConverter::isArray($array); // true
TypeConverter::isObject($array); // false
TypeConverter::isXml($array); // false
TypeConverter::isJson($array); // false
TypeConverter::isSerialized($array); // false
```

Now how are these type detections different than using `is_object()` or `json_decode()` you say. Well not much but it does offer proper error handling and usage where applicable.

## Conversion ##

The primary function of this class is to convert one type to another, ala array to object. To do this you will use the respective methods: `toArray()`, `toObject()`, `toJson()`, `toXml()`, `toSerialize()`. When converting, nested and deep associations are respected.

```php
use mjohnson\utility\TypeConverter;

$array = array('foo' => 'bar');

TypeConverter::toArray($array);
// array('foo' => 'bar');

TypeConverter::toObject($array);
// stdClass { foo => bar }

TypeConverter::toJson($array);
// {"foo":"bar"}

TypeConverter::toSerialize($array);
// a:1:{s:3:"foo";s:3:"bar";}

TypeConverter::toXml($array);
// <?xml version="1.0" encoding="utf-8"?><root><foo>bar</foo></root>
```

## XML to Array ##

When converting XML to an array, it can be rather complicated and tedious to parse in attributes. To counter-act this, multiple types of XML conversion have been supplied, they are:

* `XML_NONE` - Disregards XML attributes and only return the value of the node.
* `XML_MERGE` - Merge attributes and the value into a single dimension. The key for the node value will be "value".
* `XML_GROUP` - Group the attributes into a key "attributes" and the value into a key of "value".
* `XML_OVERWRITE` - Attributes will only be returned if no value is present.

Each one of these types have their uses and by default `XML_GROUP` is used when calling `toArray()` from an XML document. Personally, I find `XML_MERGE` to be the most versatile but overwrites are possible. To use the type you want, call `xmlToArray()`.

```php
$xml = file_get_contents('path.xml');
mjohnson\utility\TypeConverter::xmlToArray($xml, TypeConverter::XML_NONE);
```

Lets show off a few examples of these in action, starting with our XML document.

```markup
<?xml version="1.0" encoding="utf-8"?>
<unit>
    <name>Barbarian</name>
    <life max="150">50</life>
    <mana max="250">100</mana>
    <stamina>15</stamina>
    <vitality>20</vitality>
    <dexterity evade="5%" block="10%" />
    <agility turnRate="1.25" acceleration="5" />
    <armors items="6">
        <armor defense="15">Helmet</armor>
        <armor defense="25">Shoulder Plates</armor>
        <armor defense="50">Breast Plate</armor>
        <armor defense="10">Greaves</armor>
        <armor defense="10">Gloves</armor>
        <armor defense="25">Shield</armor>
    </armors>
    <weapons items="6">
        <sword damage="25">Broadsword</sword>
        <sword damage="30">Longsword</sword>
        <axe damage="20">Heavy Axe</axe>
        <axe damage="25">Double-edged Axe</axe>
        <polearm damage="50" range="3" speed="slow">Polearm</polearm>
        <mace damage="15" speed="fast">Mace</mace>
    </weapons>
    <items>
        <potions>
            <potion>Health Potion</potion>
            <potion>Mana Potion</potion>
        </potions>
        <keys>
            <chestKey>Chest Key</chestKey>
            <bossKey>Boss Key</bossKey>
        </keys>
        <food>Food</food>
        <scrap count="25">Scrap</scrap>
    </items>
</unit>
```

And when we use `XML_NONE`, all attributes are disregarded.

```
Array
(
    [name] => Barbarian
    [life] => 50
    [mana] => 100
    [stamina] => 15
    [vitality] => 20
    [dexterity] => 
    [agility] => 
    [armors] => Array
        (
            [armor] => Array
                (
                    [0] => Helmet
                    [1] => Shoulder Plates
                    [2] => Breast Plate
                    [3] => Greaves
                    [4] => Gloves
                    [5] => Shield
                )
        )
    [weapons] => Array(
            [sword] => Array
                (
                    [0] => Broadsword
                    [1] => Longsword
                )
            [axe] => Array
                (
                    [0] => Heavy Axe
                    [1] => Double-edged Axe
                )
            [polearm] => Polearm
            [mace] => Mace
        )
    [items] => Array
        (
            [potions] => Array
                (
                    [potion] => Array
                        (
                            [0] => Health Potion
                            [1] => Mana Potion
                        )
                )
            [keys] => Array
                (
                    [chestKey] => Chest Key
                    [bossKey] => Boss Key
                )
            [food] => Food
            [scrap] => Scrap
        )
)
```

And when using `XML_MERGE`, the attributes and value are merged into a single dimension of the array.

```
Array
(
    [name] => Barbarian
    [life] => Array
        (
            [value] => 50
            [max] => 150
        )
    [mana] => Array
        (
            [value] => 100
            [max] => 250
        )
    [stamina] => 15
    [vitality] => 20
    [dexterity] => Array
        (
            [value] => 
            [evade] => 5%
            [block] => 10%
        )
    [agility] => Array
        (
            [value] => 
            [turnRate] => 1.25
            [acceleration] => 5
        )
    [armors] => Array
        (
            [armor] => Array
                (
                    [0] => Array
                        (
                            [value] => Helmet
                            [defense] => 15
                        )
                    [1] => Array
                        (
                            [value] => Shoulder Plates
                            [defense] => 25
                        )
                    [2] => Array
                        (
                            [value] => Breast Plate
                            [defense] => 50
                        )
                    [3] => Array
                        (
                            [value] => Greaves
                            [defense] => 10
                        )
                    [4] => Array
                        (
                            [value] => Gloves
                            [defense] => 10
                        )
                    [5] => Array
                        (
                            [value] => Shield
                            [defense] => 25
                        )
                )
            [items] => 6
        )
    [weapons] => Array
        (
            [sword] => Array
                (
                    [0] => Array
                        (
                            [value] => Broadsword
                            [damage] => 25
                        )
                    [1] => Array
                        (
                            [value] => Longsword
                            [damage] => 30
                        )
                )
            [axe] => Array
                (
                    [0] => Array
                        (
                            [value] => Heavy Axe
                            [damage] => 20
                        )
                    [1] => Array
                        (
                            [value] => Double-edged Axe
                            [damage] => 25
                        )
                )
            [polearm] => Array
                (
                    [value] => Polearm
                    [damage] => 50
                    [range] => 3
                    [speed] => slow
                )
            [mace] => Array
                (
                    [value] => Mace
                    [damage] => 15
                    [speed] => fast
                )
            [items] => 6
        )
    [items] => Array
        (
            [potions] => Array
                (
                    [potion] => Array
                        (
                            [0] => Health Potion
                            [1] => Mana Potion
                        )
                )
            [keys] => Array
                (
                    [chestKey] => Chest Key
                    [bossKey] => Boss Key
                )
            [food] => Food
            [scrap] => Array
                (
                    [value] => Scrap
                    [count] => 25
                )
        )
)
```

And when using `XML_GROUP`, the attributes and value are contained in their respective keys (the most verbose format).

```
Array
(
    [name] => Barbarian
    [life] => Array
        (
            [attributes] => Array
                (
                    [max] => 150
                )
            [value] => 50
        )
    [mana] => Array
        (
            [attributes] => Array
                (
                    [max] => 250
                )
            [value] => 100
        )
    [stamina] => 15
    [vitality] => 20
    [dexterity] => Array
        (
            [attributes] => Array
                (
                    [evade] => 5%
                    [block] => 10%
                )
            [value] => 
        )
    [agility] => Array
        (
            [attributes] => Array
                (
                    [turnRate] => 1.25
                    [acceleration] => 5
                )
            [value] => 
        )
    [armors] => Array
        (
            [attributes] => Array
                (
                    [items] => 6
                )
            [value] => Array
                (
                    [armor] => Array
                        (
                            [0] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [defense] => 15
                                        )
                                    [value] => Helmet
                                )
                            [1] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [defense] => 25
                                        )
                                    [value] => Shoulder Plates
                                )
                            [2] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [defense] => 50
                                        )
                                    [value] => Breast Plate
                                )
                            [3] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [defense] => 10
                                        )
                                    [value] => Greaves
                                )
                            [4] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [defense] => 10
                                        )
                                    [value] => Gloves
                                )
                            [5] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [defense] => 25
                                        )
                                    [value] => Shield
                                )
                        )
                )
        )
    [weapons] => Array
        (
            [attributes] => Array
                (
                    [items] => 6
                )
            [value] => Array
                (
                    [sword] => Array
                        (
                            [0] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [damage] => 25
                                        )
                                    [value] => Broadsword
                                )
                            [1] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [damage] => 30
                                        )
                                    [value] => Longsword
                                )
                        )
                    [axe] => Array
                        (
                            [0] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [damage] => 20
                                        )
                                    [value] => Heavy Axe
                                )
                            [1] => Array
                                (
                                    [attributes] => Array
                                        (
                                            [damage] => 25
                                        )
                                    [value] => Double-edged Axe
                                )
                        )
                    [polearm] => Array
                        (
                            [attributes] => Array
                                (
                                    [damage] => 50
                                    [range] => 3
                                    [speed] => slow
                                )
                            [value] => Polearm
                        )
                    [mace] => Array
                        (
                            [attributes] => Array
                                (
                                    [damage] => 15
                                    [speed] => fast
                                )
                            [value] => Mace
                        )
                )
        )
    [items] => Array
        (
            [potions] => Array
                (
                    [potion] => Array
                        (
                            [0] => Health Potion
                            [1] => Mana Potion
                        )
                )
            [keys] => Array
                (
                    [chestKey] => Chest Key
                    [bossKey] => Boss Key
                )
            [food] => Food
            [scrap] => Array
                (
                    [attributes] => Array
                        (
                            [count] => 25
                        )
                    [value] => Scrap
                )
        )
)
```

And finally we have `XML_OVERWRITE` which only returns attribute values if no node value exists. This method is great for parsing XML documents that only use attributes for data. However, it does not respect nested nodes.

```
Array
(
    [name] => Barbarian
    [life] => Array
        (
            [max] => 150
        )
    [mana] => Array
        (
            [max] => 250
        )
    [stamina] => 15
    [vitality] => 20
    [dexterity] => Array
        (
            [evade] => 5%
            [block] => 10%
        )
    [agility] => Array
        (
            [turnRate] => 1.25
            [acceleration] => 5
        )
    [armors] => Array
        (
            [items] => 6
        )
    [weapons] => Array
        (
            [items] => 6
        )
    [items] => Array
        (
            [potions] => Array
                (
                    [potion] => Array
                        (
                            [0] => Health Potion
                            [1] => Mana Potion
                        )
                )
            [keys] => Array
                (
                    [chestKey] => Chest Key
                    [bossKey] => Boss Key
                )
            [food] => Food
            [scrap] => Array
                (
                    [count] => 25
                )
        )
)
```
