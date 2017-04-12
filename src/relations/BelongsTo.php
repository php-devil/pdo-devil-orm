<?php
namespace PhpDevil\ORM\relations;

use PhpDevil\ORM\attributes\PermanentEmptyAttribute;
use PhpDevil\ORM\providers\RecordSet;
use PhpDevil\ORM\QueryBuilder\components\QueryCriteria;

class BelongsTo extends AbstractRelation implements RelationObserver
{
    protected $shortPreloadDone = false;

    protected $longPreloadDone = false;

    protected $preloaded = null;

    public function getHtmlType()
    {
        return 'one_from_list';
    }

    public function getVariantsFor($model, $template)
    {
        $valid = [['key' => 0,  'value' => '/']];

        $where = null;
        if ($this->leftClassName === $this->rightClassName) {

        }

        $variants = (new RecordSet([
            'prototype' => $this->rightClassName,
            'query' => ($this->rightClassName)::findAll()
        ]))->all();

        foreach($variants as $row) {
            $valid[] = ['key' => $row->getAttribute($this->rightFieldName)->getValue(), 'value' => $row->fromTemplate($template), 'level'=> $row->getLevel()];
        }
        return $valid;
    }

    protected function getFromProvider($model)
    {
        if (isset($this->preloaded[$model->getAttribute($this->leftFieldName)->getValue()])) {
            return $this->preloaded[$model->getAttribute($this->leftFieldName)->getValue()];
        } else {
            return new PermanentEmptyAttribute;
        }
    }

    protected function preloadShort()
    {
        if (!$this->shortPreloadDone) {
            $where = null;
            if (!empty($this->loadedValues)) $where = QueryCriteria::createAND([[$this->rightFieldName, 'in', $this->loadedValues]]);
            $this->preloaded = (new RecordSet([
                'prototype' => $this->rightClassName,
                'query' => ($this->rightClassName)::findAll($this->queriedColumns)->where($where)
            ]))->all();
            $this->shortPreloadDone = true;
        }
    }

    protected function preloadLong()
    {
        if (!$this->longPreloadDone) {
            $where = null;
            if (!empty($this->loadedValues)) $where = QueryCriteria::createAND([[$this->rightFieldName, 'in', $this->loadedValues]]);
            $this->preloaded = (new RecordSet([
                'prototype' => $this->rightClassName,
                'query' => ($this->rightClassName)::findAll(null)->where($where)
            ]))->all();
            $this->shortPreloadDone = true;
            $this->longPreloadDone = true;
        }
    }
}