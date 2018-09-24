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

    /**
     * @param   Model   $source
     *
     * @return  array
     */
    public function resolve(Model $source)
    {
        if ($this->via !== null) {
            $intermediate = (new Model())
                ->setTableName($this->via);

            $relation = (new static())
                ->setTarget($intermediate);

            $resolved = $relation->resolve($source);


            $monkey = clone $this;
            $monkey
                ->setVia(null)
                ->setCandidateKey($this->resolveForeignKey($this->getTarget()))
                ->setForeignKey($this->resolveCandidateKey($this->getTarget()));

            $resolved = array_merge($resolved, $monkey->resolve($intermediate));

            return $resolved;
        } else {
            return parent::resolve($source);
        }
    }
}
