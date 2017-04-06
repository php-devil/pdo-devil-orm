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
            echo " == SHORT == ";
            $this->preloaded = (new RecordSet([
                'prototype' => $this->rightClassName,
                'query' => ($this->rightClassName)::findAll($this->queriedColumns)->where(QueryCriteria::createAND([[$this->rightFieldName, 'in', $this->loadedValues]]))
            ]))->all();
            $this->shortPreloadDone = true;
        }
    }

    protected function preloadLong()
    {
        if (!$this->longPreloadDone) {
            echo " == LONG == ";
            $this->preloaded = (new RecordSet([
                'prototype' => $this->rightClassName,
                'query' => ($this->rightClassName)::findAll(null)->where(QueryCriteria::createAND([[$this->rightFieldName, 'in', $this->loadedValues]]))
            ]))->all();
            $this->shortPreloadDone = true;
            $this->longPreloadDone = true;
        }
    }
}