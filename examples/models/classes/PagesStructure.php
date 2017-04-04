<?php
class PagesStructure extends \PhpDevil\ORM\models\ActiveRecord
{
    /**
     * Путь к конфигрурационному файлу модели
     * @return string
     */
    public static function getConfigSource()
    {
        return dirname(__DIR__) . '/configs/pages_structure.php';
    }
}