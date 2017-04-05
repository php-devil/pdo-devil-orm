<?php
namespace PhpDevil\ORM\providers;

use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;

abstract class AbstractDataProvider
{
    protected $modelClass;

    protected $query;

    final protected function raiseError($message)
    {
        //todo: Ошибка конфигурации параметра
        die($message);
    }

    /**
     * Имя класса модели для формирования строк результатов запроса
     * @param $className
     */
    protected function setPrototype($className)
    {
        if (is_object($className)) $this->modelClass = get_class($className);
        else $this->modelClass = $className;
    }

    /**
     * Установка запроса
     * @param QueryBuilderInterface $query
     */
    protected function setQuery(QueryBuilderInterface $query)
    {
        $this->query = $query;
    }

    final public function __construct($config)
    {
        foreach ($config as $k=>$v) {
            if (method_exists($this, 'set' . ucfirst($k))) {
                call_user_func([$this, 'set' . ucfirst($k)], $v);
            } else {
                $this->raiseError('unknown parameter ' . $k);
            }
        }
    }
}