<?php
namespace PhpDevil\ORM\providers;
use PhpDevil\ORM\queries\QueryExecutable;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;
use PhpDevil\ORM\relations\AbstractRelation;

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
     * Получение атрибута связанной модели
     * @param $member
     * @param null $column
     * @return mixed
     */
    public function getAttributeFor($member, $column = null)
    {
        if (false === ($dot = strpos($column, '.'))) {
            $alias = null;
        } else {
            $alias = substr($column, $dot+1);
            $column = substr($column, 0, $dot);
        }
        if (isset($this->relations[$column])) {
            return $this->relations[$column]->getAttributeFor($member, $alias);
        } else {
            return null;
        }
    }

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
    private function addRelatedField($relation, $queryAlias = null)
    {
        if (isset($this->relationsConfigs[$relation])) {
            if (!isset($this->relations[$relation])) {
                $this->relations[$relation] = AbstractRelation::create($this->relationsConfigs[$relation], $this->modelClass);
                $this->addObserver($relation, $this->relationsConfigs[$relation]['here']);
            }
            if ($queryAlias) $this->relations[$relation]->addQueryAlias($queryAlias);
        }
    }

    /**
     * Приведение запроса к исполняемому типу
     */
    protected function makeExecutableQuery()
    {
        return new QueryExecutable(($this->modelClass)::db(), $this->query);
    }

    private $_recordSet = null;

    private $_preloaded = null;

    /**
     * Если запрос был выборочным и произошло обращение к невыбранному атрибуту
     * перегружаем тот же запрос по всем полям и переназначаем атрибуты загруженным записям
     * @param $attribute
     */
    public function checkIfQueried($attribute)
    {
        if (!in_array($attribute, $this->_preloaded)) {
            $this->query->select(null);
            $prepared = $this->makeExecutableQuery()->execute();
            $pk = ($this->modelClass)::getRoleFieldStatic('id');
            while ($row = $prepared->fetch()) {
                if (isset($this->_recordSet[$row[$pk]])) {
                    $this->_recordSet[$row[$pk]]->setAttributes($row);
                }
            }
            $this->_preloaded = array_keys(($this->modelClass)::attributes());
        }
    }

    /**
     * Все записи результата запроса в виде инстансов класса, переданного в качестве
     * прототипа для провайдера данных
     * @param $rowCallBack
     * @return array
     */
    public function all(callable $rowCallBack = null)
    {
        if (empty($this->_recordSet)){
            $prepared = $this->makeExecutableQuery()->execute();
            $prototype = ($this->modelClass)::model();
            $prototype->setOwner($this);
            $this->_recordSet = [];
            while ($row = $prepared->fetch()) {
                $record = clone($prototype);
                foreach ($row as $k=>$v) {
                    $this->notifyObservers($k, $v);
                }
                $record->setAttributes($row);
                if (null !== $rowCallBack) {
                    call_user_func($rowCallBack, $record);
                }
                $this->_recordSet[$record->getRoleValue('id')] = $record;
            }
        }
        return $this->_recordSet;
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
        foreach ($this->relationsConfigs as $k=>$v) $this->addRelatedField($k, null);
        $attributes = ($this->modelClass)::attributes();
        $realQueryColumns = [];
        if (is_array($columns['main'])) foreach ($columns['main'] as $k=>$v) {
            if (false === ($dot = strrpos($v, '.'))) {
                if (isset($attributes[$v])) $realQueryColumns[] = $v;
            } else {
                $relationName = substr($v, 0, $dot);
                $this->addRelatedField($relationName, substr($v, $dot + 1));
            }
        }
        $this->query->select($realQueryColumns);
        $this->_preloaded = empty($realQueryColumns) ? array_keys(($this->modelClass)::attributes()) : $realQueryColumns;
    }
}