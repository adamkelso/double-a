<?php

namespace AdamKelso\DoubleA\Tests;

use AdamKelso\DoubleA\CalcuTrait;
use PHPUnit_Framework_TestCase;

class Object1 {
    use CalcuTrait;

    private $settable = [
        'Year_Built' => null,
        'Square_Footage' => null,
        'Total_Value' => null
    ];

    public $BeforeCalc, $AfterCalc, $count = 0;

    private function No_Arguments()
    {
        return 'no arguments needed';
    }

    private function Age($Year_Built)
    {
        $this->count++;
        return date('Y') - $Year_Built;
    }

    private function Value_Per_SqFt($Square_Footage, $Total_Value)
    {
        return $Total_Value / $Square_Footage;
    }

    private function Adjust_Value_For_Age($Value_Per_SqFt, $Age)
    {
        return $Value_Per_SqFt - ($Value_Per_SqFt * ($Age * .01));
    }

    public function getCalculated()
    {
        return $this->calculated;
    }

    public function getSettable()
    {
        return $this->settable;
    }
}

class Object2 {
    use CalcuTrait;

    private $settable = [
        'Year_Built' => null,
        'Square_Footage' => null,
        'Municiple_Tax' => null,
        'Total_Value' => null
    ];

    public function Age($Year_Built)
    {
        return date('Y') - $Year_Built;
    }

    public function Municiple_Value($Total_Value, $Municiple_Tax)
    {
        return $Total_Value * (1 - $Municiple_Tax);
    }

    public function Value_Per_SqFt($Square_Footage, $Municiple_Value)
    {
        return $Municiple_Value / $Square_Footage;
    }

    public function Adjust_Value_For_Age($Value_Per_SqFt, $Age)
    {
        return $Value_Per_SqFt - ($Value_Per_SqFt * ($Age * .01));
    }

    public function getCalculated()
    {
        return $this->calculated;
    }
}

class CalcuTraitTest extends PHPUnit_Framework_TestCase {

    public function testBasicSettersAndGetterMagicMethods()
    {
        $obj = new Object1();

        // Everything starts out nulled
        $this->assertEquals($obj->getSettable(),[
            'Year_Built' => null,
            'Square_Footage' => null,
            'Total_Value' => null
        ]);

        // Basic setters
        $obj->Year_Built = 1994;
        $obj->Square_Footage = 2000;
        $obj->Total_Value = 100000;

        // Values properly set
        $this->assertEquals($obj->getSettable(),[
            'Year_Built' => 1994,
            'Square_Footage' => 2000,
            'Total_Value' => 100000
        ]);

        // Basic getters
        $this->assertEquals($obj->Year_Built, 1994);
        $this->assertEquals($obj->Square_Footage, 2000);
        $this->assertEquals($obj->Total_Value, 100000);
    }

    /**
     * @expectedException   Exception
     * @expectedExceptionMessage Tried to set unknown property on AdamKelso\DoubleA\Tests\Object1 class: Meaning_Of_Life with value 42
     */
    public function testNonExistingSetters()
    {
        $obj = new Object1();
        $obj->Meaning_Of_Life = 42;
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Asked for undefined property on AdamKelso\DoubleA\Tests\Object1 class: Meaning_Of_Life
     */
    public function testNonExistingGetters()
    {
        $obj = new Object1();
        $val = $obj->Meaning_Of_Life;
    }

    public function testIssetMagicMethod()
    {
        // Default value is null
        $obj = new Object1();
        $this->isFalse(isset($obj->Year_Built));

        // Give it a value
        $obj->Year_Built = 1995;
        $this->isTrue(isset($obj->Year_Built));

        // Calculated attribute should not exist yet.
        $this->isFalse(isset($obj->No_Arguments));

        $age = $obj->No_Arguments;

        // Should properly show the new calculated value;
        $this->isTrue(isset($obj->NoArguments));
    }

    public function testDynamicMethodCalculationViaGets()
    {
        $obj = new Object1();
        $obj->Year_Built = 1995;
        $obj->Square_Footage = 2000;
        $obj->Total_Value = 200000;

        $age = date('Y') - 1995;

        $this->assertEquals($obj->Age, $age);
        $this->assertEquals($obj->Value_Per_SqFt, 200000 / 2000);
        $this->assertEquals($obj->Adjust_Value_For_Age, 100 - (100 * ($age * .01)));
    }

    public function testCalculatedValuesCachedAndErasedOnSet()
    {
        $obj = new Object1();

        // Empty array to start
        $this->assertEquals($obj->getCalculated(), []);

        $obj->Year_Built = 1995;
        $obj->Square_Footage = 2000;
        $obj->Total_Value = 200000;

        $age = $obj->Age;
        $valPrSqFt = $obj->Value_Per_SqFt;

        // Check that age is in calculated array
        $this->assertEquals($obj->getCalculated(), ['Age' => date('Y') - 1995, 'Value_Per_SqFt' => 100]);

        $obj->Year_Built = 2000;

        // Age should no longer be in calculated array
        $this->assertEmpty($obj->getCalculated());
    }

    public function testCalculatedValuesCachedAndOnlyRunOnce()
    {
        $obj = new Object1();

        $this->assertEquals(0, $obj->count);

        $obj->Year_Built = 2000;
        $age = $obj->Age;

        $this->assertEquals(1, $obj->count);

        $age = $obj->Age;
        $age = $obj->Age;

        $this->assertEquals(1, $obj->count);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The following properties need to be set on the AdamKelso\DoubleA\Tests\Object1 object before the method Value_Per_SqFt can be called: Square_Footage, Total_Value.
     */
    public function testRequiredParamsAreRequired()
    {
        $obj = new Object1();
        $adjusted = $obj->Value_Per_SqFt;
    }

    public function testMethodsCascade()
    {
        $obj = new Object1();
        $obj->Year_Built = 1995;
        $obj->Total_Value = 200000;
        $obj->Square_Footage = 2000;

        $val = $obj->Adjust_Value_For_Age;

        $calculated = $obj->getCalculated();

        $this->assertArrayHasKey('Age', $calculated);
        $this->assertArrayHasKey('Value_Per_SqFt', $calculated);
        $this->assertArrayHasKey('Adjust_Value_For_Age', $calculated);
    }

    public function testThatMultipleImplementationsAreSelfContained()
    {
        $obj1 = new Object1();
        $obj2 = new Object2();

        $obj2->Year_Built = $obj1->Year_Built = 2005;
        $obj2->Square_Footage = $obj1->Square_Footage = 2000;
        $obj2->Total_Value = $obj1->Total_Value = 200000;
        $obj2->Municiple_Tax = .03;

        $arr = [$obj1, $obj2];

        usort($arr, function($a, $b) {
            return $a->Adjust_Value_For_Age > $b->Adjust_Value_For_Age;
        });

        $this->assertArrayHasKey('Municiple_Value', $obj2->getCalculated());
        $this->assertArrayNotHasKey('Municiple_Value', $obj1->getCalculated());
    }
}