<?php

namespace ipl\Orm\Filter;

use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterAnd;

trait Filters
{
    /** @var FilterAnd */
    private $filter;

    public function getFilter()
    {
        $this->assertFilter();

        return $this->filter;
    }

    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function filter(Filter $filter)
    {
        $this->assertFilter();

        $this->filter->addFilter($filter);

        return $this;
    }

    private function assertFilter()
    {
        if ($this->filter === null) {
            $this->filter = Filter::matchAll();
        }
    }
}
