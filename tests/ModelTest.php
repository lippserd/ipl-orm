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

    public function testNoKey()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getKey());
    }

    public function testKey()
    {
        $product = (new Orm\Model())
            ->setKey('id');

        $this->assertSame('id', $product->getKey());
    }


    public function testKeyCompound()
    {
        $key = ['name', 'vendor'];

        $product = (new Orm\Model())
            ->setKey($key);

        $this->assertSame($key, $product->getKey());
    }

    public function testNoRelations()
    {
        $product = new Orm\Model();

        $this->assertNull($product->getRelations());
    }

    public function testHasRelation()
    {
        $product = new Orm\Model();

        $shop = new Orm\Model();

        $product->hasMany('shop', $shop);

        $this->assertTrue($product->hasRelation('shop'));
        $this->assertFalse($shop->hasRelation('product'));
    }

    public function testGetRelation()
    {
        $product = new Orm\Model();

        $shop = new Orm\Model();

        $product->hasMany('shop', $shop);

        $this->assertInstanceOf(Orm\Many::class, $product->getRelation('shop'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRelationThrowsExceptionIfRelationDoesNotExist()
    {
        $product = new Orm\Model();

        $product->getRelation('shop');
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
            ->setKey('id')
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
            ->setKey('id')
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

    public function testSelectManyRelationWithExplicitForeignAndCandidateKey()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop)
            ->setForeignKey('product_hash')
            ->setCandidateKey('hash');

        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop shop ON shop.product_hash = product.hash'
        );
    }

    public function testSelectManyRelationWithCompoundKey()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey(['name', 'vendor'])
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop);

        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop shop ON (shop.product_name = product.name) AND (shop.product_vendor = product.vendor)'
        );
    }

    public function testSelectManyRelationWithCompoundForeignAndCandidateKey()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop)
            ->setForeignKey(['product_name', 'product_vendor'])
            ->setCandidateKey(['name', 'vendor']);

        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop shop ON (shop.product_name = product.name) AND (shop.product_vendor = product.vendor)'
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSelectManyRelationWithoutKeyThrowsException()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product->hasMany('shop', $shop);

        $product->with('shop');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSelectManyRelationWithMismatchingKeyCountsThrowsException()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop)
            ->setForeignKey('id')
            ->setCandidateKey(['name', 'vendor']);

        $product->with('shop');
    }

    public function testSelectNestedRelation()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setKey('id')
            ->setColumns(['name', 'city']);

        $country = (new Orm\Model())
            ->setTableName('country')
            ->setColumns(['name']);

        $product->hasMany('shop', $shop);

        $shop->hasMany('country', $country);

        $product->with('shop.country');

        $this->assertSql(
            $product->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city, country.name'
            . ' FROM product product'
            . ' INNER JOIN shop shop ON shop.product_id = product.id'
            . ' INNER JOIN country country ON country.shop_id = shop.id'
        );
    }

    public function testDuplicateWithIsNoop()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setKey('id')
            ->setColumns(['name', 'city']);

        $country = (new Orm\Model())
            ->setTableName('country')
            ->setColumns(['name']);

        $product->hasMany('shop', $shop);

        $shop->hasMany('country', $country);

        $product
            ->with('shop.country')
            ->with('shop.country')
            ->with('shop');

        $this->assertSql(
            $product->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city, country.name'
            . ' FROM product product'
            . ' INNER JOIN shop shop ON shop.product_id = product.id'
            . ' INNER JOIN country country ON country.shop_id = shop.id'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDuplicateRelationThrowsException()
    {
        $product = new Orm\Model();
        $shop = new Orm\Model();

        $product->hasMany('shop', $shop);
        $product->hasMany('shop', $shop);
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
     * @expectedException \RuntimeException
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
