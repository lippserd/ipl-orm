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

    public function testNoForeignKey()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getForeignKey());
    }

    public function testForeignKey()
    {
        $relation = (new Orm\Relation())
            ->setForeignKey('shop_id');

        $this->assertSame('shop_id', $relation->getForeignKey());
    }

    public function testNoCandidateKey()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getCandidateKey());
    }

    public function testCandidateKey()
    {
        $relation = (new Orm\Relation())
            ->setCandidateKey('product_id');

        $this->assertSame('product_id', $relation->getCandidateKey());
    }
}
