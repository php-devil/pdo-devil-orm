<?php
namespace PhpDevil\ORM\queries;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;

class QueryExecutable extends AbstractQuery
{
    public function execute()
    {
        $this->statement->execute();
        return $this;
    }

    protected function fromQueryBuilder($builder)
    {
        $query = $builder->parse($this->connection->getDialect());
        $this->sqlExpression = $query->getSql();
        $this->arguments = $query->getArguments();
        $this->statement = $this->connection->getHandler()->prepare($this->sqlExpression);
    }

    public function __construct($connection, $query)
    {
        $this->connection = $connection;
        if ($query instanceof QueryBuilderInterface) {
            $this->fromQueryBuilder($query);
        }
    }
}