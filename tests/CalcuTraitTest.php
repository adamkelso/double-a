<?php

namespace AdamKelso\DoubleA\Tests;

use AdamKelso\DoubleA\CalcuTrait;
use PHPUnit_Framework_TestCase;

class Object1 {
    use CalcuTrait;


}

class Object2 {
    use CalcuTrait;


}

class CalcuTraitTest extends PHPUnit_Framework_TestCase {

    public function testCase()
    {
        $this->assertTrue(true);
    }

}