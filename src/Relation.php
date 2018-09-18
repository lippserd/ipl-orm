<?php

namespace ipl\Orm;

class Relation
{
    /** @var string */
    protected $name;

    /** @var Model */
    protected $target;

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
}
