<?php

namespace ipl\Orm;

use ipl\Sql;

class Model
{
    /** @var string */
    protected $tableName;

    /** @var string[] */
    protected $columns;

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
     * @return  Sql\Select
     */
    public function getSelect()
    {
        if ($this->select === null) {
            $this->select = (new Sql\Select())
                ->from($this->getTableName())
                ->columns($this->getColumns());
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
}
