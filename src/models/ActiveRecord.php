<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\Connector;
use PhpDevil\ORM\queries\QueryBuildable;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;
use PhpDevil\ORM\QueryBuilder\queries\SelectQueryBuilder;
use PhpDevil\ORM\behavior\NestedSets;

abstract class ActiveRecord extends AbstractModel implements ActiveRecordInterface
{
    /**
     * Определение поведения модели в целом
     * (дерево NS, списки с сортировкой по полю, с русной сортировкой по полю, маппер и т.п.)
     * @return mixed
     */
    public static function mainBehavior()
    {
        return NestedSets::class;
    }

    /**
     * Построитель запроса, предварительно сконфигурированный для данной модели
     * После указания параметров запроса вызывается метод execute();
     * @return QueryBuildable
     */
    public static function query()
    {
        return new QueryBuildable(static::db(), static::tableName());
    }

    /**
     * Поиск всех строк связанной таблицы
     * @param null|array $columns
     * @return SelectQueryBuilder
     */
    public static function findAll($columns = null)
    {
        $query = new SelectQueryBuilder;
        if (null !== $columns) $columns = (static::mainBehavior())::prepareSelectColumns(static::class, $columns);
        $query->select($columns)->from(static::tableName())->orderBy((static::mainBehavior())::defaultOrderBy(static::class));
        return $query;
    }

    public function moveLeft()
    {
        return (static::mainBehavior())::moveLeft($this);
    }

    public function moveRight()
    {
        return (static::mainBehavior())::moveRight($this);
    }

    /**
     * Поиск строки по первичному ключу
     * @param $value
     * @return mixed
     */
    public static function findByPK($value)
    {
        if ($row = static::query()
                ->select()
                    ->where(QueryCriteria::createAND([[static::getRoleFieldStatic('id'), '=', $value]]))
                    ->execute()
                    ->fetch()
        ) {
            $model = static::model();
            $model->setAttributes($row);
            return $model;
        } else {
            return null;
        }
    }

    /**
     * Параметры конфигурации модели
     * @return string|array
     */
    public static function tableName() { return (static::getConfig())['table']['name']; }


    /**
     * Соединение с базой данных
     * @return mixed
     */
    public static function db()
    {
        return Connector::getInstance()->getConnection((static::getConfig())['table']['connection']);
    }

    public function isNewRecord()
    {
        return (bool) $this->getRoleValue('id');
    }

    public function save()
    {
        if ($this->isNewRecord()) {
            $insertArray = (static::mainBehavior())::beforeInsert($this);
            $this->insert($insertArray);
            (static::mainBehavior())::beforeUpdate($this);
        } else {
            $updateArray = (static::mainBehavior())::beforeUpdate($this);
            $this->update($updateArray);
            (static::mainBehavior())::afterUpdate($this);
        }
    }

    protected function insert($attributes)
    {

    }

    protected function update($attributes)
    {

    }

    #====== Правила валидации автрибутов, завязанные на БД

    public function validateUnique($attribute)
    {
        return true;
    }
}