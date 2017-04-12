<?php
namespace PhpDevil\ORM\models;

class ActiveRecordCollection implements ActiveRecordCollectionInterface
{
    /**
     * Прототип строки данных
     * @var ActiveRecordInterface
     */
    protected $modelPrototype;

    public function __construct()
    {

    }
}