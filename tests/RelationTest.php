<?php

namespace ipl\Tests\Orm;

use ipl\Orm;

class RelationTest extends \PHPUnit_Framework_TestCase
{
    public function testNoTarget()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getTarget());
    }

    public function testTarget()
    {
        $shop = new Orm\Model();
        $relation = (new Orm\Relation())
            ->setTarget($shop);

        $this->assertSame($shop, $relation->getTarget());
    }

    public function testNoName()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getName());
    }

    public function testName()
    {
        $relation = (new Orm\Relation())
            ->setName('shop');

        $this->assertSame('shop', $relation->getName());
    }
}
