<?php

namespace ipl\Orm;

class Model
{
    /** @var string */
    protected $tableName;

    /** @var string[] */
    protected $columns;

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


}
