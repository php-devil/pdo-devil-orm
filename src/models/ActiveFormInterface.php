<?php
namespace PhpDevil\ORM\models;

interface ActiveFormInterface
{
    /**
     * Ссылка на файл конфигурации модели
     * @return mixed
     */
    public static function getConfigSource();
}