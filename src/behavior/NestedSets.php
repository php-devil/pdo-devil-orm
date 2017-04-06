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

    /**
     * Добавление ключей дерева к полям селект запроса
     * @param $class
     * @param $columns
     * @return mixed
     */
    public static function prepareSelectColumns($class, $columns)
    {
        $queried = [
            $class::getRoleFieldStatic('id'),
            $class::getRoleFieldStatic('tree-left'),
            $class::getRoleFieldStatic('tree-level'),
            $class::getRoleFieldStatic('tree-right'),
            $class::getRoleFieldStatic('tree-parent')
        ];
        foreach ($columns as $col) {
            if (!in_array($col, $queried)) $queried[] = $col;
        }
        return $queried;
    }
}