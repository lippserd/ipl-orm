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

    public function testColumnsQualified()
    {
        $columns = ['name', 'rrp'];

        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns($columns);

        $this->assertSame(['product.name', 'product.rrp'], $product->getColumnsQualified($product->getTableName()));
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

    /**
     * @expectedException \RuntimeException
     */
    public function testUnknownWithThrowsException()
    {
        $product = new Orm\Model();

        $product->with('shop');
    }

    public function testSelectManyViaRelation()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setKey('id')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop)
            ->setVia('shop_product');

        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop_product shop_product ON shop_product.product_id = product.id'
            . ' INNER JOIN shop shop ON shop.id = shop_product.shop_id'
        );
    }

    public function testSelectManyViaRelationWithExplicitForeignAndCandidateKey()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setKey('id')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop)
            ->setForeignKey('product_hash')
            ->setCandidateKey('hash')
            ->setVia('shop_product')
            ->setTargetForeignKey('shop_hash')
            ->setTargetCandidateKey('hash');


        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop_product shop_product ON shop_product.product_hash = product.hash'
            . ' INNER JOIN shop shop ON shop.hash = shop_product.shop_hash'
        );
    }

    public function testSelectManyViaRelationWithPartlyExplicitForeignAndCandidateKey()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setKey('id')
            ->setColumns(['name', 'city']);

        $product
            ->hasMany('shop', $shop)
            ->setForeignKey('product_hash')
            ->setVia('shop_product')
            ->setTargetCandidateKey('hash');


        $this->assertSql(
            $product->with('shop')->getSelect(),
            'SELECT product.name, product.rrp, shop.name, shop.city'
            . ' FROM product product'
            . ' INNER JOIN shop_product shop_product ON shop_product.product_hash = product.id'
            . ' INNER JOIN shop shop ON shop.hash = shop_product.shop_id'
        );
    }

    public function testSelectManyRelationWithSameTarget()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name', 'rrp']);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name', 'city']);

        $product->hasMany('retail', $shop);
        $product->hasMany('digital', $shop);

        $this->assertSql(
            $product->with('retail')->with('digital')->getSelect(),
            'SELECT product.name, product.rrp, retail.name, retail.city, digital.name, digital.city'
            . ' FROM product product'
            . ' INNER JOIN shop retail ON retail.product_id = product.id'
            . ' INNER JOIN shop digital ON digital.product_id = product.id'
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
