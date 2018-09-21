<?php

namespace ipl\Orm;

class Many extends Relation
{
    /** @var string */
    protected $via;

    /**
     * @return  string
     */
    public function getVia()
    {
        return $this->via;
    }

    /**
     * @param   string  $via
     *
     * @return  $this
     */
    public function setVia($via)
    {
        $this->via = $via;

        return $this;
    }
}
