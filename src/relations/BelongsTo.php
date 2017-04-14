<?php
namespace PhpDevil\ORM\relations;

use PhpDevil\ORM\attributes\PermanentEmptyAttribute;
use PhpDevil\ORM\models\ActiveRecordInterface;
use PhpDevil\ORM\providers\RecordSet;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;

class BelongsTo extends AbstractRelation implements RelationObserver
{
    protected $right = null;

    public function createLink(ActiveRecordInterface $row)
    {
        $rf = $this->rightField;
        $rv = $row->$rf->getValue();
        $this->right[$rv] = &$row;
    }

    protected function createRight()
    {
        if (null === $this->right) {
            $where = null;
            if (!empty($this->filterValues)) {
                $where = QueryCriteria::createAND([[$this->rightField, 'in', $this->filterValues]]);
            }
            ($this->rightClassName)::findAll($this->queryColumns, $where)->all([$this, 'createLink']);
        }
    }

    public function preloadSingle($row)
    {
        $lf = $this->leftField;
        $lv = $row->$lf->getValue();
        return ($this->rightClassName)::findOne(QueryCriteria::createAND([[$this->rightField, '=', $lv]]));
    }

    public function getValueFor(ActiveRecordInterface $left, $alias)
    {

        $this->createRight();
        $lf = $this->leftField;
        $lv =  $left->$lf->getValue();
        if (isset($this->right[$lv])) {
            $row = $this->right[$lv];
            return $row->$alias;
        }
    }
}