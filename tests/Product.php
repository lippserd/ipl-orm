<?php

namespace ipl\Tests\Orm;

use ipl\Orm;

class Product extends Orm\Model
{
    protected $tableName = 'product';

    protected $key = 'id';

    protected $columns = ['name', 'rrp'];
}
