<?php
namespace PhpDevil\ORM\relations;

use PhpDevil\ORM\Connector;

abstract class AbstractRelation implements RelationObserver
{


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
        $classes = Connector::getInstance()->getRelationsClasses();
        if (isset($classes[$config['type']])) {
            $class = $classes[$config['type']];
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