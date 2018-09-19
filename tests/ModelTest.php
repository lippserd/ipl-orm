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

    public function testNoTableAlias()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getTableAlias());
    }

    public function testTableAlias()
    {
        $product = (new Orm\Model())
            ->setTableAlias('p');

        $this->assertSame('p', $product->getTableAlias());
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

    public function testColumnsQualified()
    {
        $columns = ['name', 'rrp'];

        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns($columns);

        $this->assertSame(['product.name', 'product.rrp'], $product->getColumnsQualified());
    }

    public function testColumnsQualifiedWithAlias()
    {
        $columns = ['name', 'rrp'];

        $product = (new Orm\Model())
            ->setTableName('product')
            ->setTableAlias('p')
            ->setColumns($columns);

        $this->assertSame(['p.name', 'p.rrp'], $product->getColumnsQualified());
    }

    public function testNoKeyName()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getKeyName());
    }

    public function testKeyName()
    {
        $product = (new Orm\Model())
            ->setKeyName('id');

        $this->assertSame('id', $product->getKeyName());
    }

    public function testNoRelations()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getRelations());
    }

    public function testManyRelation()
    {
        $product = new Orm\Model();
        $shop = new Orm\Model();

        $product->hasMany('shop', $shop);

        $relation = $product->getRelations()['shop'];

        $this->assertSame('shop', $relation->getName());
        $this->assertSame($shop, $relation->getTarget());
    }

    public function testSelectManyRelation()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKeyName('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product->hasMany('shop', $shop);

        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop shop ON shop.product_id = product.id'
        );
    }

    public function testSelectManyRelationWithAlias()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setTableAlias('p')
            ->setKeyName('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setTableAlias('s')
            ->setColumns(['name', 'city']);

        $product->hasMany('shop', $shop);

        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT p.name, p.rrp, s.name, s.city'
            . ' FROM product p'
            . ' INNER JOIN shop s ON s.product_id = p.id'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDuplicateRelationThrowsException()
    {
        $product = new Orm\Model();
        $shop = new Orm\Model();

        $product
            ->hasMany('shop', $shop)
            ->hasMany('shop', $shop);
    }

    public function testSelect()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name', 'rrp']);

        $this->assertSql(
            $product->getSelect(),
            'SELECT product.name, product.rrp FROM product product'
        );
    }

    public function testSelectWithAlias()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setTableAlias('p')
            ->setColumns(['name', 'rrp']);

        $this->assertSql(
            $product->getSelect(),
            'SELECT p.name, p.rrp FROM product p'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownWithThrowsException()
    {
        $product = new Orm\Model();

        $product->with('shop');
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
