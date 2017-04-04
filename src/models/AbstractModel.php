<?php
namespace PhpDevil\ORM\models;
use PhpDevil\Common\Configurator\Loader;

abstract class AbstractModel
{
    /**
     * Путь к файлу конфигурации модели
     * @return null
     */
    public static function getConfigSource(){ return null; }

    /**
     * Доступ к данным конфигурационного массива
     * @return mixed
     */
    public static function getConfig()  { return (Loader::getInstance()->load(static::getConfigSource()))['data']; }
    public static function attributes() { return (static::getConfig())['attributes']; }

    /**
     * Создание экземпляра класса модели возможно вызовом метода model()
     * AbstractModel constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Создание экземпляра класса модели
     */
    public static function model()
    {
    }
}