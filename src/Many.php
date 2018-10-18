<?php

namespace ipl\Orm;

class Many extends Relation
{
    /** @var string */
    protected $via;

    /** @var string|array */
    protected $targetForeignKey;

    /** @var string|array */
    protected $targetCandidateKey;

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
     * @return  string|array
     */
    public function getTargetForeignKey()
    {
        return $this->targetForeignKey;
    }

    /**
     * @param   string|array    $targetForeignKey
     *
     * @return  $this
     */
    public function setTargetForeignKey($targetForeignKey)
    {
        $this->targetForeignKey = $targetForeignKey;

        return $this;
    }

    /**
     * @return  string|array
     */
    public function getTargetCandidateKey()
    {
        return $this->targetCandidateKey;
    }

    /**
     * @param   string|array    $targetCandidateKey
     *
     * @return  $this
     */
    public function setTargetCandidateKey($targetCandidateKey)
    {
        $this->targetCandidateKey = $targetCandidateKey;

        return $this;
    }

    /**
     * @param   Model   $subject
     *
     * @return  array
     */
    public function resolve(Model $subject)
    {
        $viaTable = $this->getVia();

        if ($viaTable !== null) {
            $intermediate = (new Model())
                ->setTableName($viaTable);

            $viaRelation = clone $this;
            $viaRelation
                ->setVia(null)
                ->setName($viaTable)
                ->setTarget($intermediate);

            $resolved = $viaRelation->resolve($subject);

            $monkey = clone $this;
            $monkey
                ->setVia(null)
                ->setCandidateKey($this->resolveForeignKey(
                    $this->getTarget(),
                    $this->getTargetForeignKey()
                ))
                ->setForeignKey($this->resolveCandidateKey(
                    $this->getTarget(),
                    $this->getTargetCandidateKey()
                ));

            $resolved = array_merge($resolved, $monkey->resolve($intermediate));

            return $resolved;
        } else {
            return parent::resolve($subject);
        }
    }
}
