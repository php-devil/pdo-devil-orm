<?php
class PagesStructure extends \PhpDevil\ORM\models\ActiveRecord
{
    /**
     * Определение поведения модели в целом
     * (дерево NS, списки с сортировкой по полю, с русной сортировкой по полю, маппер и т.п.)
     * @return mixed
     */
    public static function mainBehavior()
    {
        return \PhpDevil\ORM\behavior\NestedSets::class;
    }

    /**
     * Путь к конфигрурационному файлу модели
     * @return string
     */
    public static function getConfigSource()
    {
        return dirname(__DIR__) . '/configs/pages_structure.php';
    }
}