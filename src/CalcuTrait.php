<?php namespace adamkelso\DoubleA;

trait CalcuTrait {
    private $_calculated = array(),
            $reflection;
    // Assumes there is a private $settable array on the class.

    public function __set($name, $value)
    {
        if(array_key_exists($name, $this->_settable)) {
            $this->_settable[$name] = $value;

            // We empty the calculated values because someone changed
            // the input values. So, calculations need to be remade.
            if(!property_exists($this, 'leaveCacheOnSet') || $this->leaveCacheOnSet == false) {
                $this->_calculated = [];
            }
        }elseif(property_exists($this, 'catch')) {
            $this->catch[$name] = $value;
        }else{
            throw new \Exception('Tried to set unknown property on '.__CLASS__.' class: '.$name.' with value '.$value);
        }
    }

    public function __get($name)
    {
        if(empty($this->reflection)) {
            $this->reflection = new \ReflectionClass($this);
        }

        if(array_key_exists($name, $this->_calculated))
        {
            return $this->_calculated[$name];
        }

        else if(array_key_exists($name, $this->_settable) && $this->_settable[$name] !== null)
        {
            return $this->_settable[$name];
        }

        else if(method_exists($this, $name))
        {
        	$params = $this->reflection->getMethod($name)->getParameters();
        	$args = [];
        	$missing = [];

        	// Internal IOC container
        	foreach($params as $p)
        	{
        		if(isset($this->{$p->name}) || method_exists($this, $p->name))
        		{
        			$args[] = $this->{$p->name};
        			continue;
        		}

        		$missing[] = $p->name;
        	}

        	if(count($missing) > 0)
        	{
        		throw new \Exception('The following properties need to be set on the '.__CLASS__.' object before the method '.$name.' can be called: '.implode(', ', $missing).'. ');
        	}

            $this->_calculated[$name] = call_user_func_array([$this, $name], $args);

            if(method_exists($this, 'AfterCalc'))
            {
                $this->_calculated[$name] = $this->AfterCalc($this->_calculated[$name]);
            }

            return $this->_calculated[$name];
        }

        else{
            throw new \Exception('Asked for undefined property on '.__CLASS__.' class: '.$name);
        }
    }

    // Useful to check from outside the class if a property has been set or calculated already.
    public function __isset($name)
    {
        return (array_key_exists($name, $this->_calculated)
            || (array_key_exists($name, $this->_settable) && $this->_settable[$name] != null)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Check
    |--------------------------------------------------------------------------
    |
    | This method is kind of an internal IOC container, in that it checks that the
    | requested values have been set. If they haven't and they can be
    | calculated, they are. Otherwise, an exception is thrown.
    |
    */
    protected function Check(...$args)
    {
        $debug = debug_backtrace();

        $method = $debug[1]['function'];

        $missing = array();

        foreach($args as $arg)
        {
            if(array_key_exists($arg, $this->_settable) && $this->_settable[$arg] === null)
            {
                array_push($missing, $arg);
            }

            else if(!array_key_exists($arg, $this->_calculated) && method_exists($this, $arg))
            {
                $this->_calculated[$arg] = $this->$arg();
            }
        }

        if(count($missing) > 0)
        {
            throw new \Exception('The following properties need to be set on the '.get_class($this).' before the method '.$method.' can be called: '.implode(', ', $missing).'. ');
        }
    }
}