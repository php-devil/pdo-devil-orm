<?php
namespace PhpDevil\ORM\behavior;
use PhpDevil\ORM\models\ActiveRecordInterface;

abstract class DefaultBehavior
{
    public static function typeName()  {return 'default';}
    public static function typeClass() {return 'table';}
    public static function defaultOrderBy($class) { return [$class::getRoleFieldStatic('id')=>true]; }

    /**
     * Добавление ключей дерева к полям селект запроса
     * @param $class
     * @return array
     */
    public static function getSelectFields($class)
    {
        return [
            $class::getRoleFieldStatic('id'),
        ];
    }

    public static function beforeInsert(ActiveRecordInterface $row)
    {
        return true;
    }

    public static function afterInsert(ActiveRecordInterface $row)
    {
        return true;
    }

    public static function beforeUpdate(ActiveRecordInterface $row)
    {
        return true;
    }

    public static function afterUpdate(ActiveRecordInterface $row)
    {
        return true;
    }

    public static function beforeDelete(ActiveRecordInterface $row)
    {
        return true;
    }

    public static function afterDelete(ActiveRecordInterface $row)
    {
        return true;
    }

    /**
     * Смещение узла (ветви) выше (левее)
     * @param ActiveRecordInterface $row
     * @return mixed
     */
    public static function moveLeft(ActiveRecordInterface $row)
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