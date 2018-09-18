<?php

namespace ipl\Tests\Orm;

use ipl\Orm;
use ipl\Sql;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var Sql\QueryBuilder */
    protected $queryBuilder;

    public function setUp()
    {
        $this->queryBuilder = new Sql\QueryBuilder(new TestAdapter());
    }

    public function testNoTableName()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getTableName());
    }

    public function testTableName()
    {
        $product = (new Orm\Model())
            ->setTableName('product');

        $this->assertSame('product', $product->getTableName());
    }

    public function testNoColumns()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getColumns());
    }

    public function testColumns()
    {
        $columns = ['name', 'rrp'];

        $product = (new Orm\Model())
            ->setColumns($columns);

        $this->assertSame($columns, $product->getColumns());
    }

    public function testSelect()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name', 'rrp']);

        $this->assertSql(
            $product->getSelect(),
            'SELECT name, rrp FROM product'
        );
    }

    public function assertSql($query, $sql, $values = null)
    {
        list($stmt, $bind) = $this->queryBuilder->assemble($query);

        $this->assertSame($sql, $stmt);

        if ($values !== null) {
            $this->assertSame($values, $bind);
        }
    }
}
