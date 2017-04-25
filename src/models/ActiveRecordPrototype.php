<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\behavior\DefaultBehavior;
use PhpDevil\ORM\Connector;
use PhpDevil\ORM\queries\QueryBuildable;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;
use PhpDevil\ORM\QueryBuilder\queries\SelectQueryBuilder;


abstract class ActiveRecordPrototype extends AbstractModel implements ActiveRecordInterface
{
    /**
     * Определение поведения модели в целом
     * (дерево NS, списки с сортировкой по полю, с русной сортировкой по полю, маппер и т.п.)
     * @return string
     */
    public static function mainBehavior()
    {
        return DefaultBehavior::class;
    }

    /**
     * Класс, которым будет представлен набор записей после запроса search или findAll
     * @return string
     */
    public static function collectionClass()
    {
        return ActiveRecordCollection::class;
    }

    /**
     * Построитель запроса, предварительно сконфигурированный для данной модели
     * После указания параметров запроса вызывается метод execute();
     * @return QueryBuildable
     */
    public static function query()
    {
        return new QueryBuildable(static::db(), static::tableName());
    }

    /**
     * Сортировка выборок по умолчанию
     * @return mixed
     */
    public function getDefaultOrderBy()
    {
        return (static::mainBehavior())::defaultOrderBy(static::class);
    }

    /**
     * Перемещение записи(узла) выше(левее) в пределах родительского
     * @return mixed
     */
    public function moveLeft()
    {
        return (static::mainBehavior())::moveLeft($this);
    }

    /**
     * Перемещение записи(узла) ниже(правее) в пределах родительского
     * @return mixed
     */
    public function moveRight()
    {
        return (static::mainBehavior())::moveRight($this);
    }

    /**
     * Параметры конфигурации модели
     * @return string|array
     */
    public static function tableName() { return (static::getConfig())['table']['name']; }


    /**
     * Соединение с базой данных
     * @return mixed
     */
    public static function db()
    {
        return Connector::getInstance()->getConnection((static::getConfig())['table']['connection']);
    }

    /**
     * Определение, является ли запись новой (по факту наличия значения первичного ключа)
     * @return bool
     */
    public function isNewRecord()
    {
        return !((bool) $this->getRoleValue('id'));
    }



    #====== Правила валидации автрибутов, завязанные на БД

    public function validateUnique($attribute)
    {
        return true;
    }
}