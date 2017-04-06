<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\Connector;
use PhpDevil\ORM\QueryBuilder\queries\SelectQueryBuilder;
use PhpDevil\ORM\behavior\NestedSets;

abstract class ActiveRecord extends AbstractModel implements ActiveRecordInterface
{
    /**
     * Определение поведения модели в целом
     * (дерево NS, списки с сортировкой по полю, с русной сортировкой по полю, маппер и т.п.)
     * @return mixed
     */
    public static function mainBehavior()
    {
        return NestedSets::class;
    }

    /**
     * Поиск всех строк связанной таблицы
     * @param null|array $columns
     * @return SelectQueryBuilder
     */
    public static function findAll($columns = null)
    {
        $query = new SelectQueryBuilder;
        if (null !== $columns) $columns = (static::mainBehavior())::prepareSelectColumns(static::class, $columns);
        $query->select($columns)->from(static::tableName())->orderBy((static::mainBehavior())::defaultOrderBy(static::class));
        return $query;
    }

    /**
     * Параметры конфигурации модели
     * @return string|array
     */
    public static function tableName() { return (static::getConfig())['table']['name']; }
    public static function typeName()  { return (static::mainBehavior())::typeName();   }
    public static function typeClass() { return (static::mainBehavior())::typeClass();  }

    public static function db()
    {
        return Connector::getInstance()->getConnection((static::getConfig())['table']['connection']);
    }
}