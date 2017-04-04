<?php
namespace PhpDevil\ORM\models;


interface ActiveRecordInterface
{
    /**
     * Ссылка на файл конфигурации модели
     * @return mixed
     */
    public static function getConfigSource();

    public static function findAll();
}