<?php

namespace ipl\Tests\Orm;

use ipl\Orm;

class RelationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNameReturnsNullIfUnset()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getName());
    }

    public function testGetNameReturnsCorrectNameIfSet()
    {
        $relation = (new Orm\Relation())
            ->setName('shop');

        $this->assertSame('shop', $relation->getName());
    }

    public function testGetSubjectReturnsNullIfUnset()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getSubject());
    }

    public function testGetSubjectReturnsCorrectSubjectIfSet()
    {
        $product = new Orm\Model();

        $relation = (new Orm\Relation())
            ->setSubject($product);

        $this->assertSame($product, $relation->getSubject());
    }

    public function testGetTargetReturnsNullIfUnset()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getTarget());
    }

    public function testGetTargetReturnsCorrectTargetIfSet()
    {
        $shop = new Orm\Model();

        $relation = (new Orm\Relation())
            ->setTarget($shop);

        $this->assertSame($shop, $relation->getTarget());
    }

    public function testGetForeignKeyReturnsNullIfUnset()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getForeignKey());
    }

    public function testGetForeignKeyReturnsCorrectKeyIfSet()
    {
        $relation = (new Orm\Relation())
            ->setForeignKey('shop_id');

        $this->assertSame('shop_id', $relation->getForeignKey());
    }

    public function testGetForeignKeyReturnsCorrectKeysIfSetAndCompound()
    {
        $foreignKey = ['shop_name', 'shop_city'];

        $relation = (new Orm\Relation())
            ->setForeignKey($foreignKey);

        $this->assertSame($foreignKey, $relation->getForeignKey());
    }

    public function testGetCandidateKeyReturnsNullIfUnset()
    {
        $relation = new Orm\Relation();

        $this->assertNull($relation->getCandidateKey());
    }

    public function testGetCandidateKeyReturnsCorrectKeyIfSet()
    {
        $relation = (new Orm\Relation())
            ->setCandidateKey('product_id');

        $this->assertSame('product_id', $relation->getCandidateKey());
    }

    public function testGetCandidateKeyReturnsCorrectKeysIfSetAndCompound()
    {
        $candidateKey = ['product_name', 'product_vendor'];

        $relation = (new Orm\Relation())
            ->setCandidateKey($candidateKey);

        $this->assertSame($candidateKey, $relation->getCandidateKey());
    }

    public function testWantForeignKeyReturnsEmptyArrayIfThereIsNoForeignKey()
    {
        $product = new Orm\Model();

        $relation = (new Orm\Relation());

        $fk = $relation->wantForeignKey($relation->getForeignKey(), $product);

        $this->assertSame([], $fk);
    }

    public function testWantForeignKeyDefaultsToTableNamePlusPrimaryKeyOfTheSubject()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation());

        $fk = $relation->wantForeignKey($relation->getForeignKey(), $product);

        $this->assertSame(['product_id'], $fk);
    }

    public function testWantForeignKeyDefaultsToTableNamePlusPrimaryKeysOfTheSubjectIfPrimaryKeyIsCompound()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey(['name', 'vendor']);

        $relation = (new Orm\Relation());

        $fk = $relation->wantForeignKey($relation->getForeignKey(), $product);

        $this->assertSame(['product_name', 'product_vendor'], $fk);
    }

    public function testWantForeignKeyPrefersExplicitForeignKeyOfTheRelationIfSet()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setForeignKey('product_hash');

        $fk = $relation->wantForeignKey($relation->getForeignKey(), $product);

        $this->assertSame(['product_hash'], $fk);
    }

    public function testWantForeignKeyPrefersExplicitForeignKeysOfTheRelationIfSetAndCompound()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setForeignKey(['product_name', 'product_vendor']);

        $fk = $relation->wantForeignKey($relation->getForeignKey(), $product);

        $this->assertSame(['product_name', 'product_vendor'], $fk);
    }

    public function testWantCandidateKeyReturnsEmptyArrayIfThereIsNoCandidateKey()
    {
        $product = new Orm\Model();

        $relation = (new Orm\Relation());

        $ck = $relation->wantCandidateKey($relation->getCandidateKey(), $product);

        $this->assertSame([], $ck);
    }

    public function testWantCandidateKeyDefaultsToPrimaryKeyOfTheSubject()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation());

        $ck = $relation->wantCandidateKey($relation->getCandidateKey(), $product);

        $this->assertSame(['id'], $ck);
    }

    public function testWantCandidateKeyDefaultsToPrimaryKeysOfTheSubjectIfPrimaryKeyIsCompound()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey(['name', 'vendor']);

        $relation = (new Orm\Relation());

        $ck = $relation->wantCandidateKey($relation->getCandidateKey(), $product);

        $this->assertSame(['name', 'vendor'], $ck);
    }

    public function testWantCandidateKeyPrefersExplicitCandidateKeyOfTheRelationIfSet()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setCandidateKey('hash');

        $ck = $relation->wantCandidateKey($relation->getCandidateKey(), $product);

        $this->assertSame(['hash'], $ck);
    }

    public function testWantCandidateKeyPrefersExplicitCandidateKeysOfTheRelationIfSetAndCompound()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setCandidateKey(['name', 'vendor']);

        $ck = $relation->wantCandidateKey($relation->getCandidateKey(), $product);

        $this->assertSame(['name', 'vendor'], $ck);
    }

    /** @expectedException \RuntimeException */
    public function testResolveConditionsThrowsExceptionIfThereIsNoCandidateKey()
    {
        $product = new Orm\Model();

        $relation = new Orm\Relation();

        $relation->resolveConditions($product);
    }

    /** @expectedException \RuntimeException */
    public function testResolveConditionsThrowsExceptionIfThereIsNoForeignKey()
    {
        $product = new Orm\Model();

        $relation = (new Orm\Relation())
            ->setCandidateKey('id');

        $relation->resolveConditions($product);
    }

    /** @expectedException \RuntimeException */
    public function testResolveConditionsThrowsExceptionIfForeignKeyCountDoesNotMatchCandidateKeyCount()
    {
        $product = (new Orm\Model())
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setForeignKey(['product_name', 'product_vendor']);

        $relation->resolveConditions($product);
    }

    public function testResolveConditionsReturnsCorrectForeignKeyAndCandidateKeyPairFromSubject()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation());

        $conditions = $relation->resolveConditions($product);

        $this->assertSame(['product_id' => 'id'], $conditions);
    }

    public function testResolveConditionsReturnsCorrectForeignKeyAndCandidateKeyPairsIfCompoundFromSubject()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey(['name', 'vendor']);

        $relation = (new Orm\Relation());

        $conditions = $relation->resolveConditions($product);

        $this->assertSame(['product_name' => 'name', 'product_vendor' => 'vendor'], $conditions);
    }

    public function testResolveConditionsReturnsCorrectForeignKeyAndCandidateKeyPairFromRelation()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setForeignKey('product_hash')
            ->setCandidateKey('hash');

        $conditions = $relation->resolveConditions($product);

        $this->assertSame(['product_hash' => 'hash'], $conditions);
    }

    public function testResolveConditionsReturnsCorrectForeignKeyAndCandidateKeyPairsIfCompoundFromRelation()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $relation = (new Orm\Relation())
            ->setForeignKey(['product_name', 'product_vendor'])
            ->setCandidateKey(['name', 'vendor']);

        $conditions = $relation->resolveConditions($product);

        $this->assertSame(['product_name' => 'name', 'product_vendor' => 'vendor'], $conditions);
    }

    public function testResolveReturnsCorrectTableAndConditionToJoin()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey('id');

        $shop = (new Orm\Model())
            ->setTableName('shop');

        $relation = (new Orm\Relation())
            ->setName('shop')
            ->setSubject($product)
            ->setTarget($shop);

        $join = $relation->resolve();

        $this->assertSame(
            [['shop', 'shop', ['shop.product_id = product.id']]],
            $join
        );
    }

    public function testResolveReturnsCorrectTableAndConditionsToJoinIfCompound()
    {
        $product = (new Orm\Model())
            ->setTableName('product')
            ->setKey(['name', 'vendor']);

        $shop = (new Orm\Model())
            ->setTableName('shop');

        $relation = (new Orm\Relation())
            ->setName('shop')
            ->setSubject($product)
            ->setTarget($shop);

        $join = $relation->resolve();

        $this->assertSame(
            [['shop', 'shop', ['shop.product_name = product.name', 'shop.product_vendor = product.vendor']]],
            $join
        );
    }
}
