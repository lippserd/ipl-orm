<?php

namespace ipl\Tests\Orm;

use ipl\Sql\Connection;

class TestConnection extends Connection
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        $this->adapter = new TestAdapter();
    }
}
