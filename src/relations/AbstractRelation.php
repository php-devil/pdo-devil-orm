<?php
namespace PhpDevil\ORM\relations;

class AbstractRelation implements RelationObserver
{
    /**
     * Модель, запрос которой инициировал подгрузку связей
     * @var string
     */
    protected $leftClassName;

    /**
     * Поле левой модели
     * @var
     */
    protected $leftFieldName;

    /**
     * Присоединяемая по связи модель
     * @var string
     */
    protected $rightClassName;

    /**
     * Поле правой модели
     * @var
     */
    protected $rightFieldName;

    /**
     * Предконфигурированные поля для запроса
     * @var array
     */
    protected $queriedColumns = [];

    /**
     * Реальные реализации различных типов связей между моделями
     * @var array
     */
    private static $classes = [
        'BelongsTo' => BelongsTo::class,
    ];

    /**
     * Добавление предварительно сконфигурированного поля для запроса
     * @param $alias
     */
    public function addQueryAlias($alias)
    {
        if (!in_array($alias, $this->queriedColumns)) {
            $this->queriedColumns[] = $alias;
        }
    }

    /**
     * Создание связи по типу и конфигурации
     * @param $config
     * @param $leftClassName
     * @return mixed
     */
    public static function create($config, $leftClassName)
    {
        if (isset(self::$classes[$config['type']] )) {
            $realClass = self::$classes[$config['type']];
            return new $realClass($config, $leftClassName);
        } else {
            return null;
        }
    }

    protected function __construct($config, $leftClassName)
    {
        $this->leftClassName = $leftClassName;
        if ('self' === $config['model']) {
            $this->rightClassName = $this->leftClassName;
        } else {
            $this->rightClassName = $config['model'];
        }
        $this->leftFieldName = $config['here'];
        $this->rightFieldName = $config['there'];
    }
}