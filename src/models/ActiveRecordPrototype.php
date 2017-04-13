<?php
namespace PhpDevil\ORM\models;
use PhpDevil\ORM\behavior\DefaultBehavior;
use PhpDevil\ORM\Connector;
use PhpDevil\ORM\queries\QueryBuildable;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;
use PhpDevil\ORM\QueryBuilder\queries\SelectQueryBuilder;


abstract class ActiveRecordPrototype extends AbstractModel implements ActiveRecordInterface
{
    /**
     * Определение поведения модели в целом
     * (дерево NS, списки с сортировкой по полю, с русной сортировкой по полю, маппер и т.п.)
     * @return string
     */
    public static function mainBehavior()
    {
        return DefaultBehavior::class;
    }

    /**
     * Класс, которым будет представлен набор записей после запроса search или findAll
     * @return string
     */
    public static function collectionClass()
    {
        return ActiveRecordCollection::class;
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
     * Сортировка выборок по умолчанию
     * @return mixed
     */
    public function getDefaultOrderBy()
    {
        return (static::mainBehavior())::defaultOrderBy(static::class);
    }

    /**
     * Перемещение записи(узла) выше(левее) в пределах родительского
     * @return mixed
     */
    public function moveLeft()
    {
        return (static::mainBehavior())::moveLeft($this);
    }

    /**
     * Перемещение записи(узла) ниже(правее) в пределах родительского
     * @return mixed
     */
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

    /**
     * Определение, является ли запись новой (по факту наличия значения первичного ключа)
     * @return bool
     */
    public function isNewRecord()
    {
        return !((bool) $this->getRoleValue('id'));
    }

    /**
     * Сохранение записи в БД
     * todo добавить $this->accessControl(insert|delete)
     */
    public function save()
    {
        if ($this->isNewRecord()) {
            if ((static::mainBehavior())::beforeInsert($this)) {
                $id = static::query()->insert($this->getAttributes())->execute()->getInsertID();
                $this->setRoleValue('id', $id);
                (static::mainBehavior())::afterInsert($this);
            }
        } else {
            if ((static::mainBehavior())::beforeUpdate($this)) {
                static::query()->update(
                    $this->getAttributes(),
                    QueryCriteria::createAND([[$this->getRoleField('id'), '=', $this->getRoleValue('id')]])
                )->execute();
                (static::mainBehavior())::afterUpdate($this);
            }
        }
    }

    public function remove()
    {
        if ($this->accessControl('delete') && ((static::mainBehavior())::beforeDelete($this))) {
            static::query()->delete(QueryCriteria::createAND([[$this->getRoleField('id'), '=', $this->getRoleValue('id')]]))
                ->execute();
            (static::mainBehavior())::afterDelete($this);
        }
    }

    #====== Правила валидации автрибутов, завязанные на БД

    public function validateUnique($attribute)
    {
        return true;
    }
}