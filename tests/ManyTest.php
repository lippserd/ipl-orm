<?php

namespace ipl\Tests\Orm;

use ipl\Orm;

class ManyTest extends \PHPUnit_Framework_TestCase
{
    public function testNoVia()
    {
        $relation = new Orm\Many();

        $this->assertNull($relation->getVia());
    }

    public function testVia()
    {
        $via = 'shop_product';

        $relation = (new Orm\Many())
            ->setVia($via);

        $this->assertSame($via, $relation->getVia());
    }
}
