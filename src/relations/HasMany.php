<?php
namespace PhpDevil\ORM\relations;
use PhpDevil\ORM\models\ActiveRecordInterface;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;
use PhpDevil\ORM\QueryBuilder\components\QueryExpression;

class HasMany extends AbstractRelation implements RelationObserver
{
    protected $right = null;

    protected function createRight()
    {
        if (null === $this->right) {
            $where = null;
            if (!empty($this->filterValues)) {
                $where = QueryCriteria::createAND([[$this->rightField, 'in', $this->filterValues]]);
            }
            $columns = ['key_value' => $this->rightField];
            foreach ($this->queryColumns as $col) {
                if (false === strpos($col, '.') && strpos($col, '()')) {
                    $col = substr($col, 0, strpos($col, '()'));
                    $columns[$col] = QueryExpression::$col();
                }
            }
            $query = ($this->rightClassName)::query()->select($columns)->groupBy([$this->rightField])->where($where)->execute();
            while($row = $query->fetch()) {
                $this->right[$row['key_value']] = $row;
            }
        }
    }

    public function getValueFor(ActiveRecordInterface $left, $alias)
    {
        $this->createRight();
        if (false === strpos($alias, '.') && strpos($alias, '()')) {
            $col = substr($alias, 0, strpos($alias, '()'));
            $ff = $this->leftField;
            $fv = $left->$ff->getValue();
            return $this->right[$fv][$col];
        }
    }
}