<?php

namespace ipl\Orm;

use ipl\Sql;

class Model implements \ArrayAccess, \IteratorAggregate
{
    use Properties;

    /** @var bool */
    protected $new = true;

    /** @var Sql\Connection */
    protected $db;

    /** @var string */
    protected $tableName;

    /** @var string */
    protected $tableAlias;

    /** @var array */
    protected $columns = [];

    /** @var string|array */
    protected $key;

    /** @var array */
    protected $sortRules = [];

    /** @var Relation[] */
    protected $relations = [];

    /** @var Sql\Select */
    protected $select;

    /** @var array */
    protected $selectColumns = [];

    /** @var Relation[] */
    protected $with = [];

    /** @var array */
    protected $from = [];

    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);

        $this->init();
    }

    /**
     * @param   Sql\Connection  $db
     *
     * @return  static
     */
    public static function on(Sql\Connection $db)
    {
        $model = (new static())
            ->setDb($db);

        return $model;
    }

    protected function init()
    {

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
    public function setDb(Sql\Connection $db = null)
    {
        // Supports null for resetting the connection
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
     * @param   string  $prefix
     *
     * @return  array
     */
    public function getColumnsQualified($prefix)
    {
        $qualified = [];

        foreach ((array) $this->getColumns() as $alias => $column) {
            if (is_int($alias)) {
                $column = $prefix . '.' . $column;
            }

            $qualified[$alias] = $column;
        }

        return $qualified;
    }

    /**
     * @param   string  $column
     *
     * @return  bool
     */
    public function hasColumn($column)
    {
        return in_array($column, $this->columns) || $this->hasAlias($column);
    }

    /**
     * @param   string  $column
     *
     * @return  string
     */
    public function resolveColumn($column)
    {
        return $this->getTableAlias() . '.' . $column;
    }

    /**
     * @param   string  $alias
     *
     * @return  bool
     */
    public function hasAlias($alias)
    {
        return isset($this->columns[$alias]);
    }

    /**
     * @param   string  $alias
     *
     * @return  string
     */
    public function resolveAlias($alias)
    {
        return $this->columns[$alias];
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
            ->setSubject($this)
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
     * @param   array   $relations
     *
     * @return  $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
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
    public function getSelectBase()
    {
        if ($this->select === null) {
            $this->select = (new Sql\Select());
        }

        return $this->select;
    }

    /**
     * @return  Sql\Select
     */
    public function getSelect()
    {
        $tableName = $this->getTableName();
        $tableAlias = $this->getTableAlias();

        $select = clone $this->getSelectBase();

        if (! empty($this->from)) {
            $from = new Sql\Select();

            foreach ($this->from as list($model, $columns)) {
                /** @var Model $model */
                $from->unionAll($model->select($columns)->getSelect());
            }

            $select->from([$tableAlias => $from]);
        } else {
            $select->from([$tableAlias => $tableName]);
        }

        $columnMap = [];

        foreach ($this->getRelations() as $name => $relation) {
            $prefix = $relation->getPrefix();

            if ($prefix === null) {
                continue;
            }

            $columns = $relation->getTarget()->getColumns();

            foreach ($columns as $alias => $column) {
                if (is_int($alias)) {
                    $alias = $column;
                }

                $column = $relation->getTarget()->resolveColumn($column);

                $alias = "{$prefix}{$alias}";

                $columnMap[$alias] = [$name, $column];
            }
        }

        if (! empty($this->selectColumns)) {
            $autoColumns = false;

            $selectColumns = [];

            foreach ($this->selectColumns as $alias => $path) {
                if ($path === null || $path instanceof Sql\Expression) {
                    $selectColumns[$alias] = $path;

                    continue;
                }

                if (isset($columnMap[$path])) {
                    if (is_int($alias)) {
                        $alias = $path;
                    }

                    list($relation, $column) = $columnMap[$path];

                    $this->with($relation);

                    $selectColumns[$alias] = $column;

                    continue;
                }

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

                if (! is_int($column) && $target->hasAlias($column)) {
                    if (is_int($alias)) {
                        $alias = $column;
                    }
                    $column = $target->resolveAlias($column);
                } else {
                    $column = $target->resolveColumn($column);
                }

                $selectColumns[$alias] = $column;
            }
        } else {
            $autoColumns = true;

            $selectColumns = $this->getColumnsQualified($tableAlias);
        }

        $select->columns($selectColumns);

        $select->orderBy($this->sortRules);

        foreach ($this->with as $relation) {
            foreach ($relation->resolve() as list($targetTableAlias, $targetTableName, $condition)) {
                $select->join([$targetTableAlias => $targetTableName], $condition);
            }

            if ($autoColumns) {
                $select->columns($relation->getTarget()->getColumnsQualified($relation->getName()));
            }
        }

        return $select;
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
        $this->selectColumns = array_merge(
            $this->selectColumns,
            is_string($columns) ? func_get_args() : $columns
        );

        return $this;
    }

    /**
     * @param   string|array    $relations
     *
     * @return  $this
     */
    public function with($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;

        foreach ($relations as $path) {
            $subject = $this;

            $processed = [];

            foreach (explode('.', $path) as $name) {
                $processed[] = $name;

                $current = implode('.', $processed);

                if (isset($this->with[$current])) {
                    $subject = $this->with[$current]->getTarget();
                    continue;
                }

                if (! $subject->hasRelation($name)) {
                    throw new \InvalidArgumentException(sprintf(
                        "Can't join relation '%s' on table '%s' in model '%s'. Relation not found.",
                        $name,
                        $subject->getTableName(),
                        static::class
                    ));
                }

                $relation = $subject->getRelation($name);

                $this->with[$current] = $relation;

                $subject = $relation->getTarget();
            }
        }

        return $this;
    }

    /**
     * @param   Model   $model
     * @param   array   $columns
     *
     * @return  $this
     */
    public function from(Model $model, array $columns = null)
    {
        $this->from[] = [$model, $columns];

        return $this;
    }

    /**
     * @return  \Generator
     */
    public function query()
    {
        foreach ($this->getDb()->select($this->getSelect()) as $row) {
            $model = (new static($row))
                ->setDb($this->getDb());

            yield $model;
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

        $target = clone $relation->getTarget();

        $conditions = $relation->resolveConditions($this);

        $conditionsTarget = $target->getTableAlias();

        $select = $target->getSelectBase();

        if ($relation instanceof Many) {
            $viaTable = $relation->getVia();

            if ($viaTable !== null) {
                $intermediate = (new self())
                    ->setTableName($viaTable);

                $viaRelation = clone $relation;
                $viaRelation
                    ->setVia(null)
                    ->setName($viaTable)
                    ->setSubject($target)
                    ->setTarget($intermediate);

//                $target->with[$viaTable] = $viaRelation; // Lazy-loading alternative

                foreach ($viaRelation->resolve() as list($targetTableAlias, $targetTableName, $condition)) {
                    $select->join([$targetTableAlias => $targetTableName], $condition);
                }

                $conditionsTarget = $viaTable;
            }
        }

        foreach ($conditions as $fk => $ck) {
            $select->where(["$conditionsTarget.$fk = ?" => $this->getProperty($ck)]);
        }

        $target->setDb($this->getDb());

        return $target;
    }

    private function assertRelationDoesNotYetExist($name)
    {
        if (isset($this->relations[$name])) {
            throw new \InvalidArgumentException("Relation '$name' already exists");
        }
    }
}
