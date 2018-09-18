<?php

namespace ipl\Orm;

class Relation
{
    /** @var Model */
    protected $target;

    /**
     * @return  Model
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
