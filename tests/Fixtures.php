<?php

namespace ipl\Tests\Orm;

use ipl\Sql;

trait Fixtures
{
    public function getFixturesDb()
    {
        $db = new Sql\Connection([
            'db' => 'sqlite',
            'dbname' => ':memory:'
        ]);

        $fixtures = file_get_contents(__DIR__ . '/fixtures/sqlite.schema.sql');

        $db->exec($fixtures);

        return $db;
    }
}
