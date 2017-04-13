<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\queries\QueryExecutable;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;
use PhpDevil\ORM\relations\AbstractRelation;

/**
 * Class ActiveRecordCollection
 * Набор записей ActiveRecordInterface - результат select запроса
 * @package PhpDevil\ORM\models
 */
class ActiveRecordCollection implements ActiveRecordCollectionInterface
{
    /**
     * Созданные связи для запроса выборки
     * @var array
     */
    protected $_relations = [];

    /**
     * Ссылки на созданные связи по именам обозреваемых полей
     * @var array
     */
    protected $_observers = [];

    /**
     * Поля модели, участвующие в запросе
     * @var array
     */
    protected $columnsFromSelf = [];

    /**
     * Построитель запроса с заданными полями
     * @var QueryBuilderInterface
     */
    protected $query;

    /**
     * Прототип строки данных
     * @var ActiveRecordInterface
     */
    protected $modelPrototype;

    /**
     * Результаты запроса
     * @var ActiveRecordInterface array
     */
    protected $rows = [];

    public function getPrototype()
    {
        return $this->modelPrototype;
    }

    /**
     * Получение отношения
     * @param ActiveRecordInterface $row
     * @param $alias
     * @return mixed
     */
    public function getByAlias(ActiveRecordInterface $row, $alias)
    {
        if (false === ($dot = strpos($alias, '.'))) {
            // todo: trigger error
        } else {
            $relation = substr($alias, 0, $dot);
            if (isset($this->_relations[$relation])) {
                return $this->_relations[$relation]->getValueFor($row, substr($alias, $dot + 1));
            } else {
                echo 'Unknown Rel';
            }
        }
    }

    /**
     * Добавление поля связанной модели
     * @param $relation
     * @param $name
     */
    protected function createRelated($relation, $name)
    {
        $relations = $this->modelPrototype->relations();
        if (isset($relations[$relation])) {
            if (!isset($this->_relations[$relation])) {
                $this->_relations[$relation] = AbstractRelation::create($relations[$relation], get_class($this->modelPrototype));
                $column = $relations[$relation]['here'];
                $this->_observers[$column][$relation] = &$this->_relations[$relation];
            }
            $this->_relations[$relation]->addQueryAlias($name);
        }
    }

    /**
     * Разделение полей запроса ы зависимости от принадлежнояти к моделям
     */
    protected function prepareColumns()
    {
        $queriedColumns = $this->query->getFieldsNames();
        foreach ($queriedColumns['main'] as $colName) {
            if (false === ($dot = strpos($colName, '.'))) {
                if (!in_array($colName, $this->columnsFromSelf)) $this->columnsFromSelf[] = $colName;
            } else {
                $relName = substr($colName, 0, $dot);
                $alias = substr($colName, $dot+1);
                $this->createRelated($relName, $alias);
            }
        }
    }

    /**
     * Оповещение обозревателей поля $name о появлении в выборке значения $value
     * @param $name
     * @param $value
     * @return mixed
     */
    public function notifyValueSet($name, $value)
    {
        if (isset($this->_observers[$name])) foreach ($this->_observers[$name] as $observer) {
            $observer->addFilterValue($value);
        }
    }

    /**
     * Выборка всех записей, подходящих под условие сконфигурированного запроса
     * @param callable $callback - функция, принимающая 1 аргумент - модель
     * @return $this
     */
    public function all(callable $callback = null)
    {
        $this->query->select($this->columnsFromSelf);
        $selection = (new QueryExecutable($this->modelPrototype->db(), $this->query))->execute();
        while ($row = $selection->fetch()) {
            $record = clone($this->modelPrototype);
            $record->setAttributes($row);
            if ($callback) call_user_func($callback, $record);
            $this->rows[$record->getRoleValue(id)] = $record;
        }
        return $this;
    }

    public function rows()
    {
        return $this->rows;
    }

    /**
     * ActiveRecordCollection constructor.
     *  - Создание прототипа модели
     *  - Назначение модели членства в коллекции для корректной обработки связей
     * @param $resultClassName
     * @param QueryBuilderInterface $query
     */
    public function __construct($resultClassName, QueryBuilderInterface $query)
    {
        $this->modelPrototype = $resultClassName::model();
        $this->query = $query;
        $this->modelPrototype->setCollection($this);
        $this->prepareColumns();
    }
}