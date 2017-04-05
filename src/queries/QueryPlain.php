<?php
namespace PhpDevil\ORM\queries;

class QueryPlain extends AbstractQuery
{

    public function __construct($handler, $statement)
    {
        $this->sqlExpression = $statement;
        $this->statement = $handler->prepare($statement);
    }
}