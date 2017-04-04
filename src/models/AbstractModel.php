<?php
namespace PhpDevil\ORM\models;
use PhpDevil\Common\Configurator\Loader;

abstract class AbstractModel
{
    /**
     * Доступ к данным конфигурационного массива
     * @return mixed
     */
    public static function getConfig(){ return (Loader::getInstance()->load(static::getConfigSource()))['data']; }

    protected function __construct()
    {

    }

    public static function model()
    {

    }
}