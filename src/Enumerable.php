<?php namespace AdamKelso\DoubleA;

trait Enumerable {
    public static function all()
    {
        $ref = new \ReflectionClass(self::class);

        return $ref->getConstants();
    }

    public static function keys()
    {
        $ref = new \ReflectionClass(self::class);

        return array_keys($ref->getConstants());
    }

    public static function values()
    {
        $ref = new \ReflectionClass(self::class);

        return array_values($ref->getConstants());
    }

    public static function keyByValue($val)
    {
        $ref = new \ReflectionClass(self::class);

        foreach($ref->getConstants() as $key => $value)
        {
            if($value == $val)
            {
                return $key;
            }
        }
    }

    public static function valueByStringKey($key)
    {
        return constant(self::class.'::'.$key);
    }
}