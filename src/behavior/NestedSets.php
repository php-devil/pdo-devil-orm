<?php
namespace PhpDevil\ORM\behavior;

class NestedSets extends DefaultBehavior
{
    public static function typeName()  {return 'nestedsets';}
    public static function typeClass() {return 'tree';}

    /**
     * Для NS дкревьев сортировка по умоляанию - возрастание левого ключа
     * @param $class
     * @return array
     */
    public static function defaultOrderBy($class)
    {
        return [$class::getRoleFieldStatic('tree-left') => true];
    }
}