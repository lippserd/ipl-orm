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
}
