<?php

namespace ipl\Orm;

class Relation
{
    /** @var string */
    protected $name;

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
    public function setTarget($target)
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
    protected function resolveForeignKey(Model $subject, $foreignKey)
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
    protected function resolveCandidateKey(Model $subject, $candidateKey)
    {
        $candidateKey = (array) $candidateKey;

        if (empty($candidateKey)) {
            $candidateKey = (array) $subject->getKey();
        }

        return $candidateKey;
    }

    /**
     * @param   Model   $source
     *
     * @return  array
     */
    public function resolve(Model $source)
    {
        $name = $this->getName();

        $candidateKey = $this->resolveCandidateKey($source, $this->getCandidateKey());

        if (empty($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' on table '%s' in model '%s'. No candidate key found.",
                $name,
                $source->getTableName(),
                static::class
            ));
        }

        $foreignKey = $this->resolveForeignKey($source, $this->getForeignKey());

        if (count($foreignKey) !== count($candidateKey)) {
            throw new \RuntimeException(sprintf(
                "Can't join relation '%s' on table '%s' in model '%s'."
                . " Foreign key count (%s) does not match candidate key count (%s).",
                $name,
                $source->getTableName(),
                static::class,
                implode(', ', $foreignKey),
                implode(', ', $candidateKey)
            ));
        }

        $tableAlias = $source->getTableAlias();

        $target = $this->getTarget();
        $targetTableAlias = $target->getTableAlias();

        $condition = [];

        foreach ($foreignKey as $k => $fk) {
            $condition[] = sprintf('%s.%s = %s.%s', $targetTableAlias, $fk, $tableAlias, $candidateKey[$k]);
        }

        return [
            [$targetTableAlias, $target->getTableName(), $condition]
        ];
    }
}
