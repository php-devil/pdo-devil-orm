<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteriaInterface;

/**
 * Class ActiveRecord
 * Модель (строка таблицы в БД)
 * @package PhpDevil\ORM\models
 */
abstract class ActiveRecord extends ActiveRecordPrototype
{
    /**
     * Коллекция, которой принадлежит запись
     * @var null
     */
    protected $collection = null;

    /**
     * Отношения для работы без коллекции записей
     * @var array
     */
    protected $relations = [];

    /**
     * Добавление отношения в качестве атрибута
     * @param $name
     * @return mixed
     */
    public function getRelationAsAttribute($name)
    {
        if (!isset($this->relations[$name])) {
            if ($rel = $this->getRelation($name)) {
                $this->relations[$name] = $rel->preloadSingle($this);
            }
        }
        return $this->relations[$name];
    }

    /**
     * Получение атрибута по алиасу с учетом коллекции и связей
     * @param $attribute
     * @return string
     */
    public function getAttribute($attribute)
    {
        if (false === ($dot = strpos($attribute, '.'))) {
            if (isset($this->_attributes[$attribute])) {
                return $this->_attributes[$attribute];
            } else {
                if (null === $this->collection) {
                    return $this->getRelationAsAttribute($attribute);
                } else {
                    // догрузка незапрошенного поля в коллекции
                }
            }
        } else {
            if (null !== $this->collection) {
                return $this->collection->getByAlias($this, $attribute);
            }
        }
    }

    /**
     * Установка коллекции записей для выборок данных
     * @param ActiveRecordCollectionInterface $collection
     * @return mixed
     */
    public function setCollection(ActiveRecordCollectionInterface $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Установка значений атрибутов с учетом принадлежности к коллекии
     * @param $arr
     */
    public function setAttributes($arr)
    {
        foreach ($arr as $k=>$v) {
            if (null !== $this->collection) $this->collection->notifyValueSet($k, $v);
            $this->setAttributeValue($k, $v);
        }
    }

    /**
     * Поиск по значению первичного ключа
     * @param $value
     * @return null|ActiveRecord
     */
    public static function findByPK($value)
    {
        return static::findOne(QueryCriteria::createAND([[static::getRoleFieldStatic('id'), '=', $value]]));
    }

    /**
     * Поиск первой записи, удовлетворяющей критерию
     * @param $arrayOrCriteria
     * @return null|static
     */
    public static function findOne($arrayOrCriteria)
    {
        if (is_array($arrayOrCriteria)) $arrayOrCriteria = QueryCriteria::createAND($arrayOrCriteria);
        if ($row = static::query()->select()->where($arrayOrCriteria)->limit(1)->execute()->fetch()) {
            $model = static::model();
            $model->setAttributes($row);
            return $model;
        } else {
            return null;
        }
    }

    /**
     * Обязательные ключи для select запросов
     * @return mixed
     */
    public static function getSelectFields()
    {
        return (static::mainBehavior())::getSelectFields(static::class);
    }

    /**
     * Подготовка полей select запросов (добавление обязательных)
     * @param $columns
     * @return array|mixed
     */
    final public static function prepareSelectColumns($columns)
    {
        $queried = static::getSelectFields();
        foreach ($columns as $col) {
            if (!in_array($col, $queried)) $queried[] = $col;
        }
        return $queried;
    }

    /**
     * Инициалиазися коллекции записей и начального SQL запроса
     * @param null $columns
     * @param QueryCriteriaInterface $where
     * @return ActiveRecordCollectionInterface
     */
    public static function findAll($columns = null, QueryCriteriaInterface $where = null)
    {
        $collectionClass = static::collectionClass();
        if (null !== $columns) {
            $columns = static::prepareSelectColumns($columns);
        }
        $collection = new $collectionClass(
            static::class,
            static::query()->select($columns)->where($where)->orderBy(static::getDefaultOrderBy())
        );
        return $collection;
    }
}