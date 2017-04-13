<?php
namespace PhpDevil\ORM\models;
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
                return  'MaybeProperty or Relation ))';
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
     * Инициалиазися коллекции записей и начального SQL запроса
     * @param null $columns
     * @param QueryCriteriaInterface $where
     * @return ActiveRecordCollectionInterface
     */
    public static function findAll($columns = null, QueryCriteriaInterface $where = null)
    {
        $collectionClass = static::collectionClass();
        if (null !== $columns) {
            $columns = (static::mainBehavior())::prepareSelectColumns(static::class, $columns);
        }
        $collection = new $collectionClass(
            static::class,
            static::query()->select($columns)->where($where)->orderBy(static::getDefaultOrderBy())
        );
        return $collection;
    }
}