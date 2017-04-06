<?php
namespace PhpDevil\ORM\behavior;

abstract class DefaultBehavior
{
    public static function typeName()  {return 'default';}
    public static function typeClass() {return 'table';}
    public static function defaultOrderBy($class) { return null; }
    public static function prepareSelectColumns($class, $columns) {return $columns;}
}