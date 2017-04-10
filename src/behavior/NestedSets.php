<?php
namespace PhpDevil\ORM\behavior;
use PhpDevil\ORM\models\ActiveRecordInterface;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;
use PhpDevil\ORM\QueryBuilder\components\QueryExpression;

/**
 * Class NestedSets
 * Поведение ключей таблицы для деревьев вложенных множеств (NestedSets)
 * @package PhpDevil\ORM\behavior
 */
class NestedSets extends DefaultBehavior
{
    /**
     * Имя типа поведения
     * @return string
     */
    public static function typeName()
    {
        return 'nestedsets';
    }

    /**
     * Имя класса набора поведений
     * @return string
     */
    public static function typeClass()
    {
        return 'tree';
    }

    /**
     * Для NS дкревьев сортировка по умоляанию - возрастание левого ключа
     * @param $class
     * @return array
     */
    public static function defaultOrderBy($class)
    {
        return [$class::getRoleFieldStatic('tree-left') => true];
    }

    /**
     * Добавление ключей дерева к полям селект запроса
     * @param $class
     * @param $columns
     * @return mixed
     */
    public static function prepareSelectColumns($class, $columns)
    {
        $queried = [
            $class::getRoleFieldStatic('id'),
            $class::getRoleFieldStatic('tree-left'),
            $class::getRoleFieldStatic('tree-level'),
            $class::getRoleFieldStatic('tree-right'),
            $class::getRoleFieldStatic('tree-parent')
        ];
        foreach ($columns as $col) {
            if (!in_array($col, $queried)) $queried[] = $col;
        }
        return $queried;
    }

    /**
     * Смещение узла (ветви) дерева на один выше (левее)
     * @param ActiveRecordInterface $row
     * @return void
     */
    public static function moveLeft(ActiveRecordInterface $row)
    {
        $where = QueryCriteria::createAND([
            [$row->getRoleField('tree-parent'), '=', $row->getRoleValue('tree-parent')],
            [$row->getRoleField('tree-left'), '<', $row->getRoleValue('tree-left')],
        ]);
        $neighbour = $row::query()->select()
            ->where($where)
            ->orderBy([$row->getRoleField('tree-left') => false])
            ->limit(1, 1)
            ->execute()
            ->fetch();
        $keyValue = isset($neighbour[$row->getRoleField('id')]) ? $neighbour[$row->getRoleField('id')] : -1;
        static::moveNode($row, $row->getRoleValue('tree-parent'), $keyValue);
    }

    /**
     * Смещение узла (ветви) дерева ниже (правее)
     * @param ActiveRecordInterface $row
     * @return void
     */
    public static function moveRight(ActiveRecordInterface $row)
    {
        $where = QueryCriteria::createAND([
            [$row->getRoleField('tree-parent'), '=', $row->getRoleValue('tree-parent')],
            [$row->getRoleField('tree-left'), '>', $row->getRoleValue('tree-left')],
        ]);
        $neighbour = $row::query()->select()
            ->where($where)
            ->orderBy([$row->getRoleField('tree-left') => true])
            ->limit(1)
            ->execute()
            ->fetch();
        if ($neighbour) {
            static::moveNode($row, $row->getRoleValue('tree-parent'), $neighbour[$row->getRoleField('id')]);
        }
    }

