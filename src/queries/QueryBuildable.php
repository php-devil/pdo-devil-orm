<?php
namespace PhpDevil\ORM\queries;
use PhpDevil\ORM\QueryBuilder\queries\DeleteQueryBuilder;
use PhpDevil\ORM\QueryBuilder\queries\QueryExecutorInterface;
use PhpDevil\ORM\QueryBuilder\queries\SelectQueryBuilder;
use PhpDevil\ORM\QueryBuilder\queries\UpdateQueryBuilder;
use PhpDevil\ORM\QueryBuilder\queries\InsertQueryBuilder;
use PhpDevil\ORM\QueryBuilder\QueryBuilderInterface;

class QueryBuildable implements QueryExecutorInterface
{
    protected $connection;

    protected $tableName;

    protected $builder = null;

    public function select($columns = null)
    {
        return (new SelectQueryBuilder($this))->select($columns)->from($this->tableName);
    }

    public function update($what, $where)
    {
        return (new UpdateQueryBuilder($this))->update($this->tableName)->set($what)->where($where);
    }

    public function insert($what)
    {
        return (new InsertQueryBuilder($this))->into($this->tableName)->set($what);
    }

    public function delete($where)
    {
        return (new DeleteQueryBuilder($this))->from($this->tableName)->where($where);
    }

    public function execute(QueryBuilderInterface $builder, $arguments = null)
    {
        $resultQuery = new QueryExecutable($this->connection, $builder);
        $resultQuery->execute($arguments);
        return $resultQuery;
    }

    public function __construct($connection, $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }
}