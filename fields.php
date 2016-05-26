<?php namespace Landscape;

interface iField
{
    public function getSQLDefinition();
    public function __construct($name, $args);
    public function setValue($value);
    public function getValue();
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
}

class TextField extends Field
{
    public function getSQLDefinition()
    {
        return "TEXT NOT NULL";
    }
}

?>
