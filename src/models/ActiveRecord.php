<?php
namespace PhpDevil\ORM\models;


abstract class ActiveRecord extends ActiveRecordPrototype
{
    /**
     * Коллекция, которой принадлежит запись
     * @var null
     */
    protected $collection = null;

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
     * @return ActiveRecordCollectionInterface
     */
    public static function findAll($columns = null)
    {
        $collectionClass = static::collectionClass();
        if (null !== $columns) {
            $columns = (static::mainBehavior())::prepareSelectColumns(static::class, $columns);
        }
        $collection = new $collectionClass(
            static::class,
            static::query()->select($columns)->orderBy(static::getDefaultOrderBy())
        );
        return $collection;
    }
}