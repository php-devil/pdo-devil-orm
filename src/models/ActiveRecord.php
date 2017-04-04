<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\QueryBuilder\queries\SelectQueryBuilder;

abstract class ActiveRecord extends AbstractModel implements ActiveRecordInterface
{
    /**
     * Поиск всех строк связанной таблицы
     * @param null|array $columns
     * @return SelectQueryBuilder
     */
    public static function findAll($columns = null)
    {
        $query = new SelectQueryBuilder;
        $query->select($columns)->from(static::tableName());
        return $query;
    }

    /**
     * Параметры конфигурации модели
     * @return string|array
     */
    public static function tableName() { return (static::getConfig())['table']['name']; }
}