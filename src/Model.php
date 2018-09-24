<?php

namespace ipl\Orm;

use ipl\Sql;

class Model
{
    /** @var string */
    protected $tableName;

    /** @var array */
    protected $columns;

    /** @var string|array */
    protected $key;

    /** @var Relation[] */
    protected $relations;

    /** @var Sql\Select */
    protected $select;

    /** @var Relation[] */
    protected $with;

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
     * @return  array
     */
    public function getColumnsQualified()
    {
        $tableAlias = $this->getTableName();

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
     * @param   string  $name
     *
     * @return  bool
     */
    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    /**
     * @param   string  $name
     *
     * @return  Relation
     *
     * @throws  \InvalidArgumentException
     */
    public function getRelation($name)
    {
        if (! $this->hasRelation($name)) {
            throw new \InvalidArgumentException(sprintf(
                "Can't get relation '%s' for table '%s' in model '%s'. Relation not found.",
                $name,
                $this->getTableName(),
                static::class
            ));
        }

        return $this->relations[$name];
    }

    /**
     * @return  Sql\Select
     */
    public function getSelect()
    {
        if ($this->select === null) {
            $from = [$this->getTableName() => $this->getTableName()];

            $this->select = (new Sql\Select())
                ->from($from)
                ->columns($this->getColumnsQualified());
        }

        return $this->select;
    }

    public function with($relations)
    {
        $processed = [];

        $source = $this;

        foreach (explode('.', $relations) as $name) {
            $processed[] = $name;
            $path = implode('.', $processed);

            if (isset($this->with[$path])) {
                $source = $this->with[$path]->getTarget();
                continue;
            }

            if (! $source->hasRelation($name)) {
                throw new \RuntimeException(sprintf(
                    "Can't join relation '%s' on table '%s' in model '%s'. Relation not found.",
                    $name,
                    $source->getTableName(),
                    static::class
                ));
            }

            $select = $this->getSelect();

            $relation = $source->getRelation($name);

            foreach ($relation->resolve($source) as list($targetTableAlias, $targetTableName, $condition)) {
                $select->join([$targetTableAlias => $targetTableName], $condition);
            }

            $target = $relation->getTarget();

            $select->columns($target->getColumnsQualified());

            $this->with[$path] = $relation;

            $source = $target;
        }

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
