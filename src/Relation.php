<?php

namespace ipl\Orm;

class Relation
{
    /** @var string */
    protected $name;

    /** @var Model */
    protected $target;

    /** @var string */
    protected $foreignKey;

    /** @var string */
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
     * @return  string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param   string  $foreignKey
     *
     * @return  $this
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return  string
     */
    public function getCandidateKey()
    {
        return $this->candidateKey;
    }

    /**
     * @param   string  $candidateKey
     *
     * @return  $this
     */
    public function setCandidateKey($candidateKey)
    {
        $this->candidateKey = $candidateKey;

        return $this;
    }
}
