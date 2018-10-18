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
     * @return Model
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
     * @param   Model           $subject
     * @param   string|array    $foreignKey
     *
     * @return  array
     */
    public function resolveForeignKey(Model $subject, $foreignKey)
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
     * @param   Model           $subject
     * @param   string|array    $candidateKey
     *
     * @return  array
     */
    public function resolveCandidateKey(Model $subject, $candidateKey)
    {
        $candidateKey = (array) $candidateKey;

        if (empty($candidateKey)) {
            $candidateKey = (array) $subject->getKey();
        }

        return $candidateKey;
    }

    public function resolveConditions(Model $subject)
    {
        $name = $this->getName();

        $candidateKey = $this->resolveCandidateKey($subject, $this->getCandidateKey());

        if (empty($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' on table '%s' in model '%s'. No candidate key found.",
                $name,
                $subject->getTableName(),
                static::class
            ));
        }

        $foreignKey = $this->resolveForeignKey($subject, $this->getForeignKey());

        if (count($foreignKey) !== count($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' on table '%s' in model '%s'."
                . " Foreign key count (%s) does not match candidate key count (%s).",
                $name,
                $subject->getTableName(),
                static::class,
                implode(', ', $foreignKey),
                implode(', ', $candidateKey)
            ));
        }

        foreach ($foreignKey as $k => $fk) {
            yield $fk => $candidateKey[$k];
        }
    }

    /**
     * @return  array
     */
    public function resolve()
    {
        $subject = $this->getSubject();

        $conditions = $this->resolveConditions($subject);

        $tableAlias = $subject->getTableName();

        $targetTableAlias = $this->getName();

        $condition = [];

        foreach ($conditions as $fk => $ck) {
            $condition[] = sprintf('%s.%s = %s.%s', $targetTableAlias, $fk, $tableAlias, $ck);
        }

        return [
            [$targetTableAlias, $this->getTarget()->getTableName(), $condition]
        ];
    }
}
