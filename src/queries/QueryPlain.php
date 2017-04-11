<?php
namespace PhpDevil\ORM\queries;

class QueryPlain extends AbstractQuery
{
    public function fetch($mode = \PDO::FETCH_ASSOC)
    {
        return $this->statement->fetch($mode);
    }

    public function execute($arguments = null)
    {
        $this->statement->execute($arguments);
        return $this;
    }

    public function __construct($handler, $statement)
    {
        $this->sqlExpression = $statement;
        $this->statement = $handler->prepare($statement);
    }
}