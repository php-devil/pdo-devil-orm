<?php
namespace PhpDevil\ORM\attributes;

abstract class AbstractAttribute implements AttributeInterface
{
    protected $config = [];

    protected $owner = null;

    protected $name = null;

    protected $value = null;

    public function isValid()
    {
        return false;
    }

    public function hasErrors()
    {
        return false;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->getValue();
    }

    public function setOwner($model)
    {
        $this->owner = $model;
        return $this;
    }

    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }
}