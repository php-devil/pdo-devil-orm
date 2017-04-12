<?php
namespace PhpDevil\ORM\models;

interface ActiveRecordInterface
{
    /**
     * Установка коллекции записей для выборок данных
     * @param ActiveRecordCollectionInterface $collection
     * @return mixed
     */
    public function setCollection(ActiveRecordCollectionInterface $collection);

    public static function findAll($columns = null);


}