<?php
namespace PhpDevil\ORM\models;

interface ActiveRecordCollectionInterface
{
    /**
     * Оповещение обозревателей поля $name о появлении в выборке значения $value
     * @param $name
     * @param $value
     * @return mixed
     */
    public function notifyValueSet($name, $value);
}