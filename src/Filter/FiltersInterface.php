<?php

namespace ipl\Orm\Filter;

use Icinga\Data\Filter\Filter;

interface FiltersInterface
{
    /**
     * @return  Filter
     */
    public function getFilter();

    /**
     * @param   Filter  $filter
     *
     * @return  $this
     */
    public function setFilter(Filter $filter);

    /**
     * @param   Filter  $filter
     *
     * @return  $this
     */
    public function filter(Filter $filter);
}
