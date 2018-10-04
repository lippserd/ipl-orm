<?php

namespace ipl\Tests\Orm;

use ipl\Sql;

trait Fixtures
{
    /** @var Sql\Connection */
    private $fixturesDb;

    public function initFixturesDb()
    {
        $db = new Sql\Connection([
            'db' => 'sqlite',
            'dbname' => ':memory:'
        ]);

        $fixtures = file_get_contents(__DIR__ . '/fixtures/sqlite.schema.sql');

        $db->exec($fixtures);

        $this->fixturesDb = $db;
    }

    public function getFixturesDb()
    {
        return $this->fixturesDb;
    }
}
