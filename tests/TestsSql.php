<?php

namespace ipl\Tests\Orm;

use ipl\Sql;

trait TestsSql
{
    /** @var Sql\QueryBuilder */
    private $queryBuilder;

    public function initTestsSql()
    {
        $this->queryBuilder = new Sql\QueryBuilder(new TestAdapter());
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
