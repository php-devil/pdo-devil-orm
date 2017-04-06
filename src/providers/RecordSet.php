<?php
namespace PhpDevil\ORM\providers;
use PhpDevil\ORM\queries\QueryExecutable;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;
use PhpDevil\ORM\relations\AbstractRelation;
use PhpDevil\ORM\relations\RelationObserver;

/**
 * Class RecordSet
 * Набр записей. Результат запроса Select
 * @package PhpDevil\ORM\providers
 */
class RecordSet extends AbstractDataProvider implements RelationObservable
{
    /**
     * Созданные связи по именам, как они указаны в исходной модели (прототипе)
     * @var array
     */
    protected $relationsConfigs = [];

    /**
     * Созданные классы связей модели
     * @var array
     */
    protected $relations = [];

    /**
     * Обозреватели значений полей
     * @var array
     */
    protected $observers = [];

    /**
     * Добавление связи как обозревателя значений поля
     * @param $relationName
     * @param $notifyFieldName
     */
    public function addObserver($relationName, $notifyFieldName)
    {
        if (!isset($this->observers[$notifyFieldName])) $this->observers[$notifyFieldName] = [];
        $this->observers[$notifyFieldName][] = &$this->relations[$relationName];
    }

    /**
     * Оповещение обозревателей полей о появлении нового значения в выгрузке
     * @param $field
     * @param $value
     */
    public function notifyObservers($field, $value)
    {
        if (isset($this->observers[$field])) foreach($this->observers[$field] as $observer) {
            $observer->addNotification($value);
        }
    }

    /**
     * Создание инстанса связи, регистрация его как обозревателя для передачи значений ключа по мере
     * формирования результат выгрузки основного запроса и передачи сконфигурированных полей
     * для предварительной загрузки данных при обращении к связи
     * @param $relation
     * @param $queryAlias
     */
    private function addRelatedField($relation, $queryAlias)
    {
        if (isset($this->relationsConfigs[$relation])) {
            if (!isset($this->relations[$relation])) {
                $this->relations[$relation] = AbstractRelation::create($this->relationsConfigs[$relation], $this->modelClass);
                $this->addObserver($relation, $this->relationsConfigs[$relation]['here']);
            }
            $this->relations[$relation]->addQueryAlias($queryAlias);
        }
    }

    /**
     * Приведение запроса к исполняемому типу
     */
    protected function makeExecutableQuery()
    {
        return new QueryExecutable(($this->modelClass)::db(), $this->query);
    }

    /**
     * Все записи результата запроса в виде инстансов класса, переданного в качестве
     * прототипа для провайдера данных
     * @param $rowCallBack
     */
    public function all(callable $rowCallBack = null)
    {
        $prepared = $this->makeExecutableQuery()->execute();
        $prototype = ($this->modelClass)::model();

        $result = [];

        while ($row = $prepared->fetch()) {
            $record = clone($prototype);
            foreach ($row as $k=>$v) {
                $this->notifyObservers($k, $v);
            }
            $record->setAttributes($row);
            if (null !== $rowCallBack) {
                call_user_func($rowCallBack, $record);
            }
            $result[$record->getRoleValue('id')] = $record;
        }

        print_r($result);
    }

    /**
     * При установке запроса провайдеру списка записей модели
     * из результатов запроса убираем все, что к модели не относится. Для полей, подтягиваемых из других
     * иоделей создаем классы связей, которые будут загружены по мере попыток доступа к даннымю
     *
     * @param QueryBuilderInterface $query
     */
    protected function setQuery(QueryBuilderInterface $query)
    {
        parent::setQuery($query);
        $columns = $this->query->getFieldsNames();
        $this->relationsConfigs = ($this->modelClass)::relations();
        $attributes = ($this->modelClass)::attributes();
        $realQueryColumns = [];
        foreach ($columns['main'] as $k=>$v) {
            if (false === ($dot = strrpos($v, '.'))) {
                if (isset($attributes[$v])) $realQueryColumns[] = $v;
            } else {
                $relationName = substr($v, 0, $dot);
                $this->addRelatedField($relationName, substr($v, $dot + 1));
            }
        }
        $this->query->select($realQueryColumns);
    }
}