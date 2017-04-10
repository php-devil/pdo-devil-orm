<?php
namespace PhpDevil\ORM\behavior;
use PhpDevil\ORM\models\ActiveRecordInterface;

abstract class DefaultBehavior
{
    public static function typeName()  {return 'default';}
    public static function typeClass() {return 'table';}
    public static function defaultOrderBy($class) { return null; }
    public static function prepareSelectColumns($class, $columns) {return $columns;}

    public static function beforeInsert($row)
    {
        return $row->getAttributes();
    }

    public static function afterInsert($row)
    {
        return true;
    }

    public static function beforeUpdate($row)
    {
        return $row->getAttributes();
    }

    public static function afterUpdate($row)
    {
        return true;
    }

    /**
     * Смещение узла (ветви) выше (левее)
     * @param ActiveRecordInterface $row
     * @return mixed
     */
    public static function movLeft(ActiveRecordInterface $row)
    {
        return null;
    }

    /**
     * Смещение узла (ветви) ниже (правее)
     * @param ActiveRecordInterface $row
     * @return mixed
     */
    public static function moveRight(ActiveRecordInterface $row)
    {
        return null;
    }


}