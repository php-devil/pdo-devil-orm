<?php
namespace PhpDevil\ORM\relations;

abstract class AbstractRelation implements RelationObserver
{
    /**
     * Конкретные классы отношений по типам
     * @var array
     */
    protected static $classes = [
        'BelongsTo' => BelongsTo::class,
    ];

    protected $leftClassName;

    protected $leftField;

    protected $rightClassName;

    protected $rightField;

    protected $queryColumns = [];

    protected $filterValues = [];

    final public function addQueryAlias($alias)
    {
        if (!in_array($alias, $this->queryColumns)) {
            $this->queryColumns[] = $alias;
        }
        return $this;
    }

    final public function addFilterValue($value)
    {
        if (!in_array($value, $this->filterValues)) {
            $this->filterValues[] = $value;
        }
        return $this;
    }

    public static function create($config, $leftClassName)
    {
        if (isset(static::$classes[$config['type']])) {
            $class = static::$classes[$config['type']];
            return new $class($config, $leftClassName);
        }
    }

    final public function __construct($config, $leftClassName)
    {
        $this->leftClassName = $leftClassName;
        if ('self' === $config['model']) {
            $this->rightClassName = $leftClassName;
        } else {
            $this->rightClassName = $config['model'];
        }
        $this->leftField  = $config['here'];
        $this->rightField = $config['there'];
    }
}