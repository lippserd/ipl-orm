<?php

namespace ipl\Tests\Orm;

use ipl\Orm;

class ClassUsingProperties implements \ArrayAccess
{
    use Orm\Properties;

    /**
     * @return  mixed
     */
    public function getFoobarProperty()
    {
        return 'foobar';
    }

    /**
     * @param   mixed   $value
     */
    public function setSpecialProperty($value)
    {
        $this->properties['special'] = strtoupper($value);
    }
}
