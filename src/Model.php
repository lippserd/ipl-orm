<?php

namespace ipl\Orm;

use ipl\Sql;

class Model implements \IteratorAggregate
{
    /** @var Sql\Connection */
    protected $db;

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
     * @param   Sql\Connection  $db
     *
     * @return  static
     */
    public static function on(Sql\Connection $db)
    {
        return (new static())
            ->setDb($db);
    }

    /**
     * @return  Sql\Connection|null
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param   Sql\Connection  $db
     *
     * @return  $this
     */
    public function setDb(Sql\Connection $db)
    {
        $this->db = $db;

        return $this;
    }

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
     * @param   string  $prefix
     *
     * @return  array
     */
    public function getColumnsQualified($prefix)
    {
        return array_map(
            function ($column) use ($prefix) {
                return $prefix . '.' . $column;
            },
            $this->getColumns()
        );
    }

    /**
     * @param   string  $column
     *
     * @return  bool
     */
    public function hasColumn($column)
    {
        return in_array($column, $this->columns);
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
            $tableName = $this->getTableName();

            $from = [$tableName => $tableName];

            $this->select = (new Sql\Select())
                ->from($from)
                ->columns($this->getColumnsQualified($tableName));
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

    /**
     * @param   array|string    $columns
     *
     * @return  $this
     */
    public function select($columns)
    {
        $columns = is_string($columns) ? func_get_args() : $columns;

        $tableName = $this->getTableName();

        $processed = [];

        foreach ($columns as $path) {
            $dot = strrpos($path, '.');

            if ($dot === false) {
                $target = $this;

                $column = $path;
            } else {
                $relation = substr($path, 0, $dot);

                $column = substr($path, $dot + 1);

                if ($relation === $tableName) {
                    $target = $this;
                } else {
                    $this->with($relation);

                    $target = $this->with[$relation]->getTarget();
                }
            }

            if (! $target->hasColumn($column)) {
                throw new \RuntimeException(sprintf(
                    "Can't select column '%s' from table '%s' in model '%s'. Column not found.",
                    $column,
                    $target->getTableName(),
                    static::class
                ));
            }

            $processed[] = $path;
        }

        $this->getSelect()
            ->resetColumns()
            ->columns($processed);

        return $this;
    }

    public function with($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;

        foreach ($relations as $path) {
            $source = $this;

            $processed = [];

            foreach (explode('.', $path) as $name) {
                $processed[] = $name;

                $current = implode('.', $processed);

                if (isset($this->with[$current])) {
                    $source = $this->with[$current]->getTarget();
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

                $select->columns($target->getColumnsQualified($name));

                $this->with[$current] = $relation;

                $source = $target;
            }
        }

        return $this;
    }

    /**
     * @return  \PDOStatement
     */
    public function query()
    {
        return $this->getDb()->select($this->getSelect());
    }

    /**
     * @return  \PDOStatement
     */
    public function getIterator()
    {
        return $this->query();
    }

    private function assertRelationDoesNotYetExist($name)
    {
        if (isset($this->relations[$name])) {
            throw new \InvalidArgumentException("Relation '$name' already exists");
        }
    }
}
