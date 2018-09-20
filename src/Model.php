<?php

namespace ipl\Orm;

use ipl\Sql;

class Model
{
    /** @var string */
    protected $tableName;

    /** @var string */
    protected $tableAlias;

    /** @var array */
    protected $columns;

    /** @var string|array */
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
     * @return  array
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
     * @return  array|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param   array   $columns
     *
     * @return  $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return  string|array|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param   string|array    $key
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

        $tableName = $this->getTableName();
        $key = (array) $this->getKey();

        $relation = $this->relations[$name];

        $candidateKey = (array) $relation->getCandidateKey();

        if (empty($candidateKey)) {
            $candidateKey = $key;
        }

        if (empty($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' in model '%s'. No candidate key found.",
                $name,
                static::class
            ));
        }

        $foreignKey = (array) $relation->getForeignKey();

        if (empty($foreignKey)) {
            $foreignKey = array_map(
                function ($key) use ($tableName) {
                    return "{$tableName}_{$key}";
                },
                $key
            );
        }

        if (count($foreignKey) !== count($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' in model '%s'."
                . " Foreign key count (%s) does not match candidate key count (%s).",
                $name,
                static::class,
                implode(', ', $foreignKey),
                implode(', ', $candidateKey)
            ));
        }

        $tableAlias = $this->getTableAlias();

        $target = $relation->getTarget();
        $targetTableAlias = $target->getTableAlias();

        $condition = [];

        foreach ($foreignKey as $i => $name) {
            $condition[] = sprintf('%s.%s = %s.%s', $targetTableAlias, $name, $tableAlias, $candidateKey[$i]);
        }

        $this
            ->getSelect()
            ->join([$targetTableAlias => $target->getTableName()], $condition)
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