    /**
     * Перемещение ветви дерева в новый узел
     *
     * @param ActiveRecordInterface $row
     * @param $newParentID
     * @param null $newBefore
     *    null - поместить узел как последний
     *      -1 - поместить узел первым
     *    в остальных случаях - ID узла, после (!) которого будет перемещаемый
     */
    public static function moveNode(ActiveRecordInterface $row, $newParentID, $newBefore = null)
    {
        $left_key      = $row->getRoleValue('tree-left');
        $level         = $row->getRoleValue('tree-level');
        $right_key     = $row->getRoleValue('tree-right');
        $leftKeyField  = $row->getRoleField('tree-left');
        $levelKeyField = $row->getRoleField('tree-level');
        $rightKeyField = $row->getRoleField('tree-right');

        if (null === $newParentID) {
            $level_up = 0;
        } else {
            if ($newParentNode = ($row::findByPK($newParentID))){
                $newParentNode = $newParentNode->getAttributes();
                $level_up = $newParentNode[$levelKeyField];
            } else {
                $level_up = 0;
                $newParentNode[$leftKeyField]  = 0;
                $newParentNode[$rightKeyField] = 1;
            }
        }

        switch ($newBefore) {
            case null:
                $right_key_near = $newParentNode[$rightKeyField] - 1;
                break;
            case -1:
                $right_key_near = $newParentNode[$leftKeyField];
                break;
            default:
                $newBeforeNode = $row::findByPK($newBefore)->getAttributes();
                $right_key_near = $newBeforeNode[$rightKeyField];
        }

        $skew_level = $level_up - $level + 1;
        $skew_tree  = $right_key - $left_key + 1;

        if ($right_key_near <= $right_key) {
            $skew_edit = $right_key_near - $left_key + 1;

            $query = $row::query()->update([
                $rightKeyField => [
                    QueryCriteria::createAND([[$leftKeyField, '>=', $left_key]]),
                    QueryExpression::math(['@'.$rightKeyField, '+', $skew_edit]),
                    [
                        QueryCriteria::createAND([[$rightKeyField, '<', $left_key]]),
                        QueryExpression::math(['@'.$rightKeyField, '+', $skew_tree]),
                        '@'.$rightKeyField
                    ]
                ],
                $levelKeyField => [
                    QueryCriteria::createAND([[$leftKeyField, '>=', $left_key]]),
                    QueryExpression::math(['@'.$levelKeyField, '+', $skew_level]),
                    '@'.$levelKeyField
                ],
                $leftKeyField => [
                    QueryCriteria::createAND([[$leftKeyField, '>=', $left_key]]),
                    QueryExpression::math(['@'.$leftKeyField, '+', $skew_edit]),
                    [
                        QueryCriteria::createAND([[$leftKeyField, '>', $right_key_near]]),
                        QueryExpression::math(['@'.$leftKeyField, '+', $skew_tree]),
                        '@'.$leftKeyField
                    ]
                ]
            ],QueryCriteria::createAND([[$rightKeyField, '>', $right_key_near], [$leftKeyField, '<', $right_key]]));

        } else {
            $skew_edit = $right_key_near - $left_key + 1 - $skew_tree;

            $query = $row::query()->update([
                $leftKeyField => [
                    QueryCriteria::createAND([[$rightKeyField, '<=', $right_key]]),
                    QueryExpression::math(['@'.$leftKeyField, '+', $skew_edit]),
                    [
                        QueryCriteria::createAND([[$leftKeyField, '>', $right_key]]),
                        QueryExpression::math(['@'.$leftKeyField, '-', $skew_tree]),
                        '@'.$leftKeyField
                    ]
                ],
                $levelKeyField => [
                    QueryCriteria::createAND([[$rightKeyField, '<=', $right_key]]),
                    QueryExpression::math(['@'.$levelKeyField, '+', $skew_level]),
                    '@'.$levelKeyField
                ],
                $rightKeyField => [
                    QueryCriteria::createAND([[$rightKeyField, '<=', $right_key]]),
                    QueryExpression::math(['@'.$rightKeyField, '+', $skew_edit]),
                    [
                        QueryCriteria::createAND([[$rightKeyField, '<=', $right_key_near]]),
                        QueryExpression::math(['@'.$rightKeyField, '-', $skew_tree]),
                        '@'.$rightKeyField
                    ]
                ]
            ], QueryCriteria::createAND([[$rightKeyField, '>', $left_key], [$leftKeyField, '<=', $right_key_near]]));
        }
        $query->execute();
    }

    /**
     * Подготовка записи перед вставкой
     * @param ActiveRecordInterface $row
     * @return array
     */
    public static function beforeInsert(ActiveRecordInterface $row)
    {
        $leftKeyField   = $row->getRoleField('tree-left');
        $levelKeyField  = $row->getRoleField('tree-level');
        $rightKeyField  = $row->getRoleField('tree-right');
        if ($parentNode = $row::findByPK($row->getRoleValue('tree-parent'))) {
            $parentNode = $parentNode->getAttributes();
            $row->setRoleValue('tree-left',  $parentNode[$rightKeyField]);
            $row->setRoleValue('tree-level', $parentNode[$levelKeyField]  + 1);
            $row->setRoleValue('tree-right', $parentNode[$rightKeyField] + 1);
            $row::query()->update([
                $rightKeyField => QueryExpression::math(['@'.$rightKeyField, '+', 2]),
                $leftKeyField  => [
                    QueryCriteria::createAND([[$leftKeyField, '>', $parentNode[$rightKeyField]]]),
                    QueryExpression::math(['@'.$leftKeyField, '+', 2]),
                    '@'.$leftKeyField
                ]
            ], QueryCriteria::createAND([[$rightKeyField, '>=', $parentNode[$rightKeyField]]]))->execute();
            return true;
        } elseif (0 == $row->getRoleValue('tree-parent')) {
            $values = $row::query()->select(['max_right_value' => QueryExpression::max($rightKeyField)])
                ->execute()->fetch();
            $maxRight = $values[$row->getRoleField('tree-right')];
            $row->setRoleValue('tree-left',  intval($maxRight) + 1);
            $row->setRoleValue('tree-level', 1);
            $row->setRoleValue('tree-right', intval($maxRight) + 2);
            return true;
        } else {
            // Добавление в несуществующий узел
            return false;
        }
    }

    /**
     * Подготовка записи перед обновлением
     * @param ActiveRecordInterface $row
     * @return array
     */
    public static function beforeUpdate(ActiveRecordInterface $row)
    {
        $oldValues = $row::findByPK($row->getRoleValue('id'));
        if ($oldValues) {
            $oldValues = $oldValues->getAttributes();
        } else {
            return false;
        }
        if ($oldValues[$row->getRoleField('tree-parent')] != $row->getRoleValue('tree-parent')) {
            static::moveNode($row, $row->getRoleValue('tree-parent'));
            $afterMove =  $row::findByPK($row->getRoleValue('id'))->getAttributes();
            $row->setRoleValue('tree-left',  $afterMove[$row->getRoleField('tree-left')]);
            $row->setRoleValue('tree-level', $afterMove[$row->getRoleField('tree-level')]);
            $row->setRoleValue('tree-right', $afterMove[$row->getRoleField('tree-right')]);
        }
        return true;
    }
}