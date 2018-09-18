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
    protected $keyName;

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
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param   string  $keyName
     *
     * @return  $this
     */
    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;

        return $this;
    }

    /**
     * @param   string  $name
     * @param   Model   $target
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    public function hasMany($name, Model $target)
    {
        $this->assertRelationDoesNotYetExist($name);

        $this->relations[$name] = (new Many())
            ->setName($name)
            ->setTarget($target);

        return $this;
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
            $tableAlias = $this->getTableAlias();

            $from = [$tableAlias => $this->getTableName()];

            $columns = array_map(
                function ($column) use ($tableAlias) {
                    return $tableAlias . '.' . $column;
                },
                $this->getColumns()
            );

            $this->select = (new Sql\Select())
                ->from($from)
                ->columns($columns);
        }

        return $this->select;
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
