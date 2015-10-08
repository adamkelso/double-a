<?php

namespace AdamKelso\DoubleA\Tests;

use AdamKelso\DoubleA\Enumerable;
use PHPUnit_Framework_TestCase;

class EnumerableTest extends PHPUnit_Framework_TestCase {

    use Enumerable;

    const First = 1;
    const Second = 2;
    const Third = 5;
    const Last = 37;

    public function testAllReturnsArrayOfConstants()
    {
        $this->assertEquals([
            'First' => 1,
            'Second' => 2,
            'Third' => 5,
            'Last' => 37
        ], $this->all());
    }

    public function testKeysReturnsArrayOfKeys()
    {
        $this->assertEquals([
            'First', 'Second', 'Third', 'Last'
        ], $this->keys());
    }

    public function testValuesReturnsArrayOfValues()
    {
        $this->assertEquals([
            1, 2, 5, 37
        ], $this->values());
    }

    public function testKeyByValueReturnsProperKey()
    {
        $this->assertEquals('Third', $this->keyByValue(5));
        $this->assertEquals('Second', $this->keyByValue(2));
        $this->assertEquals('Last', $this->keyByValue(37));
        $this->assertEquals('First', $this->keyByValue(1));
    }

    public function testValueByStringKeyReturnsProperValue()
    {
        $this->assertEquals(5, $this->valueByStringKey('Third'));
        $this->assertEquals(2, $this->valueByStringKey('Second'));
        $this->assertEquals(1, $this->valueByStringKey('First'));
        $this->assertEquals(37, $this->valueByStringKey('Last'));
    }
}