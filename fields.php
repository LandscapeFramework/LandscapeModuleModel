<?php namespace Landscape;

use \DateTime;

interface iField
{
    public function getSQLDefinition();
    public function __construct($name, $args);
    public function setValue($value);
    public function getValue();
    public function getRealValue();
}

abstract class Field implements iField
{
    abstract public function getSQLDefinition();

    protected $name;
    protected $args;

    protected $value;

    public function __construct($name, $args)
    {
        $this->name = $name;
        $this->args = $args;
    }
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getRealValue()
    {
        return $this->getValue();
    }
}

class TextField extends Field
{
    public function getSQLDefinition()
    {
        return "TEXT";
    }
}

class NumberField extends Field
{
    public function getSQLDefinition()
    {
        return "INTEGER";
    }
}

class LinkField extends Field
{
    protected $type;

    public function __construct($name, $args)
    {
        $this->type = $args[0];
        parent::__construct($name, $args);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setValue($value)
    {
        if(is_int($value))
        {
            $this->value = $value;
        }
        else if($value instanceof $this->type)
        {
            $this->value = $value->get('ID');
        }
        else
        {
            $this->value = intval($value);
        }
    }

    public function getSQLDefinition()
    {
        return "INTEGER";
    }

    public function getRealValue()
    {
        return new $this->type("find", "ID=$this->value");
    }
}

class TimeField extends Field
{
    public function getSQLDefinition()
    {
        return "INTEGER";
    }

    public function setValue($value)
    {
        if(is_int($value))
        {
            $this->value = $value;
        }
        else if($value instanceof DateTime)
        {
            $this->value = $value->getTimestamp();
        }
        else
        {
            $this->value = intval($value);
        }
    }

    public function getRealValue()
    {
        $ret = new DateTime('now');
        $ret->setTimestamp($this->value);
        return $ret;
    }
}

class BoolField extends Field
{
    public function getSQLDefinition()
    {
        return "INTEGER";
    }

    public function setValue($value)
    {
        if(is_int($value))
        {
            if($value == 1 || $value == 0)
                $this->value = $value;
            else
                print("Invalid number for BoolField\n");
        }
        else if(is_bool($value))
        {
            $this->value = intval($value);
        }
        else
        {
            $this->value = intval(boolval($value));
        }
    }

    public function getRealValue()
    {
        return boolval($this->value);
    }
}

?>
