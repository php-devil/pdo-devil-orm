<?php
namespace PhpDevil\ORM\providers;
use PhpDevil\ORM\models\ActiveRecordCollectionInterface;

/**
 * Class DataList
 * Список записей ActiveRecord
 * @package PhpDevil\ORM\providers
 */
class DataList extends AbstractDataProvider
{
    /**
     * Коллекция записей
     * @var ActiveRecordCollectionInterface
     */
    protected $collection;

    public function getPrototype()
    {
        return $this->collection->getPrototype();
    }

    public function __construct(ActiveRecordCollectionInterface $collection)
    {
        $this->collection = $collection;
    }
}