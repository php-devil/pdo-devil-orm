<?php
namespace PhpDevil\ORM\queries;

abstract class AbstractQuery
{
    protected $connection;

    protected $sqlExpression;

    protected $arguments;

    protected $statement = null;

    public function fetch($mode = \PDO::FETCH_ASSOC)
    {
        return $this->statement->fetch($mode);
    }
}