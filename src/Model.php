<?php

namespace ipl\Orm;

use ipl\Sql;

class Model implements \IteratorAggregate
{
    use Properties;

    /** @var bool */
    protected $new = true;

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

    /** @var array */
    protected $sortRules = [];

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
     * @return  bool
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param   bool    $new
     *
     * @return  $this
     */
    public function setNew($new)
    {
        $this->new = (bool) $new;

        return $this;
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
            (array) $this->getColumns()
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
     * @return  array
     */
    public function getSortRules()
    {
        return $this->sortRules;
    }

    /**
     * @param   array   $sortRules
     *
     * @return  $this
     */
    public function setSortRules(array $sortRules)
    {
        $this->sortRules = $sortRules;

        return $this;
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
                ->columns($this->getColumnsQualified($tableName))
                ->orderBy($this->sortRules);
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
     * @return  \Generator
     */
    public function query()
    {
        foreach ($this->getDb()->select($this->getSelect()) as $row) {
            yield (new static())
                ->setProperties($row)
                ->setNew(false);
        }
    }

    /**
     * @return  \Generator
     */
    public function getIterator()
    {
        return $this->query();
    }

    public function __call($name, $arguments)
    {
        if (! $this->hasRelation($name)) {
            throw new \InvalidArgumentException("Relation '$name' does not exist");
        }

        if ($this->isNew()) {
            throw new \RuntimeException("Can\'t fetch relational data for new models");
        }

        $relation = $this->relations[$name];

        $target = $relation->getTarget();

        $conditions = $relation->resolveConditions($this);

        $conditionsTarget = $target->getTableName();

        $select = $target->getSelect();

        if ($relation instanceof Many) {
            $viaTable = $relation->getVia();

            if ($viaTable !== null) {
                $intermediate = (new self())
                    ->setTableName($viaTable);

                $viaRelation = clone $relation;
                $viaRelation
                    ->setVia(null)
                    ->setName($viaTable)
                    ->setTarget($intermediate);

                foreach ($viaRelation->resolve($target) as list($targetTableAlias, $targetTableName, $condition)) {
                    $select->join([$targetTableAlias => $targetTableName], $condition);
                }

                $conditionsTarget = $viaTable;
            }
        }

        foreach ($conditions as $fk => $ck) {
            $select->where(["$conditionsTarget.$fk = ?" => $this->getProperty($ck)]);
        }

        return $target;
    }

    private function assertRelationDoesNotYetExist($name)
    {
        if (isset($this->relations[$name])) {
            throw new \InvalidArgumentException("Relation '$name' already exists");
        }
    }
}
