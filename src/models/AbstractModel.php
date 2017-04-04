<?php
namespace PhpDevil\ORM\models;

abstract class AbstractModel
{
    abstract public function config();

    protected function __construct()
    {
    }

    public static function model()
    {

    }
}