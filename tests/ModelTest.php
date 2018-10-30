<?php

namespace ipl\Tests\Orm;

use ipl\Orm;
use ipl\Sql;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    use Fixtures;
    use TestsSql;

    public function setUp()
    {
        $this->initTestsSql();
        $this->initFixturesDb();
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

        $this->assertSame(['product.name', 'product.rrp'], $product->getColumnsQualified($product->getTableName()));
    }

    public function testColumnsQualifiedWithAlias()
    {
        $columns = ['name', 'rrp'];

        $product = (new Orm\Model())
            ->setTableName('product')
            ->setTableAlias('p')
            ->setColumns($columns);

        $this->assertSame(['p.name', 'p.rrp'], $product->getColumnsQualified($product->getTableAlias()));
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
            'SELECT p.name, p.rrp, shop.name, shop.city'
            . ' FROM product p'
            . ' INNER JOIN shop shop ON shop.product_id = p.id'
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

        $product
            ->hasMany('shop', $shop);

        $product
            ->with('shop')
            ->getSelect();
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

        $product
            ->with('shop')
            ->getSelect();
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

    public function testSelectNestedRelationViaColumns()
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

        $this->assertSql(
            $product->select('shop.country.name', 'product.name')->getSelect(),
            'SELECT country.name, product.name'
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
     * @expectedException \InvalidArgumentException
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

    public function testSelectManyViaRelationWithExplicitColumnsToSelect()
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
            $product->select('product.name', 'shop.name')->getSelect(),
            'SELECT product.name, shop.name'
            . ' FROM product product'
            . ' INNER JOIN shop_product shop_product ON shop_product.product_id = product.id'
            . ' INNER JOIN shop shop ON shop.id = shop_product.shop_id'
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSelectManyViaRelationWithExplicitColumnsToSelectThrowsExceptionIfColumnDoesNotExist()
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

        $product
            ->select('product.name', 'shop.country')
            ->getSelect();
    }

    public function testWithVariadic()
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
            $product->with('retail', 'digital')->getSelect(),
            'SELECT product.name, product.rrp, retail.name, retail.city, digital.name, digital.city'
            . ' FROM product product'
            . ' INNER JOIN shop retail ON retail.product_id = product.id'
            . ' INNER JOIN shop digital ON digital.product_id = product.id'
        );
    }

    public function testWithArray()
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
            $product->with(['retail', 'digital'])->getSelect(),
            'SELECT product.name, product.rrp, retail.name, retail.city, digital.name, digital.city'
            . ' FROM product product'
            . ' INNER JOIN shop retail ON retail.product_id = product.id'
            . ' INNER JOIN shop digital ON digital.product_id = product.id'
        );
    }

    public function testNoDb()
    {
        $model = new Orm\Model();

        $this->assertNull($model->getDb());
    }

    public function testDb()
    {
        $db = new TestConnection();

        $model = (new Orm\Model())
            ->setDb($db);

        $this->assertSame($db, $model->getDb());
    }

    public function testOn()
    {
        $db = new TestConnection();

        $model = Orm\Model::on($db);

        $this->assertSame($db, $model->getDb());
    }

    public function testQuery()
    {
        $model = Orm\Model::on($this->getFixturesDb())
            ->setTableName('product')
            ->setColumns(['name']);

        $this->assertInstanceOf('\Generator', $model->query());
    }

    public function testGetIterator()
    {
        $model = Orm\Model::on($this->getFixturesDb())
            ->setTableName('product')
            ->setColumns(['name']);

        $this->assertInstanceOf('\Generator', $model->getIterator());
    }

    public function testModelCreation()
    {
        $products = Product::on($this->getFixturesDb())->query();

        $this->assertInstanceOf(Product::class, $products->current());
    }

    public function testSortRules()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name'])
            ->setSortRules(['product.name']);

        $this->assertSql(
            $product->select('product.name')->getSelect(),
            'SELECT product.name'
            . ' FROM product product'
            . ' ORDER BY product.name'
        );
    }

    /** @expectedException \InvalidArgumentException */
    public function testAccessRelationalDataThroughRelationNameThrowsExceptionIfRelationDoesNotExist()
    {
        (new Orm\Model())->shop();
    }

    /** @expectedException \RuntimeException */
    public function testAccessRelationalDataThroughRelationNameThrowsExceptionForNewModels()
    {
        $product = (new Orm\Model());

        $shop = (new Orm\Model());

        $product->hasMany('shop', $shop);

        $product->shop();
    }

    public function testAccessRelationalDataThroughRelationName()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name'])
            ->setProperties(['id' => 1])
            ->setNew(false);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setColumns(['name']);

        $product->hasMany('shop', $shop);

        $this->assertSql(
            $product->shop()->getSelect(),
            'SELECT shop.name'
            . ' FROM shop shop'
            . ' WHERE shop.product_id = ?',
            [1]
        );
    }

    public function testAccessRelationalDataWithViaThroughRelationName()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id')
            ->setColumns(['name'])
            ->setProperties(['id' => 1])
            ->setNew(false);

        $shop = (new Orm\Model())
            ->setTableName('shop')
            ->setKey('id')
            ->setColumns(['name']);

        $product
            ->hasMany('shop', $shop)
            ->setVia('shop_product');

        $this->assertSql(
            $product->shop()->getSelect(),
            'SELECT shop.name'
            . ' FROM shop shop'
            . ' INNER JOIN shop_product shop_product ON shop_product.shop_id = shop.id'
            . ' WHERE shop_product.product_id = ?',
            [1]
        );
    }

    public function testCustomSelectColumns()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name' => 'LOWER(product.name)']);

        $this->assertSql(
            $product->getSelect(),
            'SELECT LOWER(product.name) AS name FROM product product'
        );
    }

    public function testAliasedCustomSelectColumns()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setColumns(['name' => 'LOWER(product.name)']);

        $this->assertSql(
            $product->select(['alias' => 'name'])->getSelect(),
            'SELECT LOWER(product.name) AS alias FROM product product'
        );
    }

    public function testOtherModelAsBase()
    {
        $summary = new Orm\Model();

        $summary->setTableName('summary');

        $summary->setColumns(['up' => 'SUM(CASE WHEN summary.state = 1 THEN 1 ELSE 0 END)']);

        $host = new Orm\Model();
        $host->setTableName('host');
        $host->setColumns(['state']);

        $summary->from($host, ['state']);

        $this->assertSql(
            $summary->select('up')->getSelect(),
            'SELECT SUM(CASE WHEN summary.state = 1 THEN 1 ELSE 0 END) AS up'
            . ' FROM ((SELECT host.state FROM host host)) summary'
        );
    }

    public function testOtherModelsAsBase()
    {
        $host = (new Orm\Model())
            ->setTableName('host')
            ->setColumns(['host_state' => 'state']);

        $service = (new Orm\Model())
            ->setTableName('service')
            ->setColumns(['service_state' => 'state']);

        $summary = (new Orm\Model())
            ->setTableName('summary')
            ->setColumns([
                'hosts_up' => 'SUM(CASE WHEN summary.host_state = 1 THEN 1 ELSE 0 END)',
                'services_ok' => 'SUM(CASE WHEN summary.service_state = 1 THEN 1 ELSE 0 END)'
            ]);

        $summary->from($host, ['host_state', 'service_state' => new Sql\Expression('NULL')]);
        $summary->from($service, ['host_state' => new Sql\Expression('NULL'), 'service_state']);

        $this->assertSql(
            $summary->select(['hosts_up', 'services_ok'])->getSelect(),
            'SELECT SUM(CASE WHEN summary.host_state = 1 THEN 1 ELSE 0 END) AS hosts_up,'
            . ' SUM(CASE WHEN summary.service_state = 1 THEN 1 ELSE 0 END) AS services_ok'
            . ' FROM ((SELECT state AS host_state, (NULL) AS service_state FROM host host)'
            . ' UNION ALL (SELECT (NULL) AS host_state, state AS service_state FROM service service)) summary'
        );
    }
}
