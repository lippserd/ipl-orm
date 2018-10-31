<?php

namespace ipl\Orm;

class Relation
{
    /** @var string */
    protected $name;

    /** @var Model */
    protected $subject;

    /** @var Model */
    protected $target;

    /** @var string|array */
    protected $foreignKey;

    /** @var string|array */
    protected $candidateKey;

    /** @var string */
    protected $columnPrefix;

    /**
     * @return  string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param   string  $name
     *
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Model|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param   Model   $subject
     *
     * @return  $this
     */
    public function setSubject(Model $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return  Model|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param   Model   $target
     *
     * @return  $this
     */
    public function setTarget(Model $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return  string|array
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param   string|array    $foreignKey
     *
     * @return  $this
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return  string|array
     */
    public function getCandidateKey()
    {
        return $this->candidateKey;
    }

    /**
     * @param   string|array    $candidateKey
     *
     * @return  $this
     */
    public function setCandidateKey($candidateKey)
    {
        $this->candidateKey = $candidateKey;

        return $this;
    }

    /**
     * @return  string
     */
    public function getColumnPrefix()
    {
        return $this->columnPrefix ?: $this->getName();
    }

    /**
     * @param   string  $columnPrefix
     *
     * @return  $this
     */
    public function setColumnPrefix($columnPrefix)
    {
        $this->columnPrefix = $columnPrefix;

        return $this;
    }

    /**
     * @param   string|array    $foreignKey
     * @param   Model           $subject
     *
     * @return  array
     */
    public function wantForeignKey($foreignKey, Model $subject)
    {
        $foreignKey = (array) $foreignKey;

        if (empty($foreignKey)) {
            $tableName = $subject->getTableName();

            $foreignKey = array_map(
                function ($key) use ($tableName) {
                    return "{$tableName}_{$key}";
                },
                (array) $subject->getKey()
            );
        }

        return $foreignKey;
    }

    /**
     * @param   string|array    $candidateKey
     * @param   Model           $subject
     *
     * @return  array
     */
    public function wantCandidateKey($candidateKey, Model $subject)
    {
        $candidateKey = (array) $candidateKey;

        if (empty($candidateKey)) {
            $candidateKey = (array) $subject->getKey();
        }

        return $candidateKey;
    }

    public function resolveConditions(Model $subject)
    {
        $candidateKey = $this->wantCandidateKey($this->getCandidateKey(), $subject);

        if (empty($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' on table '%s' in model '%s'. No candidate key found.",
                $this->getName(),
                $subject->getTableName(),
                static::class
            ));
        }

        $foreignKey = $this->wantForeignKey($this->getForeignKey(), $subject);

        if (count($foreignKey) !== count($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' on table '%s' in model '%s'."
                . " Foreign key count (%s) does not match candidate key count (%s).",
                $this->getName(),
                $subject->getTableName(),
                static::class,
                implode(', ', $foreignKey),
                implode(', ', $candidateKey)
            ));
        }

        return array_combine($foreignKey, $candidateKey);
    }

    /**
     * @return  array
     */
    public function resolve()
    {
        $subject = $this->getSubject();

        $conditions = $this->resolveConditions($subject);

        $tableName = $subject->getTableName();

        $targetTableAlias = $this->getName();

        $condition = [];

        foreach ($conditions as $fk => $ck) {
            $condition[] = sprintf('%s.%s = %s.%s', $targetTableAlias, $fk, $tableName, $ck);
        }

        return [
            [$targetTableAlias, $this->getTarget()->getTableName(), $condition]
        ];
    }
}
