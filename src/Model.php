<?php

namespace ipl\Orm;

use ipl\Sql;

class Model
{
    /** @var string */
    protected $tableName;

    /** @var string */
    protected $tableAlias;

    /** @var string[] */
    protected $columns;

    /** @var string */
    protected $key;

    /** @var Relation[] */
    protected $relations;

    /** @var Sql\Select */
    protected $select;

    /**
     * @return  string|null
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param   string  $tableName
     *
     * @return  $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return  string|null
     */
    public function getTableAlias()
    {
        return $this->tableAlias ?: $this->getTableName();
    }

    /**
     * @param   string  $tableAlias
     *
     * @return  $this
     */
    public function setTableAlias($tableAlias)
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return  string[]
     */
    public function getColumnsQualified()
    {
        $tableAlias = $this->getTableAlias();

        return array_map(
            function ($column) use ($tableAlias) {
                return $tableAlias . '.' . $column;
            },
            $this->getColumns()
        );
    }

    /**
     * @return  string[]|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param   string[]    $columns
     *
     * @return  $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return  string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param   string  $key
     *
     * @return  $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param   string  $name
     * @param   Model   $target
     *
     * @return  Many
     *
     * @throws  \InvalidArgumentException
     */
    public function hasMany($name, Model $target)
    {
        $this->assertRelationDoesNotYetExist($name);

        $relation = (new Many())
            ->setName($name)
            ->setTarget($target);

        $this->relations[$name] = $relation;

        return $relation;
    }

    /**
     * @return  Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return  Sql\Select
     */
    public function getSelect()
    {
        if ($this->select === null) {
            $from = [$this->getTableAlias() => $this->getTableName()];

            $this->select = (new Sql\Select())
                ->from($from)
                ->columns($this->getColumnsQualified());
        }

        return $this->select;
    }

    public function with($name)
    {
        if (! isset($this->relations[$name])) {
            throw new \InvalidArgumentException("Relation '$name' does not exist.");
        }

        $relation = $this->relations[$name];

        $target = $relation->getTarget();
        $targetTableAlias = $target->getTableAlias();

        $key = $this->getKey();

        $foreignKey = $targetTableAlias
            . '.'
            . ($relation->getForeignKey() ?: $this->getTableName() . '_' . $key);

        $candidateKey = $this->getTableAlias()
            . '.'
            . ($relation->getCandidateKey() ?: $key);

        $this
            ->getSelect()
            ->join([$targetTableAlias => $target->getTableName()], ["$foreignKey = $candidateKey"])
            ->columns($target->getColumnsQualified());

        return $this;
    }

    /**
     * @param   Sql\Select  $select
     *
     * @return  $this
     */
    public function setSelect(Sql\Select $select)
    {
        $this->select = $select;

        return $this;
    }

    private function assertRelationDoesNotYetExist($name)
    {
        if (isset($this->relations[$name])) {
            throw new \InvalidArgumentException("Relation '$name' already exists");
        }
    }
}
