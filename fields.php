<?php namespace Landscape;

interface iField
{
    public function getSQLUpdate();
    public function getSQLCreate();
    public function getSQLInsert();
    public function __construct($name, $args);
    public function setValue($value);
    public function getValue();
}

abstract class Field implements iField
{
    abstract public function getSQLUpdate();
    abstract public function getSQLCreate();
    abstract public function getSQLInsert();

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
    public function getSQLCreate()
    {
        return $this->name." TEXT NOT NULL";
    }

    public function getSQLUpdate()
    {
        return "UPDATE :table SET ".$this->name." = ".$this->value." WHERE :condition";
    }
    public function getSQLInsert()
    {}
}

?>
